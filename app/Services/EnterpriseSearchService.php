<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\Deal;
use App\Models\DocumentTemplate;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Meeting;
use App\Models\Product;
use App\Models\Project;
use App\Models\SearchLog;
use App\Models\ServiceContract;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\WikiPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EnterpriseSearchService
{
    protected string $host;
    protected string $apiKey;
    protected string $indexPrefix;

    public function __construct()
    {
        $this->host = config('services.meilisearch.host', 'http://localhost:7700');
        $this->apiKey = config('services.meilisearch.key', '');
        $this->indexPrefix = config('services.meilisearch.index_prefix', 'bizos_');
    }

    protected function http()
    {
        $http = Http::baseUrl(rtrim($this->host, '/'))
            ->timeout(30)
            ->acceptJson()
            ->contentType('application/json');

        if (!empty($this->apiKey)) {
            $http = $http->withToken($this->apiKey, 'Bearer');
        }

        return $http;
    }

    protected function indexName(string $model): string
    {
        return $this->indexPrefix . strtolower(class_basename($model));
    }

    // ─── SETUP ───

    public function setupIndexes(): void
    {
        $models = $this->getSearchableModels();

        foreach ($models as $modelClass) {
            $this->createIndexIfNotExists($modelClass);
        }

        $this->pushSynonymDictionary();
        $this->configureAllIndexes();
    }

    protected function createIndexIfNotExists(string $modelClass): void
    {
        $indexName = $this->indexName($modelClass);
        $response = $this->http()->get('/indexes/' . $indexName);

        if ($response->status() === 404) {
            $this->http()->post('/indexes', [
                'uid' => $indexName,
                'primaryKey' => 'id',
            ]);

            Log::info("Meilisearch index created: {$indexName}");
        }
    }

    protected function configureAllIndexes(): void
    {
        $models = $this->getSearchableModels();

        foreach ($models as $modelClass) {
            $indexName = $this->indexName($modelClass);

            $this->http()->patch('/indexes/' . $indexName . '/settings', [
                'searchableAttributes' => $this->getSearchableAttributes($modelClass),
                'filterableAttributes' => ['module', 'status', 'company_id', 'department_id', 'category_id', 'priority', 'created_at', 'email_date'],
                'sortableAttributes' => ['created_at', 'updated_at', 'email_date', 'score'],
                'rankingRules' => [
                    'words',
                    'typo',
                    'proximity',
                    'attribute',
                    'sort',
                    'exactness',
                ],
                'typoTolerance' => [
                    'enabled' => true,
                    'minWordSizeForTypos' => [
                        'oneTypo' => 4,
                        'twoTypos' => 8,
                    ],
                ],
            ]);
        }
    }

    protected function getSearchableAttributes(string $modelClass): array
    {
        $base = ['title', 'subtitle', 'description', 'module', 'status'];
        $class = class_basename($modelClass);

        $map = [
            'Employee' => ['first_name', 'last_name', 'employee_code', 'email', 'phone', 'department', 'position'],
            'Client' => ['name', 'client_code', 'email', 'phone', 'industry', 'city'],
            'Lead' => ['first_name', 'last_name', 'company_name', 'email', 'phone', 'industry'],
            'Deal' => ['title', 'notes'],
            'Invoice' => ['invoice_number', 'notes'],
            'Ticket' => ['ticket_number', 'subject', 'description', 'priority', 'source'],
            'Product' => ['code', 'name', 'description', 'active_ingredient'],
            'Project' => ['code', 'name', 'description'],
            'Task' => ['title', 'description'],
            'ChatMessage' => ['message', 'sender_name'],
            'Meeting' => ['title', 'description', 'location', 'meeting_type'],
            'WikiPage' => ['title', 'slug', 'content'],
            'DocumentTemplate' => ['name', 'type', 'module'],
            'ServiceContract' => ['contract_number', 'contract_type', 'notes'],
            'Asset' => ['asset_code', 'name', 'description'],
        ];

        return array_merge($base, $map[$class] ?? []);
    }

    // ─── INDEXING ───

    public function indexModel(string $modelClass, array $records): void
    {
        if (empty($records)) {
            return;
        }

        $indexName = $this->indexName($modelClass);

        $this->http()->post('/indexes/' . $indexName . '/documents', $records);

        Log::info("Indexed " . count($records) . " records to {$indexName}");
    }

    public function updateRecord(Model $record): void
    {
        $document = $this->modelToDocument($record);
        if (!$document) {
            return;
        }

        $indexName = $this->indexName(get_class($record));

        $this->http()->put('/indexes/' . $indexName . '/documents', [$document]);
    }

    public function deleteRecord(Model $record): void
    {
        $indexName = $this->indexName(get_class($record));

        $this->http()->delete('/indexes/' . $indexName . '/documents/' . $record->id);
    }

    public function syncAll(): void
    {
        $this->setupIndexes();

        foreach ($this->getSearchableModels() as $modelClass) {
            $this->syncModel($modelClass);
        }
    }

    public function syncModel(string $modelClass): void
    {
        $indexName = $this->indexName($modelClass);

        $this->http()->delete('/indexes/' . $indexName . '/documents');

        $chunkSize = 500;
        $modelClass::query()->chunk($chunkSize, function ($records) use ($modelClass) {
            $documents = [];
            foreach ($records as $record) {
                $doc = $this->modelToDocument($record);
                if ($doc) {
                    $documents[] = $doc;
                }
            }
            if (!empty($documents)) {
                $this->indexModel($modelClass, $documents);
            }
        });
    }

    // ─── SEARCH ───

    public function search(string $query, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $start = microtime(true);

        $searchableModels = $this->getSearchableModels();

        if (!empty($filters['module'])) {
            $modules = is_array($filters['module']) ? $filters['module'] : [$filters['module']];
            $searchableModels = array_filter($searchableModels, function ($model) use ($modules) {
                return in_array($this->getModuleName($model), $modules);
            });
        }

        $allResults = [];
        $totalHits = 0;

        $meiliFilters = $this->buildFilterString($filters);

        foreach ($searchableModels as $modelClass) {
            $indexName = $this->indexName($modelClass);

            $params = [
                'q' => $query,
                'limit' => $perPage,
                'offset' => ($page - 1) * $perPage,
                'attributesToHighlight' => ['*'],
                'showMatchesPosition' => false,
            ];

            if (!empty($meiliFilters)) {
                $params['filter'] = $meiliFilters;
            }

            try {
                $response = $this->http()->post('/indexes/' . $indexName . '/search', $params);

                if ($response->successful()) {
                    $data = $response->json();
                    $hits = $data['hits'] ?? [];

                    foreach ($hits as $hit) {
                        $allResults[] = $this->formatSearchResult($hit, $modelClass);
                    }

                    $totalHits += $data['estimatedTotalHits'] ?? count($hits);
                }
            } catch (\Exception $e) {
                Log::warning("Meilisearch search failed for {$indexName}: " . $e->getMessage());
            }
        }

        // Sort by score (Meilisearch ranking)
        usort($allResults, function ($a, $b) {
            return ($b['score'] ?? 0) <=> ($a['score'] ?? 0);
        });

        // Paginate merged results
        $allResults = array_slice($allResults, 0, $perPage);

        $elapsed = round((microtime(true) - $start) * 1000, 2);

        $this->logSearch($query, auth()->id() ?? null, $totalHits, $elapsed);

        return [
            'total_hits' => $totalHits,
            'results' => $allResults,
            'search_time_ms' => $elapsed,
        ];
    }

    public function suggest(string $prefix, int $limit = 10): array
    {
        if (mb_strlen(trim($prefix)) < 2) {
            return [];
        }

        $searchableModels = $this->getSearchableModels();
        $results = [];

        foreach ($searchableModels as $modelClass) {
            if (count($results) >= $limit) {
                break;
            }

            $indexName = $this->indexName($modelClass);

            try {
                $response = $this->http()->post('/indexes/' . $indexName . '/search', [
                    'q' => $prefix,
                    'limit' => 3,
                ]);

                if ($response->successful()) {
                    $hits = $response->json()['hits'] ?? [];
                    foreach ($hits as $hit) {
                        $results[] = $this->formatSearchResult($hit, $modelClass);
                        if (count($results) >= $limit) {
                            break 2;
                        }
                    }
                }
            } catch (\Exception $e) {
                // skip failed index
            }
        }

        return $results;
    }

    // ─── ANALYTICS ───

    public function logSearch(string $query, ?int $userId, int $resultsCount, float $searchTimeMs = 0): void
    {
        if (empty(trim($query)) || !$userId) {
            return;
        }

        SearchLog::create([
            'user_id' => $userId,
            'query' => mb_substr(trim($query), 0, 500),
            'results_count' => $resultsCount,
            'search_time_ms' => $searchTimeMs,
        ]);
    }

    public function logClick(string $query, int $userId, string $resultType, string $resultModel, int $resultId): void
    {
        SearchLog::where('user_id', $userId)
            ->where('query', $query)
            ->whereNull('clicked_result_type')
            ->latest('created_at')
            ->first()
            ?->update([
                'clicked_result_type' => $resultType,
                'clicked_result_model' => $resultModel,
                'clicked_result_id' => $resultId,
            ]);
    }

    public function getPopularSearches(int $limit = 20): array
    {
        $cacheKey = 'search:popular_searches';

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($limit) {
            return SearchLog::selectRaw('query, COUNT(*) as count, AVG(results_count) as avg_results')
                ->whereNotNull('query')
                ->where('query', '!=', '')
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('query')
                ->orderByDesc('count')
                ->limit($limit)
                ->get()
                ->toArray();
        });
    }

    public function getZeroResultSearches(int $limit = 20): array
    {
        return SearchLog::selectRaw('query, COUNT(*) as count')
            ->whereNotNull('query')
            ->where('query', '!=', '')
            ->where('results_count', 0)
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function getSearchAnalytics(string $period = '7d'): array
    {
        $since = match ($period) {
            'today' => now()->startOfDay(),
            '7d' => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            default => now()->subDays(7),
        };

        $totalSearches = SearchLog::where('created_at', '>=', $since)->count();
        $uniqueSearchers = SearchLog::where('created_at', '>=', $since)->distinct('user_id')->count('user_id');
        $avgResults = round(SearchLog::where('created_at', '>=', $since)->avg('results_count') ?? 0, 2);
        $avgTime = round(SearchLog::where('created_at', '>=', $since)->avg('search_time_ms') ?? 0, 2);
        $clickThroughRate = 0;
        $totalWithResults = SearchLog::where('created_at', '>=', $since)->where('results_count', '>', 0)->count();
        if ($totalWithResults > 0) {
            $totalClicked = SearchLog::where('created_at', '>=', $since)->whereNotNull('clicked_result_type')->count();
            $clickThroughRate = round(($totalClicked / $totalWithResults) * 100, 2);
        }

        $dailyTrend = SearchLog::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', $since)
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();

        return [
            'total_searches' => $totalSearches,
            'unique_searchers' => $uniqueSearchers,
            'avg_results_per_search' => $avgResults,
            'avg_search_time_ms' => $avgTime,
            'click_through_rate_pct' => $clickThroughRate,
            'top_queries' => $this->getPopularSearches(10),
            'zero_result_queries' => $this->getZeroResultSearches(10),
            'daily_trend' => $dailyTrend,
            'period' => $period,
        ];
    }

    // ─── SYNONYM DICTIONARY ───

    public function pushSynonymDictionary(): void
    {
        $synonyms = [
            'gaji' => ['salary', 'payroll', 'upah', 'honor'],
            'cuti' => ['leave', 'libur', 'vacation'],
            'pelanggan' => ['customer', 'klien', 'client', 'pembeli'],
            'faktur' => ['invoice', 'tagihan', 'bill'],
            'pesanan' => ['order', 'orderan', 'purchase'],
            'karyawan' => ['pegawai', 'employee', 'staff', 'pekerja'],
            'tiket' => ['ticket', 'keluhan', 'complaint'],
            'proyek' => ['project', 'projek'],
            'aset' => ['asset', 'harta', 'barang'],
            'tugas' => ['task', 'pekerjaan', 'job'],
            'rapat' => ['meeting', 'pertemuan'],
            'kontrak' => ['contract', 'perjanjian'],
            'produk' => ['product', 'barang', 'item'],
            'dokumen' => ['document', 'template', 'surat'],
        ];

        foreach ($this->getSearchableModels() as $modelClass) {
            $indexName = $this->indexName($modelClass);

            try {
                $this->http()->put('/indexes/' . $indexName . '/settings/synonyms', $synonyms);
            } catch (\Exception $e) {
                Log::warning("Failed to push synonyms to {$indexName}: " . $e->getMessage());
            }
        }

        Log::info('Synonyms pushed to all Meilisearch indexes');
    }

    // ─── HELPERS ───

    public function getSearchableModels(): array
    {
        return [
            Employee::class,
            Client::class,
            Lead::class,
            Deal::class,
            Invoice::class,
            Ticket::class,
            Product::class,
            Project::class,
            Task::class,
            ChatMessage::class,
            Meeting::class,
            WikiPage::class,
            DocumentTemplate::class,
            ServiceContract::class,
            Asset::class,
        ];
    }

    protected function modelToDocument(Model $record): ?array
    {
        $class = get_class($record);
        $module = $this->getModuleName($class);

        $doc = [
            'id' => $class . '_' . $record->id,
            'model_id' => $record->id,
            'model_class' => $class,
            'module' => $module,
            'url' => $this->getModelUrl($class, $record->id),
            'created_at' => $record->created_at?->timestamp,
            'updated_at' => $record->updated_at?->timestamp,
        ];

        $doc = array_merge($doc, $this->extractModelData($record));

        return $doc;
    }

    protected function extractModelData(Model $record): array
    {
        $class = get_class($record);
        $data = [];

        switch ($class) {
            case Employee::class:
                $data['title'] = trim($record->first_name . ' ' . $record->last_name);
                $data['subtitle'] = $record->department?->name ?? $record->employee_code ?? '';
                $data['description'] = implode(', ', array_filter([$record->email, $record->phone, $record->position?->name]));
                $data['first_name'] = $record->first_name;
                $data['last_name'] = $record->last_name;
                $data['employee_code'] = $record->employee_code;
                $data['email'] = $record->email;
                $data['phone'] = $record->phone;
                $data['department'] = $record->department?->name;
                $data['position'] = $record->position?->name;
                $data['department_id'] = $record->department_id;
                $data['status'] = $record->status;
                break;

            case Client::class:
                $data['title'] = $record->name;
                $data['subtitle'] = $record->client_type . ' - ' . ($record->industry ?? '');
                $data['description'] = implode(', ', array_filter([$record->email, $record->phone, $record->city]));
                $data['client_code'] = $record->client_code;
                $data['email'] = $record->email;
                $data['phone'] = $record->phone;
                $data['industry'] = $record->industry;
                $data['city'] = $record->city;
                $data['status'] = $record->status;
                break;

            case Lead::class:
                $data['title'] = trim($record->first_name . ' ' . $record->last_name);
                $data['subtitle'] = $record->company_name ?? '';
                $data['description'] = implode(', ', array_filter([$record->email, $record->phone, $record->industry]));
                $data['first_name'] = $record->first_name;
                $data['last_name'] = $record->last_name;
                $data['company_name'] = $record->company_name;
                $data['email'] = $record->email;
                $data['phone'] = $record->phone;
                $data['industry'] = $record->industry;
                $data['status'] = $record->status;
                break;

            case Deal::class:
                $data['title'] = $record->title;
                $data['subtitle'] = $record->client?->name ?? $record->lead?->company_name ?? '';
                $data['description'] = 'Nilai: Rp ' . number_format($record->expected_value ?? 0, 0, ',', '.');
                $data['notes'] = $record->notes;
                $data['status'] = $record->status;
                break;

            case Invoice::class:
                $data['title'] = $record->invoice_number;
                $data['subtitle'] = 'Rp ' . number_format($record->total ?? 0, 0, ',', '.');
                $data['description'] = $record->invoice_type . ' - ' . ($record->notes ?? '');
                $data['invoice_number'] = $record->invoice_number;
                $data['notes'] = $record->notes;
                $data['status'] = $record->status;
                break;

            case Ticket::class:
                $data['title'] = $record->ticket_number . ' - ' . $record->subject;
                $data['subtitle'] = $record->client?->name ?? $record->contact?->full_name ?? '';
                $data['description'] = strip_tags($record->description ?? '');
                $data['ticket_number'] = $record->ticket_number;
                $data['subject'] = $record->subject;
                $data['description'] = strip_tags($record->description ?? '');
                $data['priority'] = $record->priority;
                $data['source'] = $record->source;
                $data['status'] = $record->status;
                break;

            case Product::class:
                $data['title'] = $record->name;
                $data['subtitle'] = $record->code . ' - ' . ($record->category?->name ?? '');
                $data['description'] = $record->description ?? '';
                $data['code'] = $record->code;
                $data['active_ingredient'] = $record->active_ingredient;
                $data['category_id'] = $record->category_id;
                $data['status'] = $record->is_active ? 'active' : 'inactive';
                break;

            case Project::class:
                $data['title'] = $record->name;
                $data['subtitle'] = $record->client?->name ?? $record->code ?? '';
                $data['description'] = $record->description ?? '';
                $data['code'] = $record->code;
                $data['status'] = $record->status;
                break;

            case Task::class:
                $data['title'] = $record->title;
                $data['subtitle'] = $record->project?->name ?? '';
                $data['description'] = $record->description ?? '';
                $data['status'] = $record->status;
                $data['priority'] = $record->priority;
                break;

            case ChatMessage::class:
                $data['title'] = $record->sender?->first_name . ' ' . $record->sender?->last_name;
                $data['subtitle'] = $record->chat?->name ?? '';
                $data['description'] = mb_substr($record->message ?? '', 0, 200);
                $data['message'] = $record->message;
                $data['sender_name'] = $record->sender?->first_name . ' ' . $record->sender?->last_name;
                $data['email_date'] = $record->created_at?->timestamp;
                break;

            case Meeting::class:
                $data['title'] = $record->title;
                $data['subtitle'] = $record->organizer?->first_name ?? '';
                $data['description'] = $record->description ?? $record->location ?? '';
                $data['location'] = $record->location;
                $data['meeting_type'] = $record->meeting_type;
                $data['status'] = $record->status;
                break;

            case WikiPage::class:
                $data['title'] = $record->title;
                $data['subtitle'] = $record->category?->name ?? '';
                $data['description'] = mb_substr(strip_tags($record->content ?? ''), 0, 300);
                $data['slug'] = $record->slug;
                $data['content'] = strip_tags($record->content ?? '');
                $data['status'] = $record->status;
                break;

            case DocumentTemplate::class:
                $data['title'] = $record->name;
                $data['subtitle'] = $record->type . ' - ' . $record->module;
                $data['description'] = mb_substr(strip_tags($record->content ?? ''), 0, 300);
                $data['type'] = $record->type;
                $data['module'] = $record->module;
                $data['status'] = $record->is_active ? 'active' : 'inactive';
                break;

            case ServiceContract::class:
                $data['title'] = $record->contract_number;
                $data['subtitle'] = $record->client?->name ?? '';
                $data['description'] = $record->contract_type . ' - ' . ($record->notes ?? '');
                $data['contract_number'] = $record->contract_number;
                $data['contract_type'] = $record->contract_type;
                $data['notes'] = $record->notes;
                $data['status'] = $record->status;
                break;

            case Asset::class:
                $data['title'] = $record->name;
                $data['subtitle'] = $record->asset_code . ' - ' . ($record->category?->name ?? '');
                $data['description'] = $record->description ?? $record->location ?? '';
                $data['asset_code'] = $record->asset_code;
                $data['status'] = $record->status;
                break;
        }

        $data['company_id'] = $record->company_id ?? null;

        return $data;
    }

    protected function formatSearchResult(array $hit, string $modelClass): array
    {
        $module = $hit['module'] ?? $this->getModuleName($modelClass);
        $icon = $this->getModuleIcon($module);

        $highlights = [];
        if (!empty($hit['_formatted'])) {
            foreach (['title', 'description', 'message', 'subject'] as $field) {
                if (!empty($hit['_formatted'][$field]) && $hit['_formatted'][$field] !== ($hit[$field] ?? '')) {
                    $highlights[$field] = $hit['_formatted'][$field];
                }
            }
            unset($hit['_formatted']);
        }

        return [
            'model' => class_basename($modelClass),
            'model_class' => $hit['model_class'] ?? $modelClass,
            'id' => $hit['model_id'] ?? $hit['id'] ?? null,
            'title' => $hit['title'] ?? '',
            'subtitle' => $hit['subtitle'] ?? '',
            'url' => $hit['url'] ?? '#',
            'icon' => $icon,
            'module' => $module,
            'highlights' => $highlights,
            'score' => $hit['_rankingScore'] ?? 1.0,
        ];
    }

    protected function getModuleName(string $modelClass): string
    {
        $class = class_basename($modelClass);
        return match ($class) {
            'Employee' => 'hrm',
            'Client' => 'crm',
            'Lead' => 'crm',
            'Deal' => 'crm',
            'Invoice' => 'finance',
            'Ticket' => 'helpdesk',
            'Product' => 'inventory',
            'Project' => 'project',
            'Task' => 'project',
            'ChatMessage' => 'kolaborasi',
            'Meeting' => 'kolaborasi',
            'WikiPage' => 'kolaborasi',
            'DocumentTemplate' => 'kolaborasi',
            'ServiceContract' => 'crm',
            'Asset' => 'inventory',
            default => mb_strtolower($class),
        };
    }

    protected function getModuleIcon(string $module): string
    {
        return match ($module) {
            'hrm' => 'heroicon-o-users',
            'crm' => 'heroicon-o-briefcase',
            'finance' => 'heroicon-o-banknotes',
            'helpdesk' => 'heroicon-o-ticket',
            'inventory' => 'heroicon-o-archive-box',
            'project' => 'heroicon-o-clipboard-document-list',
            'kolaborasi' => 'heroicon-o-chat-bubble-left-right',
            default => 'heroicon-o-rectangle-stack',
        };
    }

    protected function getModelUrl(string $modelClass, int $id): string
    {
        $resourceMap = [
            Employee::class => '/admin/employees',
            Client::class => '/admin/clients',
            Lead::class => '/admin/leads',
            Deal::class => '/admin/deals',
            Invoice::class => '/admin/invoices',
            Ticket::class => '/admin/tickets',
            Product::class => '/admin/products',
            Project::class => '/admin/projects',
            Task::class => '/admin/tasks',
            Meeting::class => '/admin/meetings',
            WikiPage::class => '/admin/wiki-pages',
            DocumentTemplate::class => '/admin/document-templates',
            ServiceContract::class => '/admin/service-contracts',
            Asset::class => '/admin/assets',
            ChatMessage::class => '/admin/chats',
        ];

        $base = $resourceMap[$modelClass] ?? '/admin';
        return url($base . '/' . $id);
    }

    protected function buildFilterString(array $filters): string
    {
        $parts = [];

        if (!empty($filters['status'])) {
            $status = is_array($filters['status']) ? $filters['status'] : [$filters['status']];
            $parts[] = 'status IN [' . implode(',', array_map(fn($s) => '"' . $s . '"', $status)) . ']';
        }

        if (!empty($filters['company_id'])) {
            $parts[] = 'company_id = ' . (int) $filters['company_id'];
        }

        if (!empty($filters['department_id'])) {
            $parts[] = 'department_id = ' . (int) $filters['department_id'];
        }

        if (!empty($filters['date_from'])) {
            $ts = strtotime($filters['date_from']);
            if ($ts) {
                $parts[] = 'created_at >= ' . $ts;
            }
        }

        if (!empty($filters['date_to'])) {
            $ts = strtotime($filters['date_to'] . ' 23:59:59');
            if ($ts) {
                $parts[] = 'created_at <= ' . $ts;
            }
        }

        return implode(' AND ', $parts);
    }

    public function isAvailable(): bool
    {
        try {
            $response = $this->http()->get('/health');
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}

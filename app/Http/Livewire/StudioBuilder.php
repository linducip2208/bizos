<?php

namespace App\Http\Livewire;

use App\Models\Workflow;
use App\Services\NoCodeStudioService;
use Livewire\Component;
use Illuminate\Support\Str;

class StudioBuilder extends Component
{
    public ?int $workflowId = null;
    public string $workflowName = 'Workflow Baru';
    public string $workflowDescription = '';
    public string $triggerEvent = '';
    public array $nodes = [];
    public array $edges = [];
    public array $studioBlocks = [];
    public array $validationErrors = [];
    public ?string $selectedNodeId = null;
    public array $selectedNodeConfig = [];
    public string $executionResult = '';
    public bool $isExecuting = false;

    protected $listeners = [
        'nodeMoved' => 'handleNodeMoved',
        'connectNodes' => 'handleConnectNodes',
        'removeNode' => 'handleRemoveNode',
        'removeEdge' => 'handleRemoveEdge',
    ];

    public function mount(?int $workflowId = null): void
    {
        $service = app(NoCodeStudioService::class);
        $this->studioBlocks = $service->getStudioBlocks();

        if ($workflowId) {
            $this->workflowId = $workflowId;
            $workflow = Workflow::find($workflowId);
            if ($workflow) {
                $this->workflowName = $workflow->name;
                $this->workflowDescription = $workflow->description ?? '';
                $this->triggerEvent = $workflow->trigger_event ?? '';
                $config = $workflow->studio_config ?? [];
                $this->nodes = $config['nodes'] ?? [];
                $this->edges = $config['edges'] ?? [];
            }
        }
    }

    public function addNode(string $blockType, string $blockName, string $color): void
    {
        $nodeId = 'node_' . Str::random(10);
        $block = collect($this->studioBlocks)
            ->first(fn($b) => $b['name'] === $blockName && $b['color'] === $color);

        $this->nodes[] = [
            'id' => $nodeId,
            'block_type' => $blockType,
            'block_name' => $blockName,
            'label' => $blockName,
            'color' => $color,
            'icon' => $block['icon'] ?? 'heroicon-o-cube',
            'config' => [],
            'position' => [
                'x' => 300 + (count($this->nodes) * 50),
                'y' => 200 + (count($this->nodes) * 30),
            ],
        ];

        $this->selectNode($nodeId);
    }

    public function selectNode(string $nodeId): void
    {
        $this->selectedNodeId = $nodeId;
        $node = collect($this->nodes)->first(fn($n) => $n['id'] === $nodeId);
        $this->selectedNodeConfig = $node['config'] ?? [];
    }

    public function deselectNode(): void
    {
        $this->selectedNodeId = null;
        $this->selectedNodeConfig = [];
    }

    public function updateNodeConfig(string $key, $value): void
    {
        if (!$this->selectedNodeId) {
            return;
        }

        foreach ($this->nodes as &$node) {
            if ($node['id'] === $this->selectedNodeId) {
                if (!isset($node['config'])) {
                    $node['config'] = [];
                }
                $node['config'][$key] = $value;
                $this->selectedNodeConfig = $node['config'];
                break;
            }
        }
    }

    public function handleNodeMoved(string $nodeId, float $x, float $y): void
    {
        foreach ($this->nodes as &$node) {
            if ($node['id'] === $nodeId) {
                $node['position'] = ['x' => $x, 'y' => $y];
                break;
            }
        }
    }

    public function handleConnectNodes(string $sourceId, string $targetId): void
    {
        $exists = collect($this->edges)->first(
            fn($e) => $e['source'] === $sourceId && $e['target'] === $targetId
        );

        if (!$exists) {
            $this->edges[] = [
                'id' => 'edge_' . Str::random(8),
                'source' => $sourceId,
                'target' => $targetId,
            ];
        }
    }

    public function handleRemoveNode(string $nodeId): void
    {
        $this->nodes = array_values(
            array_filter($this->nodes, fn($n) => $n['id'] !== $nodeId)
        );

        $this->edges = array_values(
            array_filter($this->edges, fn($e) => $e['source'] !== $nodeId && $e['target'] !== $nodeId)
        );

        if ($this->selectedNodeId === $nodeId) {
            $this->deselectNode();
        }
    }

    public function handleRemoveEdge(string $edgeId): void
    {
        $this->edges = array_values(
            array_filter($this->edges, fn($e) => $e['id'] !== $edgeId)
        );
    }

    public function validateFlow(): void
    {
        $service = app(NoCodeStudioService::class);
        $result = $service->validateFlow($this->nodes, $this->edges);
        $this->validationErrors = $result['errors'];

        if ($result['valid']) {
            session()->flash('success', 'Flow valid! Siap dieksekusi.');
        } else {
            session()->flash('error', 'Flow tidak valid. Periksa error di bawah.');
        }
    }

    public function saveWorkflow(): void
    {
        $companyId = auth()->user()->company_id;

        $workflow = Workflow::updateOrCreate(
            ['id' => $this->workflowId],
            [
                'company_id' => $companyId,
                'name' => $this->workflowName,
                'description' => $this->workflowDescription,
                'workflow_type' => 'studio',
                'trigger_event' => $this->triggerEvent,
                'studio_config' => [
                    'nodes' => $this->nodes,
                    'edges' => $this->edges,
                ],
                'enabled_blocks' => array_unique(array_column($this->nodes, 'block_name')),
                'is_active' => true,
                'created_by' => auth()->user()->employee_id ?? auth()->id(),
            ]
        );

        $this->workflowId = $workflow->id;

        session()->flash('success', "Workflow '{$workflow->name}' berhasil disimpan.");
    }

    public function executeWorkflow(): void
    {
        if (!$this->workflowId) {
            session()->flash('error', 'Simpan workflow terlebih dahulu sebelum eksekusi.');
            return;
        }

        $this->isExecuting = true;
        $this->executionResult = '';

        try {
            $service = app(NoCodeStudioService::class);
            $result = $service->executeFlow($this->workflowId);

            $this->executionResult = json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            if ($result['status'] === 'success') {
                session()->flash('success', 'Workflow dieksekusi dengan sukses.');
            } else {
                session()->flash('error', 'Workflow gagal: ' . ($result['error'] ?? 'Unknown error'));
            }
        } catch (\Throwable $e) {
            $this->executionResult = json_encode([
                'status' => 'error',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], JSON_PRETTY_PRINT);
            session()->flash('error', 'Eksekusi gagal: ' . $e->getMessage());
        }

        $this->isExecuting = false;
    }

    public function generateWebhookUrl(): void
    {
        if (!$this->workflowId) {
            session()->flash('error', 'Simpan workflow terlebih dahulu.');
            return;
        }

        $service = app(NoCodeStudioService::class);
        $url = $service->registerWebhookTrigger($this->workflowId);

        session()->flash('success', 'Webhook URL: ' . $url);
    }

    public function render()
    {
        return view('livewire.studio-builder')
            ->layout('livewire.layouts.studio');
    }
}

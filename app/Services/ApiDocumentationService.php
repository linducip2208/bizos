<?php

namespace App\Services;

class ApiDocumentationService
{
    protected ApiHubService $apiHub;

    public function __construct(ApiHubService $apiHub)
    {
        $this->apiHub = $apiHub;
    }

    public function generateOpenApiSpec(): array
    {
        $endpoints = $this->apiHub->getEndpoints();
        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('app.name') . ' API',
                'description' => 'REST API untuk BizOS — HRM, Finance, CRM, Project, POS, dan lainnya.',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'BizOS Support',
                    'url' => config('app.url'),
                ],
            ],
            'servers' => [
                ['url' => config('app.url') . '/api/v1', 'description' => 'Production'],
            ],
            'security' => [
                ['ApiKeyAuth' => []],
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'Authorization',
                        'description' => 'Bearer {API_KEY}',
                    ],
                ],
                'schemas' => [
                    'Error' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                            'errors' => ['type' => 'object'],
                        ],
                    ],
                    'Pagination' => [
                        'type' => 'object',
                        'properties' => [
                            'current_page' => ['type' => 'integer'],
                            'per_page' => ['type' => 'integer'],
                            'total' => ['type' => 'integer'],
                            'last_page' => ['type' => 'integer'],
                            'data' => ['type' => 'array', 'items' => ['type' => 'object']],
                        ],
                    ],
                ],
            ],
            'paths' => [],
        ];

        foreach ($endpoints as $resource) {
            foreach ($resource['endpoints'] as $endpoint) {
                $path = $endpoint['path'];
                $method = strtolower($endpoint['method']);
                $cleanPath = str_replace('/api/v1/', '/', $path);

                if (! isset($spec['paths'][$cleanPath])) {
                    $spec['paths'][$cleanPath] = [];
                }

                $tags = [$resource['label']];
                $operationId = $method . '-' . str_replace(['/', '-'], ['-', '_'], $resource['resource']);

                if (in_array($method, ['get'])) {
                    $operationId = str_contains($path, '{id}') ? "show-{$resource['resource']}" : "list-{$resource['resource']}";
                } elseif ($method === 'post') {
                    $operationId = "create-{$resource['resource']}";
                } elseif ($method === 'put') {
                    $operationId = "update-{$resource['resource']}";
                } elseif ($method === 'delete') {
                    $operationId = "delete-{$resource['resource']}";
                }

                $spec['paths'][$cleanPath][$method] = [
                    'tags' => $tags,
                    'summary' => $endpoint['description'],
                    'operationId' => $operationId,
                    'parameters' => [],
                    'security' => [['ApiKeyAuth' => []]],
                    'responses' => [
                        '200' => ['description' => 'Berhasil'],
                        '401' => ['description' => 'Unauthorized — API key tidak valid'],
                        '403' => ['description' => 'Forbidden — tidak ada permission'],
                        '429' => ['description' => 'Rate limit exceeded'],
                    ],
                ];

                if (str_contains($path, '{id}')) {
                    $spec['paths'][$cleanPath][$method]['parameters'][] = [
                        'name' => 'id',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'integer'],
                        'description' => 'ID ' . $resource['label'],
                    ];
                }
            }
        }

        return $spec;
    }

    public function generateHtmlDocs(): string
    {
        $spec = $this->generateOpenApiSpec();
        $endpoints = $this->apiHub->getEndpoints();

        $html = '<!DOCTYPE html><html lang="id"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
        $html .= '<title>API Documentation — ' . config('app.name') . '</title>';
        $html .= '<script src="https://cdn.tailwindcss.com"></script>';
        $html .= '<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet">';
        $html .= '<style>body{font-family:"Inter",sans-serif}code{font-family:"JetBrains Mono",monospace}</style>';
        $html .= '</head><body class="bg-stone-50 text-stone-900">';

        $html .= '<div class="max-w-7xl mx-auto px-4 py-12">';
        $html .= '<h1 class="text-4xl font-bold mb-2">' . config('app.name') . ' API</h1>';
        $html .= '<p class="text-stone-500 text-lg mb-8">REST API lengkap untuk semua resource BizOS. Gunakan API Key untuk autentikasi.</p>';

        $html .= '<div class="bg-indigo-50 border border-indigo-200 rounded-xl p-6 mb-10">';
        $html .= '<h2 class="text-lg font-bold text-indigo-900 mb-2">Autentikasi</h2>';
        $html .= '<p class="text-indigo-700 text-sm mb-3">Semua request wajib menyertakan API Key di header:</p>';
        $html .= '<code class="bg-indigo-100 text-indigo-800 px-3 py-2 rounded-lg block text-sm">Authorization: Bearer {API_KEY}</code>';
        $html .= '<p class="text-indigo-600 text-xs mt-3">API Key bisa dibuat di Admin Panel → Integrasi → Kunci API</p>';
        $html .= '</div>';

        foreach ($endpoints as $resource) {
            $html .= '<div class="mb-10">';
            $html .= '<h2 class="text-2xl font-bold mb-4 text-indigo-700">' . htmlspecialchars($resource['label']) . '</h2>';
            foreach ($resource['endpoints'] as $ep) {
                $methodColor = match ($ep['method']) {
                    'GET' => 'bg-emerald-100 text-emerald-800',
                    'POST' => 'bg-blue-100 text-blue-800',
                    'PUT' => 'bg-amber-100 text-amber-800',
                    'DELETE' => 'bg-red-100 text-red-800',
                    default => 'bg-stone-100 text-stone-800',
                };
                $html .= '<div class="bg-white border border-stone-200 rounded-lg p-4 mb-3">';
                $html .= '<div class="flex items-center gap-3 mb-2">';
                $html .= '<span class="font-mono text-xs font-bold px-2 py-1 rounded ' . $methodColor . '">' . $ep['method'] . '</span>';
                $html .= '<code class="text-sm text-stone-700">' . htmlspecialchars($ep['path']) . '</code>';
                $html .= '</div>';
                $html .= '<p class="text-sm text-stone-500">' . htmlspecialchars($ep['description']) . '</p>';
                $html .= '</div>';
            }
            $html .= '</div>';
        }

        $html .= '<div class="bg-stone-100 border border-stone-200 rounded-xl p-6 mt-10">';
        $html .= '<h2 class="text-lg font-bold mb-2">Rate Limiting</h2>';
        $html .= '<p class="text-sm text-stone-600">Default: 60 request per menit per API Key. Bisa dikustomisasi saat membuat API Key.</p>';
        $html .= '<p class="text-sm text-stone-600 mt-1">Header response: <code class="text-xs">X-RateLimit-Limit</code>, <code class="text-xs">X-RateLimit-Remaining</code></p>';
        $html .= '</div>';

        $html .= '</div></body></html>';

        return $html;
    }
}

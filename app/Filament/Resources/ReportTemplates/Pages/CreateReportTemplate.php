<?php

namespace App\Filament\Resources\ReportTemplates\Pages;

use App\Filament\Resources\ReportTemplates\ReportTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReportTemplate extends CreateRecord
{
    protected static string $resource = ReportTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['created_by'] = auth()->id();

        if (empty($data['slug'])) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['name']);
        }

        $data['query_config'] = $this->cleanQueryConfig($data['query_config'] ?? []);
        $data['chart_config'] = $data['chart_config'] ?? [];

        return $data;
    }

    protected function cleanQueryConfig(array $config): array
    {
        if (isset($config['select']) && is_array($config['select'])) {
            $config['select'] = array_filter($config['select']);
        }

        if (isset($config['joins'])) {
            $config['joins'] = array_values(array_filter($config['joins'], function ($join) {
                return !empty($join['table']) && !empty($join['first']);
            }));
        }

        if (isset($config['filters'])) {
            $config['filters'] = array_values(array_filter($config['filters'], function ($filter) {
                return !empty($filter['column']);
            }));
        }

        if (isset($config['group_by'])) {
            if (is_array($config['group_by'])) {
                $config['group_by'] = array_values(array_filter($config['group_by'], function ($item) {
                    return !empty($item['column']) || (is_string($item) && !empty($item));
                }));

                $flat = [];
                foreach ($config['group_by'] as $item) {
                    $flat[] = is_array($item) ? ($item['column'] ?? '') : $item;
                }
                $config['group_by'] = array_filter($flat);
            }
        }

        if (isset($config['sort'])) {
            $config['sort'] = array_values(array_filter($config['sort'], function ($sort) {
                return !empty($sort['column']);
            }));
        }

        return $config;
    }
}

<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;

class FormLogicService
{
    public function applyLogic(Form $form, array $answers = []): array
    {
        $fields = $form->fields()->orderBy('sort_order')->get();
        $result = [];

        foreach ($fields as $field) {
            $visibility = $this->getFieldVisibility($field, $fields, $answers);

            $result[$field->id] = [
                'field_id' => $field->id,
                'field_name' => $field->field_name ?? $field->label,
                'field_type' => $field->field_type,
                'options' => $field->options,
                'is_required' => (bool) $field->is_required,
                'is_visible' => $visibility['visible'],
                'hide_reason' => $visibility['reason'],
                'sort_order' => $field->sort_order,
            ];
        }

        return array_values($result);
    }

    public function validateLogic(FormField $field, array $rules = []): void
    {
        $parsedRules = $field->conditional_logic ?? [];
        if (empty($parsedRules)) return;

        foreach ($parsedRules as $rule) {
            $this->validateRule($rule);
        }
    }

    public function shouldShowField(FormField $field, array $answers): bool
    {
        $visibility = $this->getFieldVisibility($field, collect(), $answers);
        return $visibility['visible'];
    }

    public function getConditionalFields(Form $form): array
    {
        $fields = $form->fields()->orderBy('sort_order')->get();
        $conditional = [];

        foreach ($fields as $field) {
            $conditions = $field->conditional_logic ?? [];
            if (!empty($conditions)) {
                $conditional[] = [
                    'field_id' => $field->id,
                    'field_name' => $field->field_name ?? $field->label,
                    'conditions' => $conditions,
                ];
            }
        }

        return $conditional;
    }

    public function parseConditionalExpression(string $expression): array
    {
        $parts = explode('=', $expression);
        if (count($parts) !== 2) {
            $parts = explode('!=', $expression);
            if (count($parts) !== 2) {
                $parts = explode('>', $expression);
                if (count($parts) !== 2) {
                    $parts = explode('<', $expression);
                }
            }
        }

        return [
            'expression' => $expression,
            'field' => trim($parts[0] ?? ''),
            'value' => trim($parts[1] ?? ''),
        ];
    }

    protected function getFieldVisibility(FormField $field, $allFields, array $answers): array
    {
        $conditions = $field->conditional_logic ?? [];

        if (empty($conditions)) {
            return ['visible' => true, 'reason' => null];
        }

        $conditionType = $conditions['type'] ?? 'any';
        $rules = $conditions['rules'] ?? $conditions;

        if (empty($rules)) {
            return ['visible' => true, 'reason' => null];
        }

        $ruleResults = [];
        foreach ($rules as $rule) {
            $ruleResults[] = $this->evaluateRule($rule, $answers);
        }

        if ($conditionType === 'all') {
            $allMatch = !in_array(false, $ruleResults, true);
            return [
                'visible' => $allMatch,
                'reason' => $allMatch ? null : 'Tidak semua kondisi terpenuhi',
            ];
        }

        $anyMatch = in_array(true, $ruleResults, true);
        return [
            'visible' => $anyMatch,
            'reason' => $anyMatch ? null : 'Tidak ada kondisi yang terpenuhi',
        ];
    }

    protected function evaluateRule(array $rule, array $answers): bool
    {
        $fieldKey = $rule['field_id'] ?? $rule['field'] ?? null;
        $operator = $rule['operator'] ?? '=';
        $expectedValue = $rule['value'] ?? null;

        if ($fieldKey === null || $expectedValue === null) {
            return true;
        }

        $actualValue = $answers[$fieldKey] ?? $answers[(string) $fieldKey] ?? null;

        if ($actualValue === null) {
            return $operator === 'is_empty';
        }

        return match ($operator) {
            '=' => $actualValue == $expectedValue,
            '!=' => $actualValue != $expectedValue,
            '>' => (float) $actualValue > (float) $expectedValue,
            '<' => (float) $actualValue < (float) $expectedValue,
            '>=' => (float) $actualValue >= (float) $expectedValue,
            '<=' => (float) $actualValue <= (float) $expectedValue,
            'contains' => str_contains(strtolower((string) $actualValue), strtolower((string) $expectedValue)),
            'not_contains' => !str_contains(strtolower((string) $actualValue), strtolower((string) $expectedValue)),
            'is_empty' => empty($actualValue),
            'is_not_empty' => !empty($actualValue),
            'in' => in_array($actualValue, is_array($expectedValue) ? $expectedValue : [$expectedValue]),
            'not_in' => !in_array($actualValue, is_array($expectedValue) ? $expectedValue : [$expectedValue]),
            default => $actualValue == $expectedValue,
        };
    }

    protected function validateRule(array $rule): void
    {
        $required = ['field_id', 'operator', 'value'];
        foreach ($required as $key) {
            if (!array_key_exists($key, $rule)) {
                throw new \InvalidArgumentException("Aturan conditional logic tidak valid: '$key' tidak ditemukan");
            }
        }

        $validOperators = ['=', '!=', '>', '<', '>=', '<=', 'contains', 'not_contains', 'is_empty', 'is_not_empty', 'in', 'not_in'];
        if (!in_array($rule['operator'], $validOperators)) {
            throw new \InvalidArgumentException("Operator tidak valid: {$rule['operator']}. Gunakan: " . implode(', ', $validOperators));
        }
    }
}

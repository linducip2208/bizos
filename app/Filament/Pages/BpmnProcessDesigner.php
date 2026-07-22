<?php

namespace App\Filament\Pages;

use App\Models\BpmnProcess;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Actions\Action;

class BpmnProcessDesigner extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-pencil-square';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'BPMN Designer';

    protected static ?string $navigationLabel = 'BPMN Designer';

    protected static ?string $slug = 'bpmn-designer';

    protected string $view = 'filament.pages.bpmn-designer';

    public ?BpmnProcess $process = null;

    public string $bpmnXml = '';

    public string $processName = '';

    public static function getNavigationGroup(): ?string
    {
        return 'BPMN';
    }

    public function mount(?BpmnProcess $process = null): void
    {
        if ($process?->exists) {
            $this->process = $process;
            $this->bpmnXml = $process->bpmn_xml ?? $this->getDefaultBpmnXml();
            $this->processName = $process->name;
        } else {
            $this->bpmnXml = $this->getDefaultBpmnXml();
        }
    }

    protected function getDefaultBpmnXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<bpmn:definitions xmlns:bpmn="http://www.omg.org/spec/BPMN/20100524/MODEL"
                  xmlns:bpmndi="http://www.omg.org/spec/BPMN/20100524/DI"
                  xmlns:dc="http://www.omg.org/spec/DD/20100524/DC"
                  xmlns:di="http://www.omg.org/spec/DD/20100524/DI"
                  id="Definitions_1"
                  targetNamespace="http://bpmn.io/schema/bpmn">
  <bpmn:process id="Process_1" isExecutable="true">
    <bpmn:startEvent id="StartEvent_1" name="Mulai">
      <bpmn:outgoing>Flow_1</bpmn:outgoing>
    </bpmn:startEvent>
    <bpmn:sequenceFlow id="Flow_1" sourceRef="StartEvent_1" targetRef="Task_1"/>
    <bpmn:userTask id="Task_1" name="Task 1">
      <bpmn:incoming>Flow_1</bpmn:incoming>
      <bpmn:outgoing>Flow_2</bpmn:outgoing>
    </bpmn:userTask>
    <bpmn:sequenceFlow id="Flow_2" sourceRef="Task_1" targetRef="EndEvent_1"/>
    <bpmn:endEvent id="EndEvent_1" name="Selesai">
      <bpmn:incoming>Flow_2</bpmn:incoming>
    </bpmn:endEvent>
  </bpmn:process>
</bpmn:definitions>
XML;
    }

    protected function getHeaderActions(): array
    {
        $actions = [];

        if ($this->process?->exists) {
            $actions[] = Action::make('saveBpmn')
                ->label('Simpan BPMN')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action('saveBpmn');
        }

        return $actions;
    }

    public function saveBpmn(): void
    {
        if (!$this->process?->exists) return;

        $this->process->update([
            'bpmn_xml' => $this->bpmnXml,
            'version' => $this->process->version + 1,
        ]);

        \Filament\Notifications\Notification::make()
            ->title('BPMN berhasil disimpan')
            ->success()
            ->send();
    }
}

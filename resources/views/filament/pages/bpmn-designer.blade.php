<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ $processName ?: 'BPMN Process Designer' }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Desain proses bisnis dengan BPMN 2.0 standar</p>
            </div>
        </div>

        {{-- BPMN Toolbar --}}
        <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
            <button type="button" onclick="addElement('startEvent')" class="px-3 py-2 text-sm font-medium rounded-lg bg-green-50 text-green-700 hover:bg-green-100 border border-green-200 dark:bg-green-900/30 dark:text-green-400 dark:border-green-800 transition" title="Start Event">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" stroke-width="2"/></svg>
                <span class="ml-1 hidden sm:inline">Start</span>
            </button>
            <button type="button" onclick="addElement('endEvent')" class="px-3 py-2 text-sm font-medium rounded-lg bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800 transition" title="End Event">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="8" stroke-width="3"/></svg>
                <span class="ml-1 hidden sm:inline">End</span>
            </button>
            <button type="button" onclick="addElement('userTask')" class="px-3 py-2 text-sm font-medium rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800 transition" title="User Task">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="4" y="6" width="16" height="12" rx="3" stroke-width="2"/></svg>
                <span class="ml-1 hidden sm:inline">Task</span>
            </button>
            <button type="button" onclick="addElement('exclusiveGateway')" class="px-3 py-2 text-sm font-medium rounded-lg bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800 transition" title="Exclusive Gateway (XOR)">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="12,2 22,12 12,22 2,12" stroke-width="2"/></svg>
                <span class="ml-1 hidden sm:inline">XOR</span>
            </button>
            <button type="button" onclick="addElement('parallelGateway')" class="px-3 py-2 text-sm font-medium rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-100 border border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800 transition" title="Parallel Gateway (AND)">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><polygon points="12,2 22,12 12,22 2,12" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="16" stroke-width="2"/></svg>
                <span class="ml-1 hidden sm:inline">AND</span>
            </button>
            <button type="button" onclick="addElement('inclusiveGateway')" class="px-3 py-2 text-sm font-medium rounded-lg bg-teal-50 text-teal-700 hover:bg-teal-100 border border-teal-200 dark:bg-teal-900/30 dark:text-teal-400 dark:border-teal-800 transition" title="Inclusive Gateway (OR)">
                <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/></svg>
                <span class="ml-1 hidden sm:inline">OR</span>
            </button>
            <div class="flex-1"></div>
            <button type="button" onclick="exportBpmn()" class="px-3 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 transition">
                Export XML
            </button>
            <button type="button" onclick="importBpmn()" class="px-3 py-2 text-sm font-medium rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 border border-gray-300 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600 transition">
                Import XML
            </button>
            <input type="file" id="bpmnFileInput" accept=".xml,.bpmn" style="display:none" onchange="handleFileImport(event)">
        </div>

        {{-- BPMN Canvas --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">
            <div id="bpmn-canvas" style="min-height: 600px; height: 70vh; position: relative; background: #fafbfc;">
                <textarea
                    id="bpmnXmlEditor"
                    wire:model.live.debounce.500ms="bpmnXml"
                    style="width: 100%; height: 100%; border: none; resize: none; padding: 16px; font-family: 'JetBrains Mono', 'Fira Code', monospace; font-size: 13px; line-height: 1.6; background: #fafbfc; color: #1e293b;"
                    placeholder="Paste BPMN 2.0 XML di sini atau gunakan toolbar untuk menambah element..."
                >{{ $bpmnXml }}</textarea>
            </div>
        </div>

        {{-- BPMN Elements Summary --}}
        @if(!empty($bpmnXml))
        @php
            $bpmnService = app(\App\Services\BpmnService::class);
            try {
                $elements = $bpmnService->getBpmnElements($bpmnXml);
            } catch (\Throwable $e) {
                $elements = ['pools' => [], 'lanes' => [], 'tasks' => [], 'events' => [], 'gateways' => [], 'flows' => [], 'error' => $e->getMessage()];
            }
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @if(!empty($elements['error']))
                <div class="col-span-3 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl text-red-700 dark:text-red-400 text-sm">
                    <strong>Error:</strong> {{ $elements['error'] }}
                </div>
            @else
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events ({{ count($elements['events'] ?? []) }})</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        @foreach(($elements['events'] ?? []) as $event)
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $event['type'] === 'startEvent' ? 'bg-green-500' : ($event['type'] === 'endEvent' ? 'bg-red-500' : 'bg-yellow-500') }}"></span>
                                {{ $event['name'] ?: $event['id'] }}
                                <span class="text-xs text-gray-400">({{ $event['type'] }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Tasks ({{ count($elements['tasks'] ?? []) }})</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        @foreach(($elements['tasks'] ?? []) as $task)
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                                {{ $task['name'] ?: $task['id'] }}
                                <span class="text-xs text-gray-400">({{ $task['type'] }})</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Gateways ({{ count($elements['gateways'] ?? []) }}) & Flows ({{ count($elements['flows'] ?? []) }})</h4>
                    <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                        @foreach(($elements['gateways'] ?? []) as $gw)
                            <li class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                {{ $gw['name'] ?: $gw['id'] }}
                                <span class="text-xs text-gray-400">({{ $gw['type'] }})</span>
                            </li>
                        @endforeach
                        <li class="border-t border-gray-100 dark:border-gray-700 pt-2 mt-2 text-xs text-gray-400">
                            {{ count($elements['flows'] ?? []) }} sequence flows
                        </li>
                    </ul>
                </div>
            @endif
        </div>
        @endif

        {{-- Quick Reference --}}
        <div class="p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-xl border border-indigo-200 dark:border-indigo-800">
            <h4 class="font-semibold text-indigo-900 dark:text-indigo-300 mb-2">Panduan Cepat BPMN 2.0</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 text-sm text-indigo-700 dark:text-indigo-400">
                <div><strong>Start Event</strong>: Awal proses</div>
                <div><strong>End Event</strong>: Akhir proses</div>
                <div><strong>User Task</strong>: Tugas manual oleh user</div>
                <div><strong>Service Task</strong>: Tugas otomatis sistem</div>
                <div><strong>XOR Gateway</strong>: Pilih 1 jalur (exclusive)</div>
                <div><strong>OR Gateway</strong>: Pilih 1 atau lebih jalur (inclusive)</div>
                <div><strong>AND Gateway</strong>: Semua jalur parallel</div>
                <div><strong>Sequence Flow</strong>: Arah alur proses</div>
            </div>
        </div>
    </div>

    <script>
        let elementCounter = 10;

        function addElement(type) {
            const editor = document.getElementById('bpmnXmlEditor');
            const xml = editor.value;
            const id = type + '_' + (elementCounter++);
            const name = type.charAt(0).toUpperCase() + type.slice(1).replace(/([A-Z])/g, ' $1').trim();

            let newElement = '';

            switch(type) {
                case 'startEvent':
                    newElement = `    <bpmn:startEvent id="${id}" name="Mulai">\n      <bpmn:outgoing>Flow_${elementCounter}</bpmn:outgoing>\n    </bpmn:startEvent>\n`;
                    break;
                case 'endEvent':
                    newElement = `    <bpmn:endEvent id="${id}" name="Selesai">\n      <bpmn:incoming>Flow_${elementCounter}</bpmn:incoming>\n    </bpmn:endEvent>\n`;
                    break;
                case 'userTask':
                    newElement = `    <bpmn:userTask id="${id}" name="${name}">\n      <bpmn:incoming>Flow_${elementCounter-1}</bpmn:incoming>\n      <bpmn:outgoing>Flow_${elementCounter}</bpmn:outgoing>\n    </bpmn:userTask>\n`;
                    break;
                case 'serviceTask':
                    newElement = `    <bpmn:serviceTask id="${id}" name="${name}">\n      <bpmn:incoming>Flow_${elementCounter-1}</bpmn:incoming>\n      <bpmn:outgoing>Flow_${elementCounter}</bpmn:outgoing>\n    </bpmn:serviceTask>\n`;
                    break;
                case 'scriptTask':
                    newElement = `    <bpmn:scriptTask id="${id}" name="${name}">\n      <bpmn:incoming>Flow_${elementCounter-1}</bpmn:incoming>\n      <bpmn:outgoing>Flow_${elementCounter}</bpmn:outgoing>\n    </bpmn:scriptTask>\n`;
                    break;
                case 'exclusiveGateway':
                    newElement = `    <bpmn:exclusiveGateway id="${id}" name="${name}">\n      <bpmn:incoming>Flow_${elementCounter-1}</bpmn:incoming>\n      <bpmn:outgoing>Flow_${elementCounter}_A</bpmn:outgoing>\n      <bpmn:outgoing>Flow_${elementCounter}_B</bpmn:outgoing>\n    </bpmn:exclusiveGateway>\n`;
                    elementCounter++;
                    break;
                case 'parallelGateway':
                    newElement = `    <bpmn:parallelGateway id="${id}" name="${name}">\n      <bpmn:incoming>Flow_${elementCounter-1}</bpmn:incoming>\n      <bpmn:outgoing>Flow_${elementCounter}_A</bpmn:outgoing>\n      <bpmn:outgoing>Flow_${elementCounter}_B</bpmn:outgoing>\n    </bpmn:parallelGateway>\n`;
                    elementCounter++;
                    break;
                case 'inclusiveGateway':
                    newElement = `    <bpmn:inclusiveGateway id="${id}" name="${name}">\n      <bpmn:incoming>Flow_${elementCounter-1}</bpmn:incoming>\n      <bpmn:outgoing>Flow_${elementCounter}_A</bpmn:outgoing>\n      <bpmn:outgoing>Flow_${elementCounter}_B</bpmn:outgoing>\n    </bpmn:inclusiveGateway>\n`;
                    elementCounter++;
                    break;
                default:
                    newElement = `    <bpmn:userTask id="${id}" name="${name}">\n      <bpmn:incoming>Flow_${elementCounter-1}</bpmn:incoming>\n      <bpmn:outgoing>Flow_${elementCounter}</bpmn:outgoing>\n    </bpmn:userTask>\n`;
            }

            const flowElement = `    <bpmn:sequenceFlow id="Flow_${elementCounter}" sourceRef="${prevElementId()}" targetRef="${id}"/>\n`;

            const insertPos = xml.lastIndexOf('</bpmn:process>');
            if (insertPos !== -1) {
                const before = xml.substring(0, insertPos);
                const after = xml.substring(insertPos);
                editor.value = before + newElement + after;
            } else {
                editor.value = xml + '\n' + newElement;
            }

            editor.dispatchEvent(new Event('input', { bubbles: true }));
            editor.dispatchEvent(new Event('change', { bubbles: true }));

            showNotification('Element ' + name + ' ditambahkan');
        }

        function prevElementId() {
            return 'StartEvent_1';
        }

        function exportBpmn() {
            const editor = document.getElementById('bpmnXmlEditor');
            const blob = new Blob([editor.value], {type: 'application/xml'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'process.bpmn';
            a.click();
            URL.revokeObjectURL(url);
            showNotification('BPMN XML diexport');
        }

        function importBpmn() {
            document.getElementById('bpmnFileInput').click();
        }

        function handleFileImport(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const editor = document.getElementById('bpmnXmlEditor');
                editor.value = e.target.result;
                editor.dispatchEvent(new Event('input', { bubbles: true }));
                editor.dispatchEvent(new Event('change', { bubbles: true }));
                showNotification('BPMN XML diimport');
            };
            reader.readAsText(file);
        }

        function showNotification(message) {
            const el = document.createElement('div');
            el.className = 'fixed bottom-4 right-4 bg-gray-900 text-white px-4 py-2 rounded-lg shadow-lg text-sm z-50 animate-fade-in';
            el.textContent = message;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 2000);
        }
    </script>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fadeIn 0.3s ease-out; }
    </style>
</x-filament-panels::page>

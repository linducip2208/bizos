<div x-data="studioBuilder()" x-init="init()"
    class="h-screen flex flex-col bg-stone-50 font-sans">

    {{-- Top Bar --}}
    <header class="h-14 bg-white border-b border-stone-200 flex items-center justify-between px-4 flex-shrink-0 z-10">
        <div class="flex items-center gap-3">
            <a href="{{ url('/admin') }}" class="text-stone-400 hover:text-stone-600">&larr; Kembali</a>
            <span class="text-stone-300">|</span>
            <input type="text" wire:model.lazy="workflowName"
                class="text-lg font-bold text-stone-900 bg-transparent border-none focus:ring-0 p-0 min-w-[200px]"
                placeholder="Nama Workflow">
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-semibold">No-Code Studio</span>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="validateFlow"
                class="px-4 py-1.5 text-sm font-medium text-stone-600 bg-stone-100 rounded-lg hover:bg-stone-200 transition">
                Validasi
            </button>
            <button @click="save()"
                class="px-4 py-1.5 text-sm font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition shadow-sm">
                Simpan
            </button>
            <button @click="execute()"
                class="px-4 py-1.5 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm"
                :disabled="$wire.isExecuting">
                <span x-show="!$wire.isExecuting">Jalankan</span>
                <span x-show="$wire.isExecuting" class="flex items-center gap-1">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Running...
                </span>
            </button>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        {{-- Left Panel: Block Palette --}}
        <aside class="w-64 bg-white border-r border-stone-200 flex-shrink-0 overflow-y-auto">
            <div class="p-3 border-b border-stone-100">
                <input type="text" placeholder="Cari block..." x-model="blockSearch"
                    class="w-full px-3 py-1.5 text-sm border border-stone-200 rounded-lg focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400">
            </div>

            <div class="p-3 space-y-4">
                @php $blocks = (new \App\Services\NoCodeStudioService())->getStudioBlocks(); @endphp

                @php $categories = ['trigger' => 'Trigger', 'action' => 'Aksi', 'logic' => 'Logika', 'data' => 'Data']; @endphp

                @foreach ($categories as $catKey => $catLabel)
                    <div>
                        <h4 class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-2">{{ $catLabel }}</h4>
                        <div class="space-y-1" x-show="!blockSearch || blockSearch === '' || $el.querySelectorAll('[x-show]').length">
                            @foreach ($blocks as $block)
                                @if ($block['type'] === $catKey)
                                    <div draggable="true"
                                        @dragstart="dragStart($event, '{{ $block['type'] }}', '{{ $block['name'] }}', '{{ $block['color'] }}')"
                                        class="flex items-center gap-2 px-3 py-2 rounded-lg cursor-grab hover:shadow-sm transition border border-transparent hover:border-stone-200 bg-white text-sm"
                                        style="border-left: 3px solid {{ $block['color'] }}">
                                        <span class="text-base">{{ match($block['icon']) {
                                            'heroicon-o-bolt' => '⚡',
                                            'heroicon-o-link' => '🔗',
                                            'heroicon-o-clock' => '⏰',
                                            'heroicon-o-chat-bubble-left' => '💬',
                                            'heroicon-o-envelope' => '✉️',
                                            'heroicon-o-bell' => '🔔',
                                            'heroicon-o-clipboard-document-check' => '📋',
                                            'heroicon-o-pencil-square' => '✏️',
                                            'heroicon-o-document-plus' => '📄',
                                            'heroicon-o-globe-alt' => '🌐',
                                            'heroicon-o-arrows-right-left' => '↔️',
                                            'heroicon-o-arrow-path' => '🔄',
                                            'heroicon-o-shield-exclamation' => '🛡️',
                                            'heroicon-o-adjustments-horizontal' => '⚙️',
                                            'heroicon-o-variable' => '📦',
                                            'heroicon-o-cube' => '🧊',
                                            default => '📌',
                                        } }}</span>
                                        <span class="text-stone-700">{{ $block['name'] }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>

        {{-- Center: Canvas --}}
        <main class="flex-1 relative overflow-hidden bg-stone-100"
            x-ref="canvas"
            @drop.prevent="onDrop($event)"
            @dragover.prevent
            @click.self="deselectNode()">

            {{-- Grid Background --}}
            <svg class="absolute inset-0 w-full h-full pointer-events-none opacity-20" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#94a3b8" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)"/>
            </svg>

            {{-- SVG Edges Layer --}}
            <svg class="absolute inset-0 w-full h-full pointer-events-none z-10" x-ref="edgeSvg">
                <template x-for="edge in edges" :key="edge.id">
                    <line :x1="getNodePosition(edge.source).x"
                        :y1="getNodePosition(edge.source).y"
                        :x2="getNodePosition(edge.target).x"
                        :y2="getNodePosition(edge.target).y"
                        stroke="#94a3b8" stroke-width="2" stroke-dasharray="6,3"
                        marker-end="url(#arrowhead)"/>
                </template>
                <defs>
                    <marker id="arrowhead" markerWidth="10" markerHeight="7" refX="10" refY="3.5" orient="auto">
                        <polygon points="0 0, 10 3.5, 0 7" fill="#94a3b8"/>
                    </marker>
                </defs>
            </svg>

            {{-- Nodes --}}
            <div class="relative z-20">
                <template x-for="node in nodes" :key="node.id">
                    <div
                        :id="node.id"
                        :style="`position: absolute; left: ${node.position.x}px; top: ${node.position.y}px; width: 220px;`"
                        :class="selectedNodeId === node.id ? 'ring-2 ring-indigo-400 ring-offset-2' : ''"
                        class="bg-white rounded-xl border border-stone-200 shadow-sm hover:shadow-md transition cursor-pointer group"
                        @click.stop="selectNode(node.id)"
                        @mousedown="startDragNode($event, node.id)"
                        x-draggable>

                        {{-- Node Header --}}
                        <div class="flex items-center gap-2 px-3 py-2 rounded-t-xl text-white text-xs font-semibold"
                            :style="'background: ' + node.color">
                            <span x-text="node.icon"></span>
                            <span x-text="node.label" class="flex-1"></span>
                            <button @click.stop="removeNode(node.id)" class="text-white/70 hover:text-white/100 opacity-0 group-hover:opacity-100 transition">&times;</button>
                        </div>

                        {{-- Node Body --}}
                        <div class="px-3 py-2 text-xs text-stone-500 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full" :style="'background: ' + node.color"></span>
                                <span x-text="node.block_type" class="capitalize"></span>
                            </div>
                            <template x-if="node.block_type === 'trigger' && node.config.trigger_event">
                                <div class="truncate" x-text="'Event: ' + node.config.trigger_event"></div>
                            </template>
                        </div>

                        {{-- Connection dots --}}
                        <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-3 h-3 bg-stone-400 rounded-full border-2 border-white cursor-crosshair hover:bg-indigo-500 transition"
                            @mousedown.stop="startEdgeDrag($event, node.id)"></div>
                        <div class="absolute -top-2 left-1/2 -translate-x-1/2 w-3 h-3 bg-stone-400 rounded-full border-2 border-white cursor-crosshair hover:bg-indigo-500 transition"
                            @mouseup.stop="endEdgeDrag($event, node.id)"></div>
                    </div>
                </template>
            </div>

            {{-- Temp edge line while dragging --}}
            <svg class="absolute inset-0 w-full h-full pointer-events-none z-30" x-show="draggingEdge" x-ref="tempEdgeSvg">
            </svg>

            {{-- Empty state --}}
            <div x-show="nodes.length === 0" class="absolute inset-0 flex items-center justify-center text-stone-400 z-0">
                <div class="text-center">
                    <p class="text-lg font-semibold">Seret block dari panel kiri ke sini</p>
                    <p class="text-sm mt-1">Mulai dengan menambahkan Trigger, lalu sambungkan ke Action</p>
                </div>
            </div>
        </main>

        {{-- Right Panel: Node Config --}}
        <aside class="w-72 bg-white border-l border-stone-200 flex-shrink-0 overflow-y-auto" x-show="selectedNodeId" x-transition>
            <div class="p-4">
                <template x-if="selectedNode">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-stone-800" x-text="selectedNode.label"></h3>
                            <button @click="deselectNode()" class="text-stone-400 hover:text-stone-600">&times;</button>
                        </div>

                        <p class="text-xs text-stone-500" x-text="'Type: ' + selectedNode.block_type"></p>

                        {{-- Block-specific config --}}
                        <template x-if="selectedNode.block_name === 'Event Trigger'">
                            <div>
                                <label class="text-xs font-medium text-stone-600">Trigger Event</label>
                                <select class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg"
                                    @change="updateNodeConfig('trigger_event', $event.target.value)">
                                    <option value="">Pilih Event</option>
                                    @foreach ((new \App\Services\WorkflowAutomationService())->getAvailableTriggers() as $trigger)
                                        <option value="{{ $trigger['event'] }}">{{ $trigger['label'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Kirim WA'">
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Nomor Tujuan</label>
                                    <input type="text" @change="updateNodeConfig('to', $event.target.value)"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Pesan</label>
                                    <textarea @change="updateNodeConfig('message', $event.target.value)" rows="3"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg"></textarea>
                                    <p class="text-xs text-stone-400 mt-1">Gunakan {&#123;field&#125;} untuk variabel</p>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Kirim Email'">
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Email Tujuan</label>
                                    <input type="text" @change="updateNodeConfig('to', $event.target.value)"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Subjek</label>
                                    <input type="text" @change="updateNodeConfig('subject', $event.target.value)"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Kirim Notifikasi'">
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Judul</label>
                                    <input type="text" @change="updateNodeConfig('title', $event.target.value)"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                                </div>
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Isi</label>
                                    <textarea @change="updateNodeConfig('body', $event.target.value)" rows="3"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg"></textarea>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Buat Tugas'">
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Judul Tugas</label>
                                    <input type="text" @change="updateNodeConfig('title', $event.target.value)"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Webhook'">
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs font-medium text-stone-600">URL</label>
                                    <input type="text" @change="updateNodeConfig('url', $event.target.value)"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Delay'">
                            <div>
                                <label class="text-xs font-medium text-stone-600">Menit</label>
                                <input type="number" @change="updateNodeConfig('minutes', $event.target.value)" value="1"
                                    class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Loop'">
                            <div class="space-y-2">
                                <div>
                                    <label class="text-xs font-medium text-stone-600">Tipe Loop</label>
                                    <select @change="updateNodeConfig('type', $event.target.value)"
                                        class="w-full mt-1 px-2 py-1.5 text-xs border border-stone-200 rounded-lg">
                                        <option value="fixed">Fixed</option>
                                        <option value="while">While</option>
                                        <option value="for_each">For Each</option>
                                    </select>
                                </div>
                            </div>
                        </template>

                        <template x-if="selectedNode.block_name === 'Webhook Trigger'">
                            <div class="space-y-2">
                                <button @click="$wire.generateWebhookUrl()"
                                    class="w-full px-3 py-2 text-xs font-medium text-white bg-violet-600 rounded-lg hover:bg-violet-700 transition">
                                    Generate Webhook URL
                                </button>
                            </div>
                        </template>

                        {{-- Generic config viewer --}}
                        <template x-if="selectedNode.config && Object.keys(selectedNode.config).length > 0">
                            <div>
                                <h4 class="text-xs font-semibold text-stone-500 uppercase mb-2">Config</h4>
                                <pre class="text-xs bg-stone-50 p-2 rounded overflow-auto max-h-32" x-text="JSON.stringify(selectedNode.config, null, 2)"></pre>
                            </div>
                        </template>

                        <button @click="removeNode(selectedNode.id)"
                            class="w-full mt-4 px-3 py-2 text-xs font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition">
                            Hapus Node
                        </button>
                    </div>
                </template>

                <template x-if="!selectedNode">
                    <div class="text-center text-stone-400 py-8">
                        <p class="text-sm">Pilih node untuk konfigurasi</p>
                    </div>
                </template>
            </div>
        </aside>
    </div>

    {{-- Bottom: Execution Result --}}
    @if ($executionResult)
        <div class="h-48 bg-stone-900 border-t border-stone-700 flex-shrink-0 overflow-auto">
            <div class="flex items-center justify-between px-4 py-2 border-b border-stone-700">
                <span class="text-xs font-semibold text-stone-400">Hasil Eksekusi</span>
                <button wire:click="$set('executionResult', '')" class="text-stone-500 hover:text-stone-300 text-xs">&times; Tutup</button>
            </div>
            <pre class="p-4 text-xs text-green-400 font-mono whitespace-pre-wrap">{{ $executionResult }}</pre>
        </div>
    @endif

    {{-- Validation Errors --}}
    @if (!empty($validationErrors))
        <div class="h-32 bg-red-50 border-t border-red-200 flex-shrink-0 overflow-auto p-4">
            <h4 class="text-sm font-semibold text-red-700 mb-2">Error Validasi ({{ count($validationErrors) }})</h4>
            <ul class="space-y-1">
                @foreach ($validationErrors as $error)
                    <li class="text-xs text-red-600">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>

<script>
function studioBuilder() {
    return {
        nodes: @json($nodes ?? []),
        edges: @json($edges ?? []),
        selectedNodeId: @json($selectedNodeId ?? null),
        blockSearch: '',
        draggingEdge: false,
        edgeStartNodeId: null,
        dragOffsetX: 0,
        dragOffsetY: 0,
        draggingNodeId: null,

        init() {
            this.$watch('nodes', () => {
                this.$wire.set('nodes', this.nodes);
            });
            this.$watch('edges', () => {
                this.$wire.set('edges', this.edges);
            });
            this.$watch('selectedNodeId', (val) => {
                if (val) {
                    this.$wire.selectNode(val);
                } else {
                    this.$wire.deselectNode();
                }
            });
        },

        get selectedNode() {
            return this.nodes.find(n => n.id === this.selectedNodeId) || null;
        },

        getNodePosition(nodeId) {
            const nodeElement = document.getElementById(nodeId);
            if (!nodeElement) return { x: 0, y: 0 };
            const rect = nodeElement.getBoundingClientRect();
            const canvas = this.$refs.canvas.getBoundingClientRect();
            return {
                x: rect.left - canvas.left + rect.width / 2,
                y: rect.top - canvas.top + rect.height / 2,
            };
        },

        dragStart(event, blockType, blockName, color) {
            event.dataTransfer.setData('text/plain', JSON.stringify({ blockType, blockName, color }));
            event.dataTransfer.effectAllowed = 'move';
        },

        onDrop(event) {
            const canvas = this.$refs.canvas.getBoundingClientRect();
            const x = event.clientX - canvas.left - 110;
            const y = event.clientY - canvas.top - 30;

            let data;
            try {
                data = JSON.parse(event.dataTransfer.getData('text/plain'));
            } catch (e) {
                return;
            }

            this.$wire.addNode(data.blockType, data.blockName, data.color).then(() => {
                const lastNode = this.nodes[this.nodes.length - 1];
                if (lastNode) {
                    lastNode.position = { x: Math.max(10, x), y: Math.max(10, y) };
                    this.$wire.set('nodes', this.nodes);
                }
            });
        },

        selectNode(nodeId) {
            this.selectedNodeId = nodeId;
        },

        deselectNode() {
            this.selectedNodeId = null;
            this.$wire.deselectNode();
        },

        removeNode(nodeId) {
            this.nodes = this.nodes.filter(n => n.id !== nodeId);
            this.edges = this.edges.filter(e => e.source !== nodeId && e.target !== nodeId);
            if (this.selectedNodeId === nodeId) {
                this.selectedNodeId = null;
            }
            this.$wire.handleRemoveNode(nodeId);
        },

        updateNodeConfig(key, value) {
            if (!this.selectedNodeId) return;
            const node = this.nodes.find(n => n.id === this.selectedNodeId);
            if (node) {
                if (!node.config) node.config = {};
                node.config[key] = value;
                this.nodes = [...this.nodes];
                this.$wire.updateNodeConfig(key, value);
            }
        },

        startDragNode(event, nodeId) {
            if (event.target.closest('button')) return;
            this.draggingNodeId = nodeId;
            const node = this.nodes.find(n => n.id === nodeId);
            if (!node) return;
            this.dragOffsetX = event.clientX - node.position.x;
            this.dragOffsetY = event.clientY - node.position.y;

            const onMove = (e) => {
                if (!this.draggingNodeId) return;
                const canvas = this.$refs.canvas.getBoundingClientRect();
                const node = this.nodes.find(n => n.id === this.draggingNodeId);
                if (node) {
                    node.position = {
                        x: Math.max(0, e.clientX - this.dragOffsetX),
                        y: Math.max(0, e.clientY - this.dragOffsetY),
                    };
                    this.nodes = [...this.nodes];
                }
            };

            const onUp = () => {
                this.draggingNodeId = null;
                if (this.draggingNodeId) {
                    const node = this.nodes.find(n => n.id === this.draggingNodeId);
                    if (node) {
                        this.$wire.handleNodeMoved(this.draggingNodeId, node.position.x, node.position.y);
                    }
                }
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

        startEdgeDrag(event, sourceNodeId) {
            event.preventDefault();
            event.stopPropagation();
            this.draggingEdge = true;
            this.edgeStartNodeId = sourceNodeId;

            const canvas = this.$refs.canvas.getBoundingClientRect();
            const sourcePos = this.getNodePosition(sourceNodeId);

            const onMove = (e) => {
                const tx = e.clientX - canvas.left;
                const ty = e.clientY - canvas.top;
                const svg = this.$refs.tempEdgeSvg;
                svg.innerHTML = `<line x1="${sourcePos.x}" y1="${sourcePos.y}" x2="${tx}" y2="${ty}" stroke="#6366f1" stroke-width="2" stroke-dasharray="6,3" />`;
            };

            const onUp = (e) => {
                this.draggingEdge = false;
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
                this.$refs.tempEdgeSvg.innerHTML = '';

                const targetEl = document.elementFromPoint(e.clientX, e.clientY);
                if (!targetEl) return;

                const nodeEl = targetEl.closest('[x-draggable], [id^="node_"]');
                if (nodeEl) {
                    const targetNodeId = nodeEl.id;
                    if (targetNodeId && targetNodeId !== sourceNodeId) {
                        const exists = this.edges.some(e => e.source === sourceNodeId && e.target === targetNodeId);
                        if (!exists) {
                            this.edges.push({
                                id: 'edge_' + Math.random().toString(36).substring(2, 10),
                                source: sourceNodeId,
                                target: targetNodeId,
                            });
                            this.$wire.handleConnectNodes(sourceNodeId, targetNodeId);
                        }
                    }
                }
            };

            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        },

        endEdgeDrag(event, targetNodeId) {
            if (!this.draggingEdge || !this.edgeStartNodeId) return;
            this.draggingEdge = false;

            const sourceNodeId = this.edgeStartNodeId;
            this.edgeStartNodeId = null;
            this.$refs.tempEdgeSvg.innerHTML = '';

            if (sourceNodeId !== targetNodeId) {
                const exists = this.edges.some(e => e.source === sourceNodeId && e.target === targetNodeId);
                if (!exists) {
                    this.edges.push({
                        id: 'edge_' + Math.random().toString(36).substring(2, 10),
                        source: sourceNodeId,
                        target: targetNodeId,
                    });
                    this.$wire.handleConnectNodes(sourceNodeId, targetNodeId);
                }
            }
        },

        save() {
            this.$wire.saveWorkflow();
        },

        execute() {
            this.$wire.executeWorkflow();
        },
    };
}
</script>

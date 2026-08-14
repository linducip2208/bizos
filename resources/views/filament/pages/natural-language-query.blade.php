<x-filament-panels::page>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <style>
        @keyframes floatSlow { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        .animate-float-slow { animation: floatSlow 5s ease-in-out infinite; }
    </style>

    <div x-data="nlQuery()" x-cloak class="mx-auto max-w-7xl">
        {{-- Header intro --}}
        <div class="fi-section rounded-2xl bg-gradient-to-r from-indigo-600 to-violet-600 p-6 shadow-sm dark:from-indigo-700 dark:to-violet-700">
            <div class="flex items-center gap-3 text-white">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-2xl">🤖</span>
                <div>
                    <h2 class="text-lg font-bold">Tanya BizOS dalam Bahasa Indonesia</h2>
                    <p class="text-sm text-indigo-100">Ketik pertanyaan, AI mengubahnya menjadi query database dan menampilkan hasil sesuai hak akses Anda.</p>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- ===================== CHAT AREA ===================== --}}
            <div class="lg:col-span-2">
                <div class="fi-section flex min-h-[480px] flex-col rounded-2xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                    {{-- Messages --}}
                    <div x-ref="chatBox" class="flex-1 space-y-4 overflow-y-auto p-5" style="max-height: 60vh;">
                        {{-- Empty state --}}
                        <template x-if="messages.length === 0 && !loading">
                            <div class="flex h-full flex-col items-center justify-center gap-4 py-12 text-center">
                                <span class="text-5xl animate-float-slow">💬</span>
                                <p class="max-w-md text-sm text-gray-500 dark:text-gray-400">
                                    Contoh pertanyaan yang bisa Anda tanyakan: penjualan, pengeluaran, stok, karyawan, piutang, pelanggan, supplier, hingga saldo bank.
                                </p>
                            </div>
                        </template>

                        {{-- Messages loop --}}
                        <template x-for="(m, idx) in messages" :key="idx">
                            <div>
                                {{-- User bubble --}}
                                <template x-if="m.role === 'user'">
                                    <div class="flex justify-end">
                                        <div class="max-w-[80%] rounded-2xl rounded-br-sm bg-indigo-600 px-4 py-2.5 text-sm text-white shadow-sm">
                                            <span x-text="m.text"></span>
                                        </div>
                                    </div>
                                </template>

                                {{-- AI bubble --}}
                                <template x-if="m.role === 'ai'">
                                    <div class="flex justify-start">
                                        <div class="max-w-[92%] rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-3 text-sm text-gray-800 shadow-sm dark:bg-gray-700 dark:text-gray-100">
                                            <div class="whitespace-pre-wrap leading-relaxed" x-html="m.text"></div>

                                            {{-- Chart --}}
                                            <template x-if="m.chart_type && m.chart_labels && m.chart_labels.length">
                                                <div class="mt-4 rounded-xl bg-white p-3 dark:bg-gray-800">
                                                    <div class="relative" style="height: 240px;">
                                                        <canvas :id="m.chartId"></canvas>
                                                    </div>
                                                </div>
                                            </template>

                                            {{-- Data table --}}
                                            <template x-if="m.columns && m.columns.length && m.data && m.data.length">
                                                <div class="mt-4 overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-600">
                                                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                                        <thead>
                                                            <tr class="bg-gray-50 dark:bg-gray-800">
                                                                <template x-for="col in m.columns" :key="col.key">
                                                                    <th class="whitespace-nowrap px-3 py-2 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400" x-text="col.label"></th>
                                                                </template>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                            <template x-for="(row, ri) in m.data" :key="ri">
                                                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                                                    <template x-for="col in m.columns" :key="col.key">
                                                                        <td class="whitespace-nowrap px-3 py-2 text-sm text-gray-700 dark:text-gray-300">
                                                                            <a x-if="row.url && col.key === 'title'" :href="row.url" target="_blank" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400" x-text="fmtCell(row[col.key])"></a>
                                                                            <span x-show="!(row.url && col.key === 'title')" x-text="fmtCell(row[col.key])"></span>
                                                                        </td>
                                                                    </template>
                                                                </tr>
                                                            </template>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </template>

                                            <div class="mt-2 text-right text-[10px] text-gray-400 dark:text-gray-500" x-text="'Intent: ' + m.intent"></div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Loading indicator --}}
                        <div x-show="loading" class="flex justify-start">
                            <div class="flex items-center gap-2 rounded-2xl rounded-bl-sm bg-gray-100 px-4 py-3 dark:bg-gray-700">
                                <span class="flex gap-1">
                                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay: 0ms"></span>
                                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay: 120ms"></span>
                                    <span class="h-2 w-2 animate-bounce rounded-full bg-indigo-400" style="animation-delay: 240ms"></span>
                                </span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Menganalisis data…</span>
                            </div>
                        </div>
                    </div>

                    {{-- Suggestion chips --}}
                    <div class="border-t border-gray-200 px-5 py-3 dark:border-gray-700">
                        <div class="mb-2 text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">Coba tanyakan</div>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="s in suggestions" :key="s">
                                <button
                                    type="button"
                                    @click="ask(s)"
                                    class="rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 transition-all hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:border-indigo-500 dark:hover:bg-indigo-950/40"
                                    x-text="s">
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Input form --}}
                    <div class="border-t border-gray-200 p-4 dark:border-gray-700">
                        <form @submit.prevent="ask(input)" class="flex items-end gap-2">
                            <textarea
                                x-model="input"
                                rows="1"
                                placeholder="Ketik pertanyaan Anda, mis. 'Berapa penjualan bulan ini?'"
                                @keydown.enter="handleEnter($event)"
                                class="max-h-40 flex-1 resize-none rounded-xl border-gray-300 bg-white px-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
                            <button
                                type="submit"
                                :disabled="loading || !input.trim()"
                                class="inline-flex h-11 items-center gap-2 rounded-xl bg-indigo-600 px-4 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-show="!loading">Kirim</span>
                                <span x-show="loading" class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span>
                            </button>
                        </form>
                        <p class="mt-1.5 text-[11px] text-gray-400 dark:text-gray-500">Hasil hanya menampilkan data milik perusahaan & cakupan akses Anda.</p>
                    </div>
                </div>
            </div>

            {{-- ===================== HISTORY SIDEBAR ===================== --}}
            <aside>
                <div class="fi-section rounded-2xl bg-white p-5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Riwayat Pertanyaan</h3>
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-500 dark:bg-gray-700 dark:text-gray-400" x-text="history.length"></span>
                    </div>

                    <template x-if="history.length === 0">
                        <p class="py-8 text-center text-sm text-gray-400 dark:text-gray-500">Belum ada riwayat.</p>
                    </template>

                    <div class="max-h-[70vh] space-y-2 overflow-y-auto">
                        <template x-for="h in history" :key="h.id">
                            <button
                                type="button"
                                @click="ask(h.question)"
                                class="w-full rounded-xl border border-transparent px-3 py-2 text-left transition-all hover:border-gray-200 hover:bg-gray-50 dark:hover:border-gray-600 dark:hover:bg-gray-700/40">
                                <div class="truncate text-xs font-semibold text-gray-700 dark:text-gray-200" x-text="h.question"></div>
                                <div class="mt-0.5 truncate text-[11px] text-gray-400 dark:text-gray-500" x-text="h.answer"></div>
                                <div class="mt-1 text-[10px] text-gray-300 dark:text-gray-600" x-text="h.at"></div>
                            </button>
                        </template>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <script>
        window.__nlCharts = {};

        const NL_CSRF = @json(csrf_token());
        const NL_RUN_URL = @json(route('nl-query.run'));
        const NL_HISTORY_URL = @json(route('nl-query.history'));

        function fmtCell(v) {
            if (typeof v === 'number') {
                return v.toLocaleString('id-ID');
            }
            return v ?? '-';
        }

        function chartColors(n, isDoughnut) {
            if (!isDoughnut) {
                return 'rgba(99, 102, 241, 0.85)';
            }
            const palette = [
                'rgba(99, 102, 241, 0.8)',
                'rgba(16, 185, 129, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(239, 68, 68, 0.8)',
                'rgba(139, 92, 246, 0.8)',
                'rgba(6, 182, 212, 0.8)',
                'rgba(236, 72, 153, 0.8)',
                'rgba(132, 204, 22, 0.8)',
            ];
            const colors = [];
            for (let i = 0; i < n; i++) {
                colors.push(palette[i % palette.length]);
            }
            return colors;
        }

        function nlQuery() {
            return {
                messages: [],
                loading: false,
                input: '',
                suggestions: @json($suggestions),
                history: @json($history),

                async ask(question) {
                    question = (question || this.input).trim();
                    if (!question || this.loading) {
                        return;
                    }
                    this.messages.push({ role: 'user', text: question });
                    this.input = '';
                    this.loading = true;
                    this.scrollToBottom();

                    try {
                        const res = await fetch(NL_RUN_URL, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': NL_CSRF,
                            },
                            body: JSON.stringify({ question: question }),
                        });

                        const payload = await res.json();

                        if (payload.success && payload.result) {
                            const r = payload.result;
                            this.messages.push({
                                role: 'ai',
                                text: r.answer_text,
                                intent: r.intent,
                                chart_type: r.chart_type,
                                chart_labels: r.chart_labels,
                                chart_data: r.chart_data,
                                columns: r.columns,
                                data: r.data,
                                chartId: 'nlchart-' + Date.now() + '-' + Math.floor(Math.random() * 1000),
                            });
                        } else {
                            this.messages.push({
                                role: 'ai',
                                text: payload.message || 'Terjadi kesalahan. Silakan coba lagi.',
                            });
                        }
                    } catch (e) {
                        this.messages.push({
                            role: 'ai',
                            text: 'Tidak dapat terhubung ke server. Periksa koneksi Anda.',
                        });
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            this.renderCharts();
                            this.scrollToBottom();
                        });
                        this.refreshHistory();
                    }
                },

                handleEnter(e) {
                    if (!e.shiftKey) {
                        e.preventDefault();
                        this.ask(this.input);
                    }
                },

                renderCharts() {
                    this.messages.forEach((m) => {
                        if (!m.chart_type || !m.chartId) return;
                        if (window.__nlCharts[m.chartId]) return;
                        const el = document.getElementById(m.chartId);
                        if (!el) return;

                        const isDark = document.documentElement.classList.contains('dark');
                        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
                        const textColor = isDark ? '#9ca3af' : '#6b7280';
                        const isDoughnut = m.chart_type === 'doughnut';

                        window.__nlCharts[m.chartId] = new Chart(el.getContext('2d'), {
                            type: isDoughnut ? 'doughnut' : 'bar',
                            data: {
                                labels: m.chart_labels || [],
                                datasets: [{
                                    label: 'Nilai',
                                    data: m.chart_data || [],
                                    backgroundColor: chartColors((m.chart_data || []).length, isDoughnut),
                                    borderColor: isDoughnut ? 'transparent' : 'rgba(99, 102, 241, 1)',
                                    borderWidth: 1,
                                    borderRadius: isDoughnut ? 0 : 6,
                                }],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: { display: isDoughnut, position: 'bottom', labels: { color: textColor, boxWidth: 10, font: { size: 10 } } },
                                },
                                scales: isDoughnut ? {} : {
                                    x: { grid: { color: gridColor }, ticks: { color: textColor, maxRotation: 45, font: { size: 10 } } },
                                    y: { grid: { color: gridColor }, ticks: { color: textColor, font: { size: 10 } } },
                                },
                            },
                        });
                    });
                },

                scrollToBottom() {
                    this.$nextTick(() => {
                        const box = this.$refs.chatBox;
                        if (box) {
                            box.scrollTop = box.scrollHeight;
                        }
                    });
                },

                async refreshHistory() {
                    try {
                        const res = await fetch(NL_HISTORY_URL, {
                            headers: { 'Accept': 'application/json' },
                        });
                        const payload = await res.json();
                        this.history = payload.history || [];
                    } catch (e) {
                        // ignore
                    }
                },
            };
        }
    </script>
</x-filament-panels::page>

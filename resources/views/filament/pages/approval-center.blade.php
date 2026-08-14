<div x-data="approvalCenter()" x-init="init()" class="w-full">
    {{-- Header --}}
    <div class="mb-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Pusat Persetujuan
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Kelola semua persetujuan yang menunggu tindakan Anda dalam satu tempat.
                </p>
            </div>
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-danger-50 px-3 py-1 text-xs font-semibold text-danger-600 dark:bg-danger-500/10 dark:text-danger-400">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-danger-400 opacity-75"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-danger-500"></span>
                    </span>
                    <span x-text="pendingCount"></span> Menunggu
                </span>
                <button @click="refreshData()" class="fi-btn relative inline-flex items-center justify-center rounded-lg px-3 py-2 text-sm font-medium text-gray-700 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" :class="{'animate-spin': refreshing}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182"/>
                    </svg>
                    Refresh
                </button>
            </div>
        </div>
    </div>

    {{-- Module Filter Tabs --}}
    <div class="mb-5 flex flex-wrap items-center gap-2">
        <template x-for="mod in ['all', ...moduleList]" :key="mod">
            <button
                @click="activeModule = mod"
                :class="activeModule === mod
                    ? 'bg-primary-600 text-white shadow-sm'
                    : 'bg-white text-gray-600 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-800 dark:text-gray-300 dark:ring-gray-600 dark:hover:bg-gray-700'"
                class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold transition-colors"
            >
                <span x-text="moduleLabel(mod)"></span>
                <span x-show="moduleCounts[mod] > 0 && mod !== 'all'"
                      x-text="moduleCounts[mod]"
                      class="inline-flex items-center justify-center rounded-full bg-white/20 px-1.5 py-0.5 text-[10px] font-bold leading-none"
                      :class="activeModule === mod ? 'bg-white/20' : 'bg-gray-200 dark:bg-gray-600'"></span>
                <span x-show="mod === 'all' && pendingCount > 0"
                      x-text="pendingCount"
                      class="inline-flex items-center justify-center rounded-full bg-white/20 px-1.5 py-0.5 text-[10px] font-bold leading-none"></span>
            </button>
        </template>
    </div>

    {{-- Tab Navigation --}}
    <div class="mb-5 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex space-x-6" aria-label="Tabs">
            <button @click="activeTab = 'pending'"
                    :class="activeTab === 'pending'
                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="inline-flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-semibold transition-colors">
                Menunggu
                <span x-show="pendingCount > 0"
                      x-text="pendingCount"
                      class="inline-flex items-center justify-center rounded-full bg-danger-100 px-2 py-0.5 text-xs font-bold text-danger-700 dark:bg-danger-500/20 dark:text-danger-400"></span>
            </button>
            <button @click="activeTab = 'history'"
                    :class="activeTab === 'history'
                        ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                        : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="inline-flex items-center gap-2 border-b-2 px-1 pb-3 text-sm font-semibold transition-colors">
                Riwayat
                <span x-text="historyCount"
                      class="inline-flex items-center justify-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-300"></span>
            </button>
        </nav>
    </div>

    {{-- Empty State --}}
    <div x-show="filteredApprovals().length === 0 && !loading" class="rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-6 py-16 text-center dark:border-gray-700 dark:bg-gray-800/30">
        <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-14 w-14 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
        </svg>
        <h3 x-show="activeTab === 'pending'" class="mt-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Tidak ada persetujuan menunggu</h3>
        <h3 x-show="activeTab === 'history'" class="mt-4 text-lg font-semibold text-gray-700 dark:text-gray-300">Belum ada riwayat persetujuan</h3>
        <p x-show="activeTab === 'pending'" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Semua pengajuan yang membutuhkan persetujuan Anda akan muncul di sini.
        </p>
        <p x-show="activeTab === 'history'" class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Riwayat persetujuan yang sudah Anda proses akan muncul di sini.
        </p>
    </div>

    {{-- Loading --}}
    <div x-show="loading" class="py-12 text-center">
        <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-primary-500 border-r-transparent align-[-0.125em]"></div>
        <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">Memuat data...</p>
    </div>

    {{-- Approval Cards Grid --}}
    <div x-show="filteredApprovals().length > 0 && !loading" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <template x-for="item in filteredApprovals()" :key="item.module + '-' + item.module_id">
            <div class="group relative flex flex-col rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800"
                 :class="item.status === 'pending' || item.status === 'pending_approval'
                     ? ''
                     : 'opacity-75'">
                {{-- Module Badge --}}
                <div class="mb-3 flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wider"
                          :class="colorClass(item.color)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" x-bind:d="iconPath(item.icon)"/>
                        </svg>
                        <span x-text="item.module_label"></span>
                    </span>

                    {{-- Status Badge --}}
                    <span x-show="activeTab === 'history'"
                          :class="item.status === 'approved' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400'"
                          class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-[10px] font-semibold uppercase">
                        <svg x-show="item.status === 'approved'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        <svg x-show="item.status !== 'approved'" xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        <span x-text="item.status === 'approved' ? 'Disetujui' : 'Ditolak'"></span>
                    </span>
                </div>

                {{-- Title --}}
                <h3 class="mb-2 text-sm font-semibold leading-snug text-gray-900 dark:text-white" x-text="item.title"></h3>

                {{-- Meta Info --}}
                <div class="mb-4 space-y-1.5">
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                        </svg>
                        <span class="truncate" x-text="item.requester_name"></span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                        </svg>
                        <span x-text="item.submitted_at || '-'"></span>
                    </div>
                </div>

                {{-- Amount (if exists) --}}
                <div x-show="item.amount" class="mb-4 rounded-lg bg-gray-50 px-3 py-2 dark:bg-gray-700/50">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Jumlah</span>
                    <p class="text-sm font-bold text-gray-900 dark:text-white"
                       x-text="'Rp ' + formatNumber(item.amount)"></p>
                </div>

                {{-- Spacer --}}
                <div class="flex-1"></div>

                {{-- Action Buttons (Pending only) --}}
                <div x-show="activeTab === 'pending'" class="flex items-center gap-2 border-t border-gray-100 pt-3.5 dark:border-gray-700">
                    <button @click="approve(item.module, item.module_id)"
                            :disabled="processingMap[item.module + '-' + item.module_id]"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                        </svg>
                        Setujui
                    </button>

                    <button @click="promptRejection(item.module, item.module_id)"
                            :disabled="processingMap[item.module + '-' + item.module_id]"
                            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors dark:border-red-500/30 dark:bg-gray-800 dark:text-red-400 dark:hover:bg-red-500/10">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                        </svg>
                        Tolak
                    </button>

                    <a :href="item.url"
                       class="inline-flex items-center justify-center rounded-lg border border-gray-200 p-2 text-gray-400 hover:bg-gray-50 hover:text-gray-600 dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                        </svg>
                    </a>
                </div>
            </div>
        </template>
    </div>

    {{-- Rejection Reason Modal --}}
    <div x-show="showRejectModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" @click="showRejectModal = false"></div>
        <div class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.outside="showRejectModal = false">
            <div class="mb-5 flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-500/20">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Konfirmasi Penolakan</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Berikan alasan penolakan</p>
                </div>
            </div>

            <textarea x-model="rejectReason"
                      rows="3"
                      placeholder="Masukkan alasan penolakan..."
                      class="w-full rounded-xl border border-gray-300 px-4 py-3 text-sm focus:border-red-500 focus:ring-2 focus:ring-red-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-red-400"></textarea>

            <div class="mt-4 flex items-center justify-end gap-3">
                <button @click="showRejectModal = false"
                        class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Batal
                </button>
                <button @click="confirmReject()"
                        :disabled="processingReject"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                    Tolak
                </button>
            </div>
        </div>
    </div>

    {{-- Toast notification --}}
    <div x-show="toast.show" x-transition class="fixed bottom-6 right-6 z-50 flex items-center gap-3 rounded-xl px-5 py-3.5 shadow-lg"
         :class="toast.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-red-600 text-white'"
         x-init="if (toast.show) setTimeout(() => toast.show = false, 4000)">
        <svg x-show="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
        </svg>
        <svg x-show="toast.type === 'error'" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
        </svg>
        <span class="text-sm font-medium" x-text="toast.message"></span>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
function approvalCenter() {
    return {
        pendingCount: {{ $pendingCount }},
        historyCount: {{ count($approvalHistory) }},
        moduleList: @json($modules),
        moduleCounts: @json($this->getModuleCounts()),
        activeTab: '{{ $activeTab }}',
        activeModule: '{{ $activeModule }}',
        pendingApprovals: @json($pendingApprovals),
        approvalHistory: @json($approvalHistory),
        loading: false,
        refreshing: false,
        processingMap: {},
        showRejectModal: false,
        processingReject: false,
        rejectItem: null,
        rejectReason: '',
        toast: { show: false, type: 'success', message: '' },
        pollInterval: null,

        init() {
            this.startPolling();
        },

        startPolling() {
            this.pollInterval = setInterval(() => {
                this.refreshData(true);
            }, 60000);
        },

        filteredApprovals() {
            const items = this.activeTab === 'pending' ? this.pendingApprovals : this.approvalHistory;
            if (this.activeModule === 'all') return items;
            return items.filter(item => (item.module || '') === this.activeModule);
        },

        async refreshData(silent = false) {
            if (!silent) this.refreshing = true;
            this.loading = !silent ? false : this.loading;
            try {
                const resp = await fetch('/admin/approval-center/data');
                const data = await resp.json();
                this.pendingApprovals = data.pendingApprovals || [];
                this.approvalHistory = data.approvalHistory || [];
                this.pendingCount = data.pendingCount || 0;
                this.historyCount = data.historyCount || 0;
                this.moduleList = data.modules || [];
                this.moduleCounts = data.moduleCounts || { all: this.pendingCount };
            } catch (e) {
                console.error('Refresh failed:', e);
            } finally {
                this.loading = false;
                this.refreshing = false;
            }
        },

        async approve(module, id) {
            const key = `${module}-${id}`;
            this.processingMap[key] = true;
            try {
                const resp = await fetch('/admin/approval-center/approve', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ module, id }),
                });
                const data = await resp.json();
                if (data.success) {
                    this.showToast('success', 'Pengajuan berhasil disetujui.');
                    await this.refreshData(true);
                } else {
                    this.showToast('error', data.message || 'Gagal menyetujui.');
                }
            } catch (e) {
                this.showToast('error', 'Terjadi kesalahan saat menyetujui.');
            } finally {
                delete this.processingMap[key];
            }
        },

        promptRejection(module, id) {
            this.rejectItem = { module, id };
            this.rejectReason = '';
            this.showRejectModal = true;
        },

        async confirmReject() {
            if (!this.rejectItem) return;
            this.processingReject = true;
            const { module, id } = this.rejectItem;
            const key = `${module}-${id}`;
            this.processingMap[key] = true;
            try {
                const resp = await fetch('/admin/approval-center/reject', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    },
                    body: JSON.stringify({ module, id, reason: this.rejectReason }),
                });
                const data = await resp.json();
                if (data.success) {
                    this.showToast('success', 'Pengajuan berhasil ditolak.');
                    this.showRejectModal = false;
                    this.rejectItem = null;
                    await this.refreshData(true);
                } else {
                    this.showToast('error', data.message || 'Gagal menolak.');
                }
            } catch (e) {
                this.showToast('error', 'Terjadi kesalahan saat menolak.');
            } finally {
                this.processingReject = false;
                this.showRejectModal = false;
                this.rejectItem = null;
                delete this.processingMap[key];
            }
        },

        showToast(type, message) {
            this.toast = { show: true, type, message };
            setTimeout(() => this.toast.show = false, 4000);
        },

        formatNumber(value) {
            if (!value) return '0';
            return new Intl.NumberFormat('id-ID').format(value);
        },

        moduleLabel(mod) {
            const labels = {
                all: 'Semua',
                leave: 'Cuti',
                overtime: 'Lembur',
                reimbursement: 'Reimburse',
                purchase_requisition: 'PR',
                purchase_order: 'PO',
                budget: 'Anggaran',
                payment: 'Pembayaran',
                contract: 'Kontrak',
                intercompany_transaction: 'Antar Perusahaan',
                payroll: 'Penggajian',
            };
            return labels[mod] || mod;
        },

        colorClass(color) {
            const classes = {
                blue: 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                gray: 'bg-gray-100 text-gray-700 dark:bg-gray-500/10 dark:text-gray-400',
                amber: 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                rose: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
                indigo: 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-400',
                emerald: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                purple: 'bg-purple-50 text-purple-700 dark:bg-purple-500/10 dark:text-purple-400',
                cyan: 'bg-cyan-50 text-cyan-700 dark:bg-cyan-500/10 dark:text-cyan-400',
                orange: 'bg-orange-50 text-orange-700 dark:bg-orange-500/10 dark:text-orange-400',
            };
            return classes[color] || classes.gray;
        },

        iconPath(icon) {
            const paths = {
                calendar: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
                clock: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'receipt-refund': 'M8.25 9.75h4.875a2.625 2.625 0 1 1 0 5.25H12M8.25 9.75 10.5 7.5M8.25 9.75 10.5 12m9-7.243V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z',
                'clipboard-document-list': 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z',
                'shopping-cart': 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
                'chart-pie': 'M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z',
                banknotes: 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'document-text': 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
                'arrows-right-left': 'M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
                'currency-dollar': 'M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
                'document-check': 'M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375a9 9 0 0 1 9 9v.375M10.125 2.25A3.375 3.375 0 0 1 13.5 5.625v1.5c0 .621.504 1.125 1.125 1.125h1.5a3.375 3.375 0 0 1 3.375 3.375M9 15l2.25 2.25L15 12',
            };
            return paths[icon] || paths['document-check'];
        }
    };
}
</script>

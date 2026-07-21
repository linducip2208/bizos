<?php $__env->startSection('content'); ?>
<header class="border-b border-slate-200 bg-white/80 backdrop-blur-lg sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="<?php echo e(url('/')); ?>" class="flex items-center gap-2.5 font-bold text-slate-800 text-lg no-underline">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-7 h-7 text-indigo-600"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                <span>BizOS</span>
            </a>
            <a href="<?php echo e(url('/docs')); ?>" class="text-sm font-medium text-indigo-600 hover:text-indigo-700 no-underline px-4 py-2 rounded-lg hover:bg-indigo-50 transition-colors">
                Dokumentasi
            </a>
        </div>
    </div>
</header>

<main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="mb-10">
        <p class="text-indigo-600 font-semibold text-sm uppercase tracking-wider mb-2">BizOS Finance</p>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-slate-900 leading-tight mb-4">10 Fitur Akuntansi Terbaik BizOS</h1>
        <p class="text-lg text-slate-600 leading-relaxed">Software akuntansi bisnis Indonesia: double-entry PSAK, invoice PPN, PPh21-25, budget variance, manajemen aset, general ledger. Integrasi penuh dengan HRM, POS, CRM.</p>
    </div>

    <div class="space-y-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 bg-indigo-600 text-white rounded-xl flex items-center justify-center font-bold text-lg"><?php echo e($f['rank']); ?></div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-slate-800 mb-2"><?php echo e($f['title']); ?></h2>
                    <p class="text-slate-600 leading-relaxed"><?php echo e($f['desc']); ?></p>
                </div>
            </div>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>

    <div class="mt-12 p-8 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl border border-indigo-100">
        <h2 class="text-2xl font-extrabold text-slate-900 mb-3">Kesimpulan</h2>
        <p class="text-slate-600 leading-relaxed mb-6">
            BizOS memberikan <strong>solusi akuntansi komplet</strong> yang melampaui software akuntansi tradisional. Dengan double-entry accounting sesuai PSAK Indonesia, perpajakan lengkap (PPN, PPh21, PPh22, PPh23, PPh25, PPh Final), dan integrasi dengan modul HRM, POS, dan CRM — semua transaksi otomatis tercatat di jurnal tanpa input manual ganda.
        </p>
        <p class="text-slate-600 leading-relaxed mb-6">
            Fitur <strong>AR/AP Aging</strong> membantu Anda tracking piutang dan hutang tepat waktu. <strong>Budget variance analysis</strong> memberikan kontrol keuangan real-time. Sementara <strong>manajemen aset</strong> dengan penyusutan otomatis memastikan laporan keuangan selalu akurat.
        </p>
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="<?php echo e(url('/docs')); ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 transition-colors no-underline text-sm">
                Lihat Dokumentasi Lengkap
            </a>
            <a href="<?php echo e(url('/admin/login')); ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 border-2 border-indigo-200 text-indigo-700 font-semibold rounded-xl hover:bg-indigo-50 transition-colors no-underline text-sm">
                Coba Demo Gratis
            </a>
        </div>
    </div>

    <div class="mt-12 p-6 bg-amber-50 rounded-xl border border-amber-200">
        <h3 class="font-bold text-amber-800 mb-2 text-sm uppercase tracking-wider">Disclaimer</h3>
        <p class="text-sm text-amber-700 leading-relaxed">Artikel ini bertujuan informatif. Fitur dan harga dapat berubah. Konsultasikan dengan akuntan publik atau konsultan pajak untuk memastikan kepatuhan terhadap PSAK dan peraturan perpajakan terbaru.</p>
    </div>
</main>

<?php echo $__env->make('pseo._footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('pseo._layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\project laravel\bizos\resources\views/pseo/best-accounting.blade.php ENDPATH**/ ?>
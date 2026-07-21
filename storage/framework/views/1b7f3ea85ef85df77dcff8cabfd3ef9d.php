<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <div class="space-y-6">
        
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">Dashboard Analitik Pencarian</h2>
            <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-800 rounded-xl p-1">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['today' => 'Hari Ini', '7d' => '7 Hari', '30d' => '30 Hari', '90d' => '90 Hari']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button
                        wire:click="setPeriod('<?php echo e($key); ?>')"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all
                            <?php echo e($period === $key
                                ? 'bg-white dark:bg-gray-700 text-indigo-700 dark:text-indigo-300 shadow-sm'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'); ?>"
                    >
                        <?php echo e($label); ?>

                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>

        
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Total Pencarian</div>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-gray-100"><?php echo e(number_format($totalSearches, 0, ',', '.')); ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">User Unik</div>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-gray-100"><?php echo e(number_format($uniqueSearchers, 0, ',', '.')); ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Rata² Hasil</div>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-gray-100"><?php echo e(number_format($avgResults, 1, ',', '.')); ?></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Rata² Waktu</div>
                <div class="text-3xl font-extrabold text-gray-900 dark:text-gray-100"><?php echo e(number_format($avgTime, 0, ',', '.')); ?>ms</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-1">Click-Through</div>
                <div class="text-3xl font-extrabold <?php echo e($ctr > 50 ? 'text-emerald-600' : ($ctr > 25 ? 'text-amber-600' : 'text-red-500')); ?>"><?php echo e($ctr); ?>%</div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($dailyTrend)): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-4">Tren Pencarian Harian</h3>
                <div class="flex items-end gap-1 h-36">
                    <?php
                        $maxCount = max(array_column($dailyTrend, 'count') ?: [1]);
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $dailyTrend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php
                            $height = $maxCount > 0 ? round(($day['count'] / $maxCount) * 100) : 0;
                            $tooltip = ($day['date'] ?? '') . ': ' . $day['count'] . ' pencarian';
                        ?>
                        <div class="flex-1 flex flex-col items-center gap-1 group relative">
                            <span class="text-[9px] text-gray-400 dark:text-gray-500 opacity-0 group-hover:opacity-100 transition-opacity"><?php echo e($day['count']); ?></span>
                            <div
                                class="w-full bg-indigo-500/70 dark:bg-indigo-400/70 hover:bg-indigo-600 dark:hover:bg-indigo-400 rounded-t transition-all"
                                style="height: <?php echo e(max($height, 3)); ?>%"
                                title="<?php echo e($tooltip); ?>"
                            ></div>
                            <span class="text-[9px] text-gray-400 dark:text-gray-500 truncate max-w-full">
                                <?php echo e(\Carbon\Carbon::parse($day['date'] ?? '')->format('d/m')); ?>

                            </span>
                        </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        
        <div class="grid md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100 mb-3">Top Pencarian</h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($topQueries)): ?>
                    <p class="text-sm text-gray-400 text-center py-6">Belum ada data</p>
                <?php else: ?>
                    <div class="space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $topQueries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-bold text-gray-400 w-4"><?php echo e($i + 1); ?></span>
                                    <span class="text-sm text-gray-700 dark:text-gray-300"><?php echo e($q['query']); ?></span>
                                </div>
                                <div class="flex items-center gap-3 text-xs">
                                    <span class="text-gray-400"><?php echo e($q['count'] ?? 0); ?>x</span>
                                    <span class="text-gray-400">avg <?php echo e(number_format($q['avg_results'] ?? 0, 1, ',', '.')); ?></span>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-gray-100">Pencarian Tanpa Hasil</h3>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 font-semibold">Content Gap</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($zeroResultQueries)): ?>
                    <p class="text-sm text-emerald-500 text-center py-6">Tidak ada content gap — semua pencarian menghasilkan data!</p>
                <?php else: ?>
                    <div class="space-y-1">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $zeroResultQueries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $q): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div class="flex items-center justify-between py-2 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-bold text-gray-400 w-4"><?php echo e($i + 1); ?></span>
                                    <span class="text-sm text-red-600 dark:text-red-400"><?php echo e($q['query']); ?></span>
                                </div>
                                <span class="text-xs text-gray-400"><?php echo e($q['count'] ?? 0); ?>x</span>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="text-xs text-gray-400 dark:text-gray-500 text-center py-2">
            Data analitik dikumpulkan dari log pencarian pengguna.
        </div>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php /**PATH D:\project laravel\bizos\resources\views/filament/pages/search-analytics.blade.php ENDPATH**/ ?>
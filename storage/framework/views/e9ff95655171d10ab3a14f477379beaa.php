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
    <div class="px-0 py-0">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="fi-section rounded-xl p-6 bg-gradient-to-br from-emerald-500 to-teal-600 text-white">
                <div class="text-sm opacity-80">Skor ESG</div>
                <div class="text-5xl font-bold mt-2" x-text="score.total_score">--</div>
                <div class="text-lg mt-1">Grade <?php echo e($esgScore['grade'] ?? '--'); ?></div>
            </div>
            <div class="fi-section rounded-xl p-6">
                <div class="text-sm text-stone-500">Lingkungan</div>
                <div class="text-4xl font-bold text-emerald-600 mt-2"><?php echo e($esgScore['environmental_score'] ?? 0); ?></div>
                <div class="text-xs text-stone-400 mt-1">/ 100</div>
            </div>
            <div class="fi-section rounded-xl p-6">
                <div class="text-sm text-stone-500">Sosial</div>
                <div class="text-4xl font-bold text-blue-600 mt-2"><?php echo e($esgScore['social_score'] ?? 0); ?></div>
                <div class="text-xs text-stone-400 mt-1">/ 100</div>
            </div>
            <div class="fi-section rounded-xl p-6">
                <div class="text-sm text-stone-500">Tata Kelola</div>
                <div class="text-4xl font-bold text-amber-600 mt-2"><?php echo e($esgScore['governance_score'] ?? 0); ?></div>
                <div class="text-xs text-stone-400 mt-1">/ 100</div>
            </div>
        </div>

        
        <div class="fi-section rounded-xl p-6 mb-6">
            <h2 class="text-lg font-bold mb-4">Jejak Karbon <?php echo e(now()->translatedFormat('F Y')); ?></h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-stone-500 mb-1">Cakupan 1 (Emisi Langsung)</div>
                    <div class="text-3xl font-bold">
                        <span class="text-red-500"><?php echo e(round($carbonData['scope1_tco2e'] ?? 0, 2)); ?></span>
                        <span class="text-base font-normal text-stone-400"> tCO2e</span>
                    </div>
                    <div class="text-xs text-stone-400 mt-1">Kendaraan, bahan bakar, proses</div>
                </div>
                <div>
                    <div class="text-sm text-stone-500 mb-1">Cakupan 2 (Listrik)</div>
                    <div class="text-3xl font-bold">
                        <span class="text-amber-500"><?php echo e(round($carbonData['scope2_tco2e'] ?? 0, 2)); ?></span>
                        <span class="text-base font-normal text-stone-400"> tCO2e</span>
                    </div>
                    <div class="text-xs text-stone-400 mt-1">Konsumsi listrik × 0.85 kg/kWh</div>
                </div>
                <div>
                    <div class="text-sm text-stone-500 mb-1">Cakupan 3 (Rantai Nilai)</div>
                    <div class="text-3xl font-bold">
                        <span class="text-indigo-500"><?php echo e(round($carbonData['scope3_tco2e'] ?? 0, 2)); ?></span>
                        <span class="text-base font-normal text-stone-400"> tCO2e</span>
                    </div>
                    <div class="text-xs text-stone-400 mt-1">Supply chain, perjalanan, limbah</div>
                </div>
            </div>
            <div class="mt-6 pt-4 border-t border-stone-200 dark:border-stone-700">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-stone-500">Total Emisi</div>
                        <div class="text-2xl font-bold text-stone-800 dark:text-stone-200"><?php echo e(round($carbonData['total_tco2e'] ?? 0, 2)); ?> tCO2e</div>
                    </div>
                    <div>
                        <div class="text-sm text-stone-500">Trend vs Bulan Lalu</div>
                        <div class="text-2xl font-bold <?php if(($carbonData['trend_direction'] ?? 'stable') === 'down'): ?> text-emerald-600 <?php elseif(($carbonData['trend_direction'] ?? 'stable') === 'up'): ?> text-red-600 <?php else: ?> text-stone-600 <?php endif; ?>">
                            <?php echo e($carbonData['trend_vs_last_period_percent'] ?? 0); ?>%
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-stone-500">Intensitas per Karyawan</div>
                        <div class="text-2xl font-bold text-stone-800 dark:text-stone-200"><?php echo e(round($carbonData['intensity_per_employee']['tco2e_per_employee'] ?? 0, 3)); ?> tCO2e</div>
                    </div>
                    <div>
                        <div class="text-sm text-stone-500">Peer Percentile</div>
                        <div class="text-2xl font-bold text-stone-800 dark:text-stone-200">P<?php echo e($esgScore['peer_comparison_percentile'] ?? '--'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="fi-section rounded-xl p-6">
                <h2 class="text-lg font-bold mb-4">Limbah</h2>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-3xl font-bold text-stone-800 dark:text-stone-200"><?php echo e(round($wasteStats['total_waste_kg'] ?? 0, 1)); ?></div>
                        <div class="text-xs text-stone-400">Total (kg)</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-emerald-600"><?php echo e($wasteStats['recycled_percent'] ?? 0); ?>%</div>
                        <div class="text-xs text-stone-400">Daur Ulang</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-amber-600"><?php echo e($wasteStats['landfilled_percent'] ?? 0); ?>%</div>
                        <div class="text-xs text-stone-400">Ke TPA</div>
                    </div>
                </div>
            </div>
            <div class="fi-section rounded-xl p-6">
                <h2 class="text-lg font-bold mb-4">Air</h2>
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <div class="text-3xl font-bold text-stone-800 dark:text-stone-200"><?php echo e(round($waterStats['total_water_m3'] ?? 0, 1)); ?></div>
                        <div class="text-xs text-stone-400">Total (m3)</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-blue-600"><?php echo e($waterStats['recycled_percent'] ?? 0); ?>%</div>
                        <div class="text-xs text-stone-400">Daur Ulang</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold text-indigo-600"><?php echo e(round($waterStats['total_cost'] ?? 0, 0)); ?></div>
                        <div class="text-xs text-stone-400">Biaya (Rp)</div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="fi-section rounded-xl p-6 mb-6">
            <h2 class="text-lg font-bold mb-4">Progress Target ESG</h2>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($targetProgress)): ?>
            <div class="space-y-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $targetProgress; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $target): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-medium"><?php echo e($target['metric_label']); ?></span>
                        <span class="text-xs <?php if($target['on_track']): ?> text-emerald-600 <?php else: ?> text-red-600 <?php endif; ?>">
                            <?php echo e(round($target['progress_percent'], 1)); ?>%
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($target['on_track']): ?> (Sesuai Jalur) <?php else: ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </span>
                    </div>
                    <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all <?php if($target['on_track']): ?> bg-emerald-500 <?php else: ?> bg-red-500 <?php endif; ?>"
                             style="width: <?php echo e(min(100, $target['progress_percent'])); ?>%"></div>
                    </div>
                    <div class="flex justify-between text-xs text-stone-400 mt-1">
                        <span>Target: <?php echo e($target['target']); ?> <?php echo e($target['unit']); ?></span>
                        <span>Tenggat: <?php echo e($target['deadline']); ?></span>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
            <?php else: ?>
            <p class="text-stone-400 text-sm">Belum ada target ESG yang ditetapkan. <a href="<?php echo e(url('/admin/esg-targets/create')); ?>" class="text-primary-600">Tetapkan target</a>.</p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="fi-section rounded-xl p-6">
                <h2 class="text-lg font-bold mb-4">Diversitas & Inklusi</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-stone-500">Total Karyawan</span>
                        <span class="font-bold"><?php echo e($socialMetrics['total_employees'] ?? 0); ?></span>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm">
                            <span>Pria</span><span><?php echo e($socialMetrics['diversity']['gender']['male_percent'] ?? 0); ?>%</span>
                        </div>
                        <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2 mt-1">
                            <div class="h-2 rounded-full bg-blue-500" style="width: <?php echo e($socialMetrics['diversity']['gender']['male_percent'] ?? 50); ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-sm">
                            <span>Wanita</span><span><?php echo e($socialMetrics['diversity']['gender']['female_percent'] ?? 0); ?>%</span>
                        </div>
                        <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2 mt-1">
                            <div class="h-2 rounded-full bg-pink-500" style="width: <?php echo e($socialMetrics['diversity']['gender']['female_percent'] ?? 0); ?>%"></div>
                        </div>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-stone-500">Turnover (Annualized)</span>
                        <span class="font-bold"><?php echo e(round($socialMetrics['turnover']['annualized_rate_percent'] ?? 0, 1)); ?>%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-stone-500">Gap Gaji Gender</span>
                        <span class="font-bold"><?php echo e(round($socialMetrics['compensation']['gender_pay_gap_percent'] ?? 0, 1)); ?>%</span>
                    </div>
                </div>
            </div>
            <div class="fi-section rounded-xl p-6">
                <h2 class="text-lg font-bold mb-4">Tata Kelola</h2>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-sm text-stone-500">Pelanggaran Data (YTD)</span>
                        <span class="font-bold <?php if(($governanceMetrics['data_privacy']['breaches_ytd'] ?? 0) > 0): ?> text-red-600 <?php else: ?> text-emerald-600 <?php endif; ?>">
                            <?php echo e($governanceMetrics['data_privacy']['breaches_ytd'] ?? 0); ?>

                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-stone-500">DPIA Selesai</span>
                        <span class="font-bold"><?php echo e($governanceMetrics['data_privacy']['dpia_completed'] ?? 0); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-sm text-stone-500">Risiko Teridentifikasi</span>
                        <span class="font-bold"><?php echo e($governanceMetrics['risk_management']['risks_identified'] ?? 0); ?></span>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($reductionSuggestions)): ?>
        <div class="fi-section rounded-xl p-6">
            <h2 class="text-lg font-bold mb-4">Rekomendasi Reduksi Emisi</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $reductionSuggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                <div class="border border-stone-200 dark:border-stone-700 rounded-xl p-4">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="px-2 py-0.5 text-xs rounded-full
                            <?php if($s['difficulty'] === 'low'): ?> bg-emerald-100 text-emerald-700
                            <?php elseif($s['difficulty'] === 'medium'): ?> bg-amber-100 text-amber-700
                            <?php else: ?> bg-red-100 text-red-700
                            <?php endif; ?>">
                            <?php echo e(match($s['difficulty'] ?? 'medium') { 'low' => 'Mudah', 'medium' => 'Sedang', 'high' => 'Sulit', default => 'Sedang' }); ?>

                        </span>
                        <span class="text-xs text-stone-400"><?php echo e($s['category'] ?? ''); ?></span>
                    </div>
                    <h3 class="font-semibold text-sm mb-1"><?php echo e($s['title']); ?></h3>
                    <p class="text-xs text-stone-500 mb-2"><?php echo e($s['description']); ?></p>
                    <div class="text-xs">
                        <span class="text-emerald-600 font-bold">↓ <?php echo e($s['reduction_percent']); ?>% potensi reduksi</span>
                        <span class="text-stone-400 ml-2">ROI: <?php echo e($s['roi_estimate']); ?></span>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php /**PATH D:\project laravel\bizos\resources\views/filament/pages/esg-dashboard.blade.php ENDPATH**/ ?>
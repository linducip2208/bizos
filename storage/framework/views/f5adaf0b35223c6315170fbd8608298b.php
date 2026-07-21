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

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

    <div class="space-y-6">
        
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <form method="get" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Dari</label>
                    <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Sampai</label>
                    <input type="date" name="date_to" value="<?php echo e($dateTo); ?>"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm">
                </div>
                <div>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
                <div>
                    <button type="button"
                        onclick="window.print()"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Export PDF
                    </button>
                </div>
            </form>
        </div>

        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="fi-section rounded-xl bg-emerald-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-emerald-100">Total Pendapatan</p>
                <p class="mt-2 text-2xl font-extrabold">Rp <?php echo e(number_format($cards['total_pendapatan'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="fi-section rounded-xl bg-rose-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-rose-100">Total Beban</p>
                <p class="mt-2 text-2xl font-extrabold">Rp <?php echo e(number_format($cards['total_beban'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="fi-section rounded-xl p-5 text-white shadow-sm <?php echo e(($cards['laba_rugi'] ?? 0) >= 0 ? 'bg-indigo-600' : 'bg-red-600'); ?>">
                <p class="text-sm font-medium text-indigo-100 dark:text-red-100"><?php echo e(($cards['laba_rugi'] ?? 0) >= 0 ? 'Laba' : 'Rugi'); ?></p>
                <p class="mt-2 text-2xl font-extrabold">Rp <?php echo e(number_format(abs($cards['laba_rugi'] ?? 0), 0, ',', '.')); ?></p>
            </div>
            <div class="fi-section rounded-xl p-5 text-white shadow-sm <?php echo e(($cards['margin'] ?? 0) >= 0 ? 'bg-sky-600' : 'bg-red-600'); ?>">
                <p class="text-sm font-medium text-sky-100 dark:text-red-100">Margin</p>
                <p class="mt-2 text-2xl font-extrabold"><?php echo e(number_format($cards['margin'] ?? 0, 1)); ?>%</p>
            </div>
        </div>

        
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10 lg:col-span-2">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Revenue vs Expense</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="revenueExpenseChart"></canvas>
                </div>
            </div>
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Beban per Kategori</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="expenseCategoryChart"></canvas>
                </div>
            </div>
        </div>

        
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Ringkasan Laba Rugi</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $pnlSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($row['type'] === 'header'): ?>
                            <tr>
                                <td colspan="2" class="pt-4 text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400"><?php echo e($row['label']); ?></td>
                            </tr>
                        <?php elseif($row['type'] === 'subtotal'): ?>
                            <tr class="border-t border-gray-300 dark:border-gray-600">
                                <td class="pl-6 pt-2 text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($row['label']); ?></td>
                                <td class="pt-2 text-right text-sm font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($row['amount'], 0, ',', '.')); ?></td>
                            </tr>
                        <?php elseif($row['type'] === 'total'): ?>
                            <tr class="border-t-2 border-gray-400 dark:border-gray-500 bg-gray-50 dark:bg-gray-700/30">
                                <td class="py-2 pl-6 text-sm font-extrabold <?php echo e($row['amount'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'); ?>"><?php echo e($row['label']); ?></td>
                                <td class="py-2 text-right text-sm font-extrabold <?php echo e($row['amount'] >= 0 ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400'); ?>">Rp <?php echo e(number_format($row['amount'], 0, ',', '.')); ?></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td class="py-1 pl-6 text-sm text-gray-600 dark:text-gray-400"><?php echo e($row['label']); ?></td>
                                <td class="py-1 text-right text-sm text-gray-700 dark:text-gray-300">Rp <?php echo e(number_format($row['amount'], 0, ',', '.')); ?></td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            var ctx1 = document.getElementById('revenueExpenseChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($revenueLabels, 15, 512) ?>,
                    datasets: [
                        {
                            label: 'Pendapatan',
                            data: <?php echo json_encode($revenueData, 15, 512) ?>,
                            backgroundColor: 'rgba(16, 185, 129, 0.8)',
                            borderColor: 'rgba(16, 185, 129, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        },
                        {
                            label: 'Beban',
                            data: <?php echo json_encode($expenseData, 15, 512) ?>,
                            backgroundColor: 'rgba(239, 68, 68, 0.8)',
                            borderColor: 'rgba(239, 68, 68, 1)',
                            borderWidth: 1,
                            borderRadius: 6,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            labels: { color: textColor, usePointStyle: true, pointStyleWidth: 12 }
                        }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor } },
                        y: {
                            grid: { color: gridColor },
                            ticks: {
                                color: textColor,
                                callback: function(v) { return 'Rp ' + v.toLocaleString('id-ID'); }
                            }
                        }
                    }
                }
            });

            var ctx2 = document.getElementById('expenseCategoryChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($expenseCategoryLabels, 15, 512) ?>,
                    datasets: [{
                        data: <?php echo json_encode($expenseCategoryData, 15, 512) ?>,
                        backgroundColor: [
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(6, 182, 212, 0.8)',
                            'rgba(236, 72, 153, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(34, 197, 94, 0.8)',
                        ],
                        borderWidth: 2,
                        borderColor: isDark ? '#1f2937' : '#ffffff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { color: textColor, padding: 16, usePointStyle: true }
                        }
                    }
                }
            });
        });
    </script>
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
<?php /**PATH D:\project laravel\bizos\resources\views/filament/pages/laporan-keuangan.blade.php ENDPATH**/ ?>
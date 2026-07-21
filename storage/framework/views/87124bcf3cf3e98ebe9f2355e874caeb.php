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
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Group By</label>
                    <select name="group_by"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white sm:text-sm"
                        onchange="this.form.submit()">
                        <option value="harian" <?php echo e($groupBy === 'harian' ? 'selected' : ''); ?>>Harian</option>
                        <option value="mingguan" <?php echo e($groupBy === 'mingguan' ? 'selected' : ''); ?>>Mingguan</option>
                        <option value="bulanan" <?php echo e($groupBy === 'bulanan' ? 'selected' : ''); ?>>Bulanan</option>
                    </select>
                </div>
                <div>
                    <button type="submit"
                        class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="fi-section rounded-xl bg-indigo-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-indigo-100">Total Revenue</p>
                <p class="mt-2 text-2xl font-extrabold">Rp <?php echo e(number_format($summaryCards['total_revenue'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="fi-section rounded-xl bg-emerald-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-emerald-100">Total Transaksi</p>
                <p class="mt-2 text-2xl font-extrabold"><?php echo e(number_format($summaryCards['total_transaksi'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="fi-section rounded-xl bg-amber-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-amber-100">Avg per Transaksi</p>
                <p class="mt-2 text-2xl font-extrabold">Rp <?php echo e(number_format($summaryCards['avg_per_transaksi'] ?? 0, 0, ',', '.')); ?></p>
            </div>
            <div class="fi-section rounded-xl bg-purple-600 p-5 text-white shadow-sm">
                <p class="text-sm font-medium text-purple-100">Total Pelanggan</p>
                <p class="mt-2 text-2xl font-extrabold"><?php echo e(number_format($summaryCards['total_pelanggan'] ?? 0, 0, ',', '.')); ?></p>
            </div>
        </div>

        
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10 lg:col-span-2">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Revenue per <?php echo e(ucfirst($groupBy)); ?></h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
                <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Revenue by Payment Method</h3>
                <div class="relative" style="height: 350px;">
                    <canvas id="paymentMethodChart"></canvas>
                </div>
            </div>
        </div>

        
        <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-800 dark:ring-white/10">
            <h3 class="mb-4 text-base font-semibold text-gray-900 dark:text-white">Detail Transaksi</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr class="bg-gray-50 dark:bg-gray-700/50">
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Tipe</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Referensi</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $detailTable; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
                                <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-700 dark:text-gray-300"><?php echo e($row['date'] ?? '-'); ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm">
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                        'inline-flex rounded-md px-2 py-0.5 text-xs font-semibold',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300' => ($row['source_type'] ?? '') === 'Invoice',
                                        'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300' => ($row['source_type'] ?? '') === 'POS',
                                    ]); ?>">
                                        <?php echo e($row['source_type'] ?? '-'); ?>

                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-gray-900 dark:text-white"><?php echo e($row['reference'] ?? '-'); ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-700 dark:text-gray-300">Rp <?php echo e(number_format($row['total'] ?? 0, 0, ',', '.')); ?></td>
                                <td class="whitespace-nowrap px-4 py-3 text-center text-sm">
                                    <span class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700 dark:bg-green-900 dark:text-green-300">Paid</span>
                                </td>
                            </tr>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data transaksi</td>
                            </tr>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            const textColor = isDark ? '#9ca3af' : '#6b7280';

            var ctx1 = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chartLabels, 15, 512) ?>,
                    datasets: [{
                        label: 'Revenue',
                        data: <?php echo json_encode($chartData, 15, 512) ?>,
                        backgroundColor: 'rgba(99, 102, 241, 0.85)',
                        borderColor: 'rgba(99, 102, 241, 1)',
                        borderWidth: 1,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
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

            var ctx2 = document.getElementById('paymentMethodChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($paymentMethodLabels, 15, 512) ?>,
                    datasets: [{
                        data: <?php echo json_encode($paymentMethodData, 15, 512) ?>,
                        backgroundColor: [
                            'rgba(99, 102, 241, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(245, 158, 11, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(139, 92, 246, 0.8)',
                            'rgba(6, 182, 212, 0.8)',
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
<?php /**PATH D:\project laravel\bizos\resources\views/filament/pages/laporan-bisnis.blade.php ENDPATH**/ ?>
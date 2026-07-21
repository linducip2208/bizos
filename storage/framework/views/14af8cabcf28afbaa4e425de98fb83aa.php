<?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->check() && auth()->user()?->company?->is_sandbox): ?>
    <div class="px-3 py-1 bg-amber-500 text-white text-xs font-bold rounded-full animate-pulse">
        SANDBOX
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH D:\project laravel\bizos\resources\views/filament/hooks/sandbox-badge.blade.php ENDPATH**/ ?>
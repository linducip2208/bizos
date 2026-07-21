<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php $seoMeta = $seo ?? $seoMeta ?? []; ?>
    <title><?php echo e($seoMeta['title'] ?? 'BizOS — Business Operating System'); ?></title>
    <meta name="description" content="<?php echo e($seoMeta['description'] ?? ''); ?>">
    <link rel="canonical" href="<?php echo e($seoMeta['canonical'] ?? url()->current()); ?>">
    <meta property="og:type" content="website">
    <meta property="og:title" content="<?php echo e($seoMeta['og_title'] ?? $seoMeta['title'] ?? ''); ?>">
    <meta property="og:description" content="<?php echo e($seoMeta['og_description'] ?? $seoMeta['description'] ?? ''); ?>">
    <meta property="og:url" content="<?php echo e($seoMeta['og_url'] ?? url()->current()); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($seoMeta['og_image'])): ?>
    <meta property="og:image" content="<?php echo e($seoMeta['og_image']); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <meta name="twitter:card" content="<?php echo e($seoMeta['twitter_card'] ?? 'summary_large_image'); ?>">
    <meta name="twitter:title" content="<?php echo e($seoMeta['og_title'] ?? $seoMeta['title'] ?? ''); ?>">
    <meta name="twitter:description" content="<?php echo e($seoMeta['og_description'] ?? $seoMeta['description'] ?? ''); ?>">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($seoMeta['og_image'])): ?>
    <meta name="twitter:image" content="<?php echo e($seoMeta['og_image']); ?>">
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!empty($seoMeta['jsonld'])): ?>
    <script type="application/ld+json"><?php echo json_encode($seoMeta['jsonld'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?></script>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700" rel="stylesheet">
    <style>
        * { font-family: 'Inter', system-ui, sans-serif; }
        pre, code, .font-mono { font-family: 'JetBrains Mono', monospace; }
        .browser-mock {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15), 0 1px 3px rgba(0,0,0,0.08);
            background: #1e1e2e;
        }
        .browser-mock-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            background: #1e1e2e;
        }
        .browser-mock-dot { width: 12px; height: 12px; border-radius: 50%; }
        .browser-mock-url {
            flex: 1;
            background: #2d2d3f;
            border-radius: 6px;
            padding: 5px 12px;
            font-size: 12px;
            color: #94a3b8;
            font-family: 'JetBrains Mono', monospace;
        }
        .browser-mock-body { background: #ffffff; }
        .browser-mock-body img { display: block; width: 100%; }
        html { scroll-behavior: smooth; }
    </style>
    <?php echo $__env->yieldPushContent('styles'); ?>
</head>
<body class="bg-slate-50 text-slate-800 antialiased">
    <?php echo $__env->yieldContent('content'); ?>
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH D:\project laravel\bizos\resources\views/pseo/_layout.blade.php ENDPATH**/ ?>
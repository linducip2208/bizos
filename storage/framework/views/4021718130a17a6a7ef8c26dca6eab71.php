
<script>
(function() {
    const emojiMap = {
        'Master Data':            '\uD83C\uDFE2',
        'HRM':                    '\uD83D\uDC65',
        'Payroll':                '\uD83D\uDCB0',
        'Alat Hitung':            '\uD83E\uDDEE',
        'Finance':                '\uD83D\uDCCA',
        'Procurement & Inventory': '\uD83D\uDCED',
        'CRM':                    '\uD83D\uDCC8',
        'Marketing':              '\uD83D\uDCE2',
        'Project':                '\uD83D\uDCCB',
        'Kolaborasi':             '\uD83D\uDCAC',
        'Helpdesk':               '\uD83C\uDFAB',
        'POS':                    '\uD83D\uDED2',
        'LMS':                    '\uD83C\uDF93',
        'AI Assistant':           '\uD83E\uDD16',
        'AI Analytics':           '\uD83E\uDDE0',
        'Laporan':                '\uD83D\uDCCA',
        'Integrasi':              '\uD83D\uDD0C',
        'Hub Integrasi':          '\uD83D\uDCE1',
        'Sistem':                 '\u2699\uFE0F',
        'Core':                   '\uD83C\uDFD7\uFE0F',
        'Manufaktur':             '\uD83C\uDFED',
        'Kesehatan':              '\uD83C\uDFE5',
        'Konstruksi':             '\uD83D\uDEE0\uFE0F',
        'Perhotelan':             '\uD83C\uDFE8',
        'Properti':               '\uD83C\uDFE0',
        'Logistik':               '\uD83D\uDE9A',
        'E-Commerce':             '\uD83D\uDECD\uFE0F',
        'Field Service':          '\uD83D\uDD27',
        'ESG':                    '\uD83C\uDF0D',
        'Kepatuhan':              '\uD83D\uDEE1\uFE0F',
        'Gamifikasi':             '\uD83C\uDFAE',
        'IoT & Sensor':           '\uD83D\uDCE1',
        'Treasury':               '\uD83C\uDFE6',
        'BPMN':                   '\uD83D\uDDC2\uFE0F',
        'Blockchain':             '\uD83D\uDD17',
        'Platform':               '\uD83C\uDFD7\uFE0F',
    };

    function injectEmojis() {
        document.querySelectorAll('.fi-sidebar-group button').forEach(function(btn) {
            if (btn.dataset.emojiInjected) return;
            var span = btn.querySelector('span');
            if (!span) return;
            var text = span.textContent.trim();
            var emoji = emojiMap[text];
            if (emoji && !text.startsWith(emoji)) {
                span.textContent = emoji + ' ' + text;
                btn.dataset.emojiInjected = '1';
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        injectEmojis();
        var observer = new MutationObserver(injectEmojis);
        var sidebar = document.querySelector('.fi-sidebar');
        if (sidebar) observer.observe(sidebar, { childList: true, subtree: true });
    });
})();
</script>
<?php /**PATH D:\project laravel\bizos\resources\views/filament/hooks/body-end.blade.php ENDPATH**/ ?>
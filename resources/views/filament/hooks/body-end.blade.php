{{-- Command Palette (persistent) --}}
<livewire:command-palette />

{{-- Sidebar Group Emoji Injection --}}
<script>
(function() {
    const emojiMap = {
        'Master Data': '📋',
        'HRM': '👥',
        'Payroll': '💵',
        'Finance': '💰',
        'Procurement & Inventory': '📦',
        'Billing': '🧾',
        'CRM': '🏢',
        'Marketing': '📣',
        'Project': '📐',
        'Kolaborasi': '💬',
        'Helpdesk': '🆘',
        'POS': '🛒',
        'LMS': '🎓',
        'AI Assistant': '🤖',
        'Laporan': '📊',
        'Integrasi': '🔌',
        'Core': '⚙️',
        'Sistem': '🔧',
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

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            injectEmojis();
            var observer = new MutationObserver(function(mutations) {
                injectEmojis();
            });
            var sidebar = document.querySelector('.fi-sidebar');
            if (sidebar) {
                observer.observe(sidebar, { childList: true, subtree: true });
            }
        });
    } else {
        injectEmojis();
        var observer = new MutationObserver(function(mutations) {
            injectEmojis();
        });
        var sidebar = document.querySelector('.fi-sidebar');
        if (sidebar) {
            observer.observe(sidebar, { childList: true, subtree: true });
        }
    }
})();
</script>

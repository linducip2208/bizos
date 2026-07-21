document.addEventListener('DOMContentLoaded', function () {
    const writePanel = document.getElementById('ai-write-panel-container');

    if (!writePanel || writePanel.dataset.initialized) return;
    writePanel.dataset.initialized = 'true';

    function findActiveTextarea() {
        const focused = document.activeElement;
        if (focused && focused.tagName === 'TEXTAREA') {
            return focused;
        }
        const visible = document.querySelector('textarea:focus-within, textarea:focus');
        if (visible) return visible;
        return null;
    }

    function getContextInfo(textarea) {
        if (!textarea) return '';
        const form = textarea.closest('form');
        if (!form) return '';
        const labels = [];
        const inputs = form.querySelectorAll('input[type="text"]:not([readonly]), select');
        inputs.forEach(function (input) {
            const label = form.querySelector('label[for="' + input.id + '"]');
            if (label && input.value) {
                labels.push(label.textContent.trim() + ': ' + input.value);
            }
        });
        return labels.slice(0, 5).join(' | ');
    }

    document.addEventListener('click', function (e) {
        const aiButton = e.target.closest('[data-ai-write]');
        if (!aiButton) return;

        e.preventDefault();
        const targetTextarea = aiButton.dataset.target
            ? document.getElementById(aiButton.dataset.target)
            : findActiveTextarea();

        const textareaId = targetTextarea ? targetTextarea.id || targetTextarea.name || '' : '';
        const context = getContextInfo(targetTextarea);

        try {
            Livewire.dispatch('openAiWritePanel', {
                textareaId: textareaId,
                context: context,
            });
        } catch (err) {
            console.error('AI Write: Failed to dispatch', err);
        }
    });

    Livewire.on('insertAiText', function (event) {
        const detail = Array.isArray(event) ? event[0] : event;
        const textareaId = detail.textareaId;
        const text = detail.text;

        let target = null;
        if (textareaId) {
            target = document.getElementById(textareaId)
                || document.querySelector('[name="' + textareaId + '"]')
                || document.querySelector('#' + CSS.escape(textareaId));
        }

        if (!target) {
            target = document.activeElement;
            if (!target || target.tagName !== 'TEXTAREA') {
                target = document.querySelector('textarea:focus, input[type="text"]:focus');
            }
        }

        if (target && (target.tagName === 'TEXTAREA' || target.tagName === 'INPUT')) {
            const start = target.selectionStart;
            const end = target.selectionEnd;
            const before = target.value.substring(0, start);
            const after = target.value.substring(end);
            target.value = before + text + after;
            target.focus();
            target.selectionStart = target.selectionEnd = start + text.length;
            target.dispatchEvent(new Event('input', { bubbles: true }));
        }
    });

    document.querySelectorAll('textarea').forEach(function (textarea) {
        if (textarea.closest('.ai-write-injected')) return;

        const wrapper = document.createElement('div');
        wrapper.className = 'relative ai-write-injected';
        textarea.parentNode.insertBefore(wrapper, textarea);
        wrapper.appendChild(textarea);

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'absolute bottom-2 right-2 inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-900/40 hover:bg-indigo-100 dark:hover:bg-indigo-900/60 rounded-lg transition-all border border-indigo-200 dark:border-indigo-800 z-10';
        btn.innerHTML = '<span>&#10024;</span> AI';
        btn.dataset.aiWrite = '';
        btn.dataset.target = textarea.id || textarea.name || '';
        btn.title = 'Buka AI Tulis';

        wrapper.appendChild(btn);
    });
});

(function () {
    'use strict';

    function markCopied(button) {
        var original = button.textContent;
        button.textContent = button.getAttribute('data-copied-label') || 'Kopiert';
        window.setTimeout(function () { button.textContent = original; }, 1600);
    }

    function fallbackCopy(input, button) {
        input.focus();
        input.select();
        input.setSelectionRange(0, input.value.length);
        if (document.execCommand('copy')) markCopied(button);
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-afdsp-copy-shortcode]');
        if (!button) return;

        var input = document.getElementById(button.getAttribute('data-afdsp-copy-shortcode'));
        if (!input) return;

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(function () {
                markCopied(button);
            }).catch(function () {
                fallbackCopy(input, button);
            });
            return;
        }

        fallbackCopy(input, button);
    });
}());

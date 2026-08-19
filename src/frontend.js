(function () {
    'use strict';

    document.querySelectorAll('[data-afdsp-tabs]').forEach(function (root) {
        var tabs = Array.prototype.slice.call(root.querySelectorAll('[data-afdsp-tab]'));
        var panels = Array.prototype.slice.call(root.querySelectorAll('[data-afdsp-panel]'));

        function activate(tab) {
            tabs.forEach(function (item) {
                var active = item === tab;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', active ? 'true' : 'false');
                item.tabIndex = active ? 0 : -1;
            });
            panels.forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-afdsp-panel') !== tab.getAttribute('data-afdsp-tab');
            });
            tab.focus();
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { activate(tab); });
            tab.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' && event.key !== 'Home' && event.key !== 'End') return;
                event.preventDefault();
                var next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                activate(tabs[next]);
            });
        });
    });
}());

(function () {
    'use strict';

    function builderData(root) {
        var script = root.querySelector('[data-afdsp-data]');
        if (!script) return null;
        try { return JSON.parse(script.textContent || '{}'); } catch (error) { return null; }
    }

    function updateBuilder(root, fuel) {
        var allData = builderData(root);
        var data = allData && allData[fuel];
        if (!data) return;
        root.setAttribute('data-afdsp-fuel', fuel);
        root.querySelectorAll('[data-afdsp-bind]').forEach(function (element) {
            var key = element.getAttribute('data-afdsp-bind');
            if (Object.prototype.hasOwnProperty.call(data, key)) element.textContent = data[key];
        });
    }

    function mount(root) {
        var tabs = Array.prototype.slice.call(root.querySelectorAll('[data-afdsp-tab]'));
        var panels = Array.prototype.slice.call(root.querySelectorAll('[data-afdsp-panel]'));
        if (!tabs.length) return;

        function activate(tab, focus) {
            tabs.forEach(function (item) {
                var active = item === tab;
                item.classList.toggle('is-active', active);
                if (item.hasAttribute('aria-selected')) item.setAttribute('aria-selected', active ? 'true' : 'false');
                if (item.hasAttribute('aria-pressed')) item.setAttribute('aria-pressed', active ? 'true' : 'false');
                item.tabIndex = active ? 0 : -1;
            });
            panels.forEach(function (panel) {
                panel.hidden = panel.getAttribute('data-afdsp-panel') !== tab.getAttribute('data-afdsp-tab');
            });
            if (root.hasAttribute('data-afdsp-builder')) updateBuilder(root, tab.getAttribute('data-afdsp-tab'));
            if (focus) tab.focus();
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { activate(tab, true); });
            tab.addEventListener('keydown', function (event) {
                if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight' && event.key !== 'Home' && event.key !== 'End') return;
                event.preventDefault();
                var next = event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : (index + (event.key === 'ArrowRight' ? 1 : -1) + tabs.length) % tabs.length;
                activate(tabs[next], true);
            });
        });
    }

    document.querySelectorAll('[data-afdsp-tabs], [data-afdsp-builder]').forEach(mount);
}());

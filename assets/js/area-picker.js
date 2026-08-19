(function () {
    'use strict';

    var config = window.afdspAreaPickerConfig || {};
    var cache = new Map();
    var cacheLifetime = 5 * 60 * 1000;

    function search(query, signal) {
        var normalized = String(query || '').trim();
        if (normalized.length < 3) {
            return Promise.resolve([]);
        }
        var hit = cache.get(normalized.toLowerCase());
        if (hit && Date.now() - hit.time < cacheLifetime) {
            return Promise.resolve(hit.data);
        }
        var separator = String(config.endpoint || '').indexOf('?') === -1 ? '?' : '&';
        return window.fetch(config.endpoint + separator + 'q=' + encodeURIComponent(normalized), {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-WP-Nonce': config.nonce || '' },
            signal: signal
        }).then(function (response) {
            if (!response.ok) {
                throw new Error('Photon request failed');
            }
            return response.json();
        }).then(function (data) {
            var results = Array.isArray(data) ? data.slice(0, 5) : [];
            cache.set(normalized.toLowerCase(), { time: Date.now(), data: results });
            return results;
        });
    }

    function setField(root, name, value) {
        var field = root.querySelector('[data-afdsp-field="' + name + '"]');
        if (!field) return;
        field.value = value;
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function mount(root) {
        var input = root.querySelector('[data-afdsp-search]');
        var list = root.querySelector('[data-afdsp-results]');
        if (!input || !list) return;
        var timer = 0;
        var controller = null;

        function clear() {
            list.replaceChildren();
            list.hidden = true;
        }

        input.addEventListener('input', function () {
            window.clearTimeout(timer);
            if (controller) controller.abort();
            if (input.value.trim().length < 3) {
                clear();
                return;
            }
            timer = window.setTimeout(function () {
                controller = new AbortController();
                search(input.value, controller.signal).then(function (results) {
                    clear();
                    list.hidden = false;
                    if (!results.length) {
                        var empty = document.createElement('p');
                        empty.textContent = (config.strings && config.strings.noResults) || 'Keine Orte gefunden.';
                        list.appendChild(empty);
                        return;
                    }
                    results.forEach(function (result) {
                        var button = document.createElement('button');
                        button.type = 'button';
                        button.setAttribute('role', 'option');
                        button.textContent = result.label;
                        button.addEventListener('click', function () {
                            input.value = result.label;
                            setField(root, 'areaLabel', result.label);
                            Object.keys(result.bbox || {}).forEach(function (key) { setField(root, key, result.bbox[key]); });
                            clear();
                        });
                        list.appendChild(button);
                    });
                }).catch(function (error) {
                    if (error.name === 'AbortError') return;
                    clear();
                    list.hidden = false;
                    var message = document.createElement('p');
                    message.textContent = (config.strings && config.strings.error) || 'Ortssuche derzeit nicht verfügbar.';
                    list.appendChild(message);
                });
            }, 350);
        });
        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) clear();
        });
        clear();
    }

    window.AFDSPAreaPicker = { search: search, mount: mount };
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-afdsp-area-picker]').forEach(mount);
    });
}());

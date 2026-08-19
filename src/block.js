(function (blocks, blockEditor, components, element, i18n, serverSideRender) {
    'use strict';

    var el = element.createElement;
    var Fragment = element.Fragment;
    var useEffect = element.useEffect;
    var useState = element.useState;
    var InspectorControls = blockEditor.InspectorControls;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var ToggleControl = components.ToggleControl;
    var Notice = components.Notice;
    var __ = i18n.__;
    var ServerSideRender = serverSideRender && (serverSideRender.default || serverSideRender);
    var config = window.afdspAreaPickerConfig || { area: {}, display: {} };

    function edit(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var searchState = useState('');
        var query = searchState[0];
        var setQuery = searchState[1];
        var resultsState = useState([]);
        var results = resultsState[0];
        var setResults = resultsState[1];
        var errorState = useState(false);
        var searchError = errorState[0];
        var setSearchError = errorState[1];

        useEffect(function () {
            if (typeof attributes.minLat !== 'undefined') return;
            setAttributes(Object.assign({}, config.area || {}, config.display || {}));
        }, []);

        useEffect(function () {
            if (query.trim().length < 3) {
                setResults([]);
                setSearchError(false);
                return undefined;
            }
            var controller = new AbortController();
            var timer = window.setTimeout(function () {
                window.AFDSPAreaPicker.search(query, controller.signal).then(function (items) {
                    setResults(items);
                    setSearchError(false);
                }).catch(function (error) {
                    if (error.name !== 'AbortError') setSearchError(true);
                });
            }, 350);
            return function () {
                window.clearTimeout(timer);
                controller.abort();
            };
        }, [query]);

        function choose(result) {
            setAttributes(Object.assign({ areaLabel: result.label }, result.bbox));
            setQuery(result.label);
            setResults([]);
        }

        var resultButtons = results.map(function (result, index) {
            return el('button', { type: 'button', className: 'afdsp-editor-result', key: result.label + index, onClick: function () { choose(result); } }, result.label);
        });
        var bbox = ['minLat', 'minLng', 'maxLat', 'maxLng'].map(function (key) {
            return key + ': ' + (typeof attributes[key] === 'number' ? attributes[key].toFixed(6) : '–');
        }).join(' · ');

        var inspector = el(InspectorControls, {},
            el(PanelBody, { title: __('Gebiet', 'afd-spritpreise'), initialOpen: true },
                el(TextControl, { label: __('Ort oder PLZ suchen', 'afd-spritpreise'), value: query, onChange: setQuery, help: __('Photon setzt die Bounding Box des gewählten Ergebnisses.', 'afd-spritpreise') }),
                searchError ? el(Notice, { status: 'error', isDismissible: false }, __('Ortssuche derzeit nicht verfügbar.', 'afd-spritpreise')) : null,
                resultButtons.length ? el('div', { className: 'afdsp-editor-results' }, resultButtons) : null,
                el(TextControl, { label: __('Gebietsbezeichnung', 'afd-spritpreise'), value: attributes.areaLabel || '', onChange: function (value) { setAttributes({ areaLabel: value }); } }),
                el('p', { className: 'afdsp-editor-bbox' }, bbox)
            ),
            el(PanelBody, { title: __('Darstellung', 'afd-spritpreise'), initialOpen: true },
                el(SelectControl, { label: __('Standard-Kraftstoff', 'afd-spritpreise'), value: attributes.defaultFuel || 'diesel', options: [{ label: 'Diesel', value: 'diesel' }, { label: 'Super E5', value: 'e5' }, { label: 'Super E10', value: 'e10' }], onChange: function (value) { setAttributes({ defaultFuel: value }); } }),
                el(SelectControl, { label: __('Modus', 'afd-spritpreise'), value: attributes.displayMode || 'full', options: [{ label: __('Vollständig', 'afd-spritpreise'), value: 'full' }, { label: __('Kompakt', 'afd-spritpreise'), value: 'compact' }], onChange: function (value) { setAttributes({ displayMode: value }); } }),
                toggle('showTitle', __('Titel anzeigen', 'afd-spritpreise')),
                toggle('showDemands', __('Forderungen anzeigen', 'afd-spritpreise')),
                toggle('showMethod', __('Methodik anzeigen', 'afd-spritpreise')),
                toggle('showCheapestStation', __('Günstigste Tankstelle', 'afd-spritpreise')),
                toggle('showTankSaving', __('50-Liter-Ersparnis', 'afd-spritpreise')),
                toggle('showDetailsLink', __('Detail-/Quellenlink', 'afd-spritpreise'))
            )
        );

        function toggle(key, label) {
            return el(ToggleControl, { label: label, checked: attributes[key] !== false, onChange: function (value) { var update = {}; update[key] = value; setAttributes(update); } });
        }

        return el(Fragment, {}, inspector,
            ServerSideRender ? el(ServerSideRender, { block: 'afd-spritpreise/fuel-price', attributes: attributes, skipBlockSupportAttributes: true }) : el('div', { className: 'afdsp-editor-placeholder' }, __('AfD Spritpreise – serverseitige Vorschau', 'afd-spritpreise'))
        );
    }

    blocks.registerBlockType('afd-spritpreise/fuel-price', { edit: edit, save: function () { return null; } });
}(window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n, window.wp.serverSideRender));

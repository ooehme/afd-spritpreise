(function (blocks, blockEditor, components, element, i18n) {
    'use strict';

    var el = element.createElement;
    var Fragment = element.Fragment;
    var useEffect = element.useEffect;
    var useState = element.useState;
    var InspectorControls = blockEditor.InspectorControls;
    var InnerBlocks = blockEditor.InnerBlocks;
    var useBlockProps = blockEditor.useBlockProps;
    var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var Notice = components.Notice;
    var __ = i18n.__;
    var config = window.afdspAreaPickerConfig || { area: {} };

    var DATA_FIELDS = [
        { label: __('Gebietsname', 'afd-spritpreise'), value: 'area_label', sample: __('Chemnitz', 'afd-spritpreise') },
        { label: __('Kraftstoff', 'afd-spritpreise'), value: 'fuel_label', sample: __('Diesel', 'afd-spritpreise') },
        { label: __('Anzahl Tankstellen', 'afd-spritpreise'), value: 'station_count', sample: '50' },
        { label: __('Einleitung', 'afd-spritpreise'), value: 'intro', sample: __('Median aus 50 Tankstellen im Gebiet Chemnitz.', 'afd-spritpreise') },
        { label: __('Aktueller Preis', 'afd-spritpreise'), value: 'current', sample: '2,339 €/l' },
        { label: __('Szenariopreis', 'afd-spritpreise'), value: 'scenario', sample: '1,767 €/l' },
        { label: __('Hinweis zum Szenario', 'afd-spritpreise'), value: 'scenario_note', sample: __('wenn die Entlastungen vollständig ankommen', 'afd-spritpreise') },
        { label: __('Ersparnis je Liter', 'afd-spritpreise'), value: 'saving', sample: '57,2 ct/l' },
        { label: __('Ersparnis in Prozent', 'afd-spritpreise'), value: 'saving_percent', sample: '24,5 % weniger' },
        { label: __('Ersparnis bei 50 Litern', 'afd-spritpreise'), value: 'saving_50l', sample: '28,61 € weniger' },
        { label: __('Tankstellen-Bezeichnung', 'afd-spritpreise'), value: 'station_label', sample: __('Günstigste Tankstelle für Diesel', 'afd-spritpreise') },
        { label: __('Tankstellen-Name', 'afd-spritpreise'), value: 'station_name', sample: 'Tankstelle Beispiel' },
        { label: __('Tankstellen-Adresse', 'afd-spritpreise'), value: 'station_address', sample: 'Musterstraße 1, 09111 Chemnitz' },
        { label: __('Tankstellen-Preis', 'afd-spritpreise'), value: 'station_price', sample: '2,318 €/l' },
        { label: __('Energiesteuer – Änderung', 'afd-spritpreise'), value: 'energy_change', sample: '47,0 ct → 33,0 ct' },
        { label: __('Energiesteuer – Effekt', 'afd-spritpreise'), value: 'energy_effect', sample: '14,0 ct weniger je Liter' },
        { label: __('CO₂-Preis – Änderung', 'afd-spritpreise'), value: 'co2_change', sample: '17,4 ct → 0,0 ct' },
        { label: __('CO₂-Preis – Effekt', 'afd-spritpreise'), value: 'co2_effect', sample: '17,4 ct weniger je Liter' },
        { label: __('Mehrwertsteuer – Änderung', 'afd-spritpreise'), value: 'vat_change', sample: '19 % → 7 %' },
        { label: __('Mehrwertsteuer – Effekt', 'afd-spritpreise'), value: 'vat_effect', sample: __('auf den verbleibenden Nettopreis', 'afd-spritpreise') },
        { label: __('Methodik', 'afd-spritpreise'), value: 'method', sample: __('Median aus gültigen, aktiven Tankstellen. Intern wird ohne vorzeitige Rundung gerechnet.', 'afd-spritpreise') }
    ];

    var DEFAULT_TEMPLATE = [
        ['core/group', { layout: { type: 'constrained' } }, [
            ['core/paragraph', { content: __('Regionale Kraftstoffpreise', 'afd-spritpreise') }],
            ['core/heading', { level: 2, content: __('Das kostet Kraftstoff mit der AfD', 'afd-spritpreise') }],
            ['afd-spritpreise/data-value', { field: 'intro', tagName: 'p' }]
        ]],
        ['afd-spritpreise/fuel-tabs', {}],
        ['core/columns', {}, [
            ['core/column', {}, [
                ['core/paragraph', { content: __('Aktueller Kraftstoffpreis', 'afd-spritpreise') }],
                ['afd-spritpreise/data-value', { field: 'current', tagName: 'strong' }]
            ]],
            ['core/column', {}, [
                ['core/paragraph', { content: __('Nach AfD-Forderungen', 'afd-spritpreise') }],
                ['afd-spritpreise/data-value', { field: 'scenario', tagName: 'strong' }],
                ['afd-spritpreise/data-value', { field: 'scenario_note', tagName: 'small' }]
            ]],
            ['core/column', {}, [
                ['core/paragraph', { content: __('Mögliche Ersparnis', 'afd-spritpreise') }],
                ['afd-spritpreise/data-value', { field: 'saving', tagName: 'strong' }],
                ['afd-spritpreise/data-value', { field: 'saving_percent', tagName: 'small' }]
            ]]
        ]],
        ['core/columns', {}, [
            ['core/column', {}, [
                ['core/heading', { level: 4, content: __('Bei 50 Litern', 'afd-spritpreise') }],
                ['afd-spritpreise/data-value', { field: 'saving_50l', tagName: 'strong' }]
            ]],
            ['core/column', {}, [
                ['afd-spritpreise/data-value', { field: 'station_label', tagName: 'p' }],
                ['afd-spritpreise/data-value', { field: 'station_name', tagName: 'strong' }],
                ['afd-spritpreise/data-value', { field: 'station_address', tagName: 'p' }],
                ['afd-spritpreise/data-value', { field: 'station_price', tagName: 'strong' }]
            ]]
        ]],
        ['core/heading', { level: 3, content: __('In der Rechnung', 'afd-spritpreise') }],
        ['core/group', { layout: { type: 'constrained' } }, [
            ['core/heading', { level: 4, content: __('Energiesteuer auf das EU-Mindestmaß senken', 'afd-spritpreise') }],
            ['core/paragraph', { content: __('Die Energiesteuer wird auf die europäischen Mindeststeuersätze abgesenkt.', 'afd-spritpreise') }],
            ['afd-spritpreise/data-value', { field: 'energy_change', tagName: 'strong' }],
            ['afd-spritpreise/data-value', { field: 'energy_effect', tagName: 'small' }],
            ['core/paragraph', { content: '<a href="https://dserver.bundestag.de/btd/21/063/2106332.pdf">BT-Drs. 21/6332</a>' }]
        ]],
        ['core/group', { layout: { type: 'constrained' } }, [
            ['core/heading', { level: 4, content: __('CO₂-Bepreisung abschaffen', 'afd-spritpreise') }],
            ['core/paragraph', { content: __('Die nationale CO₂-Bepreisung wird im Szenario durch den konfigurierten Zielwert ersetzt.', 'afd-spritpreise') }],
            ['afd-spritpreise/data-value', { field: 'co2_change', tagName: 'strong' }],
            ['afd-spritpreise/data-value', { field: 'co2_effect', tagName: 'small' }],
            ['core/paragraph', { content: '<a href="https://dserver.bundestag.de/btd/21/063/2106334.pdf">BT-Drs. 21/6334</a>' }]
        ]],
        ['core/group', { layout: { type: 'constrained' } }, [
            ['core/heading', { level: 4, content: __('Mehrwertsteuer auf Kraftstoffe auf 7 % senken', 'afd-spritpreise') }],
            ['core/paragraph', { content: __('Für Benzin und Diesel wird der konfigurierte ermäßigte Umsatzsteuersatz verwendet.', 'afd-spritpreise') }],
            ['afd-spritpreise/data-value', { field: 'vat_change', tagName: 'strong' }],
            ['afd-spritpreise/data-value', { field: 'vat_effect', tagName: 'small' }],
            ['core/paragraph', { content: '<a href="https://dserver.bundestag.de/btd/21/053/2105326.pdf">BT-Drs. 21/5326</a>' }]
        ]],
        ['core/heading', { level: 3, content: __('Zur Berechnung', 'afd-spritpreise') }],
        ['afd-spritpreise/data-value', { field: 'method', tagName: 'p' }],
        ['core/paragraph', { content: __('Preisdaten: TankPuls · MTS-K', 'afd-spritpreise') }]
    ];

    function parentEdit(props) {
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
            setAttributes(Object.assign({}, config.area || {}, { defaultFuel: attributes.defaultFuel || 'diesel' }));
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
            return el('button', {
                type: 'button', className: 'afdsp-editor-result', key: result.label + index,
                onClick: function () { choose(result); }
            }, result.label);
        });

        var bbox = ['minLat', 'minLng', 'maxLat', 'maxLng'].map(function (key) {
            return key + ': ' + (typeof attributes[key] === 'number' ? attributes[key].toFixed(6) : '–');
        }).join(' · ');

        var inspector = el(InspectorControls, {},
            el(PanelBody, { title: __('Gebiet', 'afd-spritpreise'), initialOpen: true },
                el(TextControl, {
                    label: __('Ort oder PLZ suchen', 'afd-spritpreise'), value: query, onChange: setQuery,
                    help: __('Photon setzt die Bounding Box des gewählten Ergebnisses.', 'afd-spritpreise')
                }),
                searchError ? el(Notice, { status: 'error', isDismissible: false }, __('Ortssuche derzeit nicht verfügbar.', 'afd-spritpreise')) : null,
                resultButtons.length ? el('div', { className: 'afdsp-editor-results' }, resultButtons) : null,
                el(TextControl, {
                    label: __('Gebietsbezeichnung', 'afd-spritpreise'), value: attributes.areaLabel || '',
                    onChange: function (value) { setAttributes({ areaLabel: value }); }
                }),
                el('p', { className: 'afdsp-editor-bbox' }, bbox)
            ),
            el(PanelBody, { title: __('Daten', 'afd-spritpreise'), initialOpen: true },
                el(SelectControl, {
                    label: __('Standard-Kraftstoff', 'afd-spritpreise'), value: attributes.defaultFuel || 'diesel',
                    options: [
                        { label: 'Diesel', value: 'diesel' },
                        { label: 'Super E5', value: 'e5' },
                        { label: 'Super E10', value: 'e10' }
                    ],
                    onChange: function (value) { setAttributes({ defaultFuel: value }); }
                })
            )
        );

        var blockProps = useBlockProps({ className: 'afdsp afdsp--builder' });
        var innerBlocksProps = useInnerBlocksProps(blockProps, {
            template: DEFAULT_TEMPLATE,
            templateLock: false,
            renderAppender: InnerBlocks.ButtonBlockAppender
        });

        return el(Fragment, {}, inspector, el('section', innerBlocksProps));
    }

    function tabsEdit(props) {
        var fuel = (props.context && props.context['afdsp/defaultFuel']) || 'diesel';
        var labels = { diesel: 'Diesel', e5: 'Super E5', e10: 'Super E10' };
        var blockProps = useBlockProps({ className: 'afdsp-tabs afdsp-builder-tabs' });
        return el('div', blockProps, Object.keys(labels).map(function (value) {
            return el('button', { type: 'button', key: value, className: 'afdsp-tab' + (value === fuel ? ' is-active' : ''), disabled: true }, labels[value]);
        }));
    }

    function dataValueEdit(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var field = DATA_FIELDS.some(function (item) { return item.value === attributes.field; }) ? attributes.field : 'current';
        var selected = DATA_FIELDS.find(function (item) { return item.value === field; }) || DATA_FIELDS[4];
        var tagName = ['div', 'p', 'span', 'strong', 'small'].indexOf(attributes.tagName) !== -1 ? attributes.tagName : 'div';
        var blockProps = useBlockProps({ className: 'afdsp-data-value' });

        return el(Fragment, {},
            el(InspectorControls, {},
                el(PanelBody, { title: __('Datenwert', 'afd-spritpreise'), initialOpen: true },
                    el(SelectControl, {
                        label: __('Wert', 'afd-spritpreise'),
                        value: field,
                        options: DATA_FIELDS.map(function (item) { return { label: item.label, value: item.value }; }),
                        onChange: function (value) { setAttributes({ field: value }); }
                    }),
                    el(SelectControl, {
                        label: __('HTML-Element', 'afd-spritpreise'),
                        value: tagName,
                        options: [
                            { label: 'div', value: 'div' },
                            { label: 'p', value: 'p' },
                            { label: 'span', value: 'span' },
                            { label: 'strong', value: 'strong' },
                            { label: 'small', value: 'small' }
                        ],
                        onChange: function (value) { setAttributes({ tagName: value }); }
                    })
                )
            ),
            el(tagName, blockProps, selected.sample)
        );
    }

    function saveInnerBlocks() { return el(InnerBlocks.Content); }
    function saveDynamic() { return null; }

    blocks.registerBlockType('afd-spritpreise/fuel-price', { edit: parentEdit, save: saveInnerBlocks });
    blocks.registerBlockType('afd-spritpreise/fuel-tabs', { edit: tabsEdit, save: saveDynamic });
    blocks.registerBlockType('afd-spritpreise/data-value', { edit: dataValueEdit, save: saveDynamic });
}(window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n));

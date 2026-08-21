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
        { label: __('Tankstellen-Bezeichnung', 'afd-spritpreise'), value: 'station_label', sample: __('aktuell günstigste Tankstelle für Diesel', 'afd-spritpreise') },
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

    function coreButtonTemplate(label, radius) {
        var attributes = { text: label, tagName: 'button', type: 'button', width: 100 };
        if (radius) attributes.style = { border: { radius: radius } };
        return [['core/buttons', { layout: { type: 'flex', justifyContent: 'stretch' } }, [['core/button', attributes]]]];
    }

    var FUEL_TAB_TEMPLATE = [
        ['afd-spritpreise/fuel-tab', { fuel: 'diesel', label: 'Diesel' }, coreButtonTemplate('Diesel', { topLeft: '8px', topRight: '0px', bottomLeft: '0px', bottomRight: '0px' })],
        ['afd-spritpreise/fuel-tab', { fuel: 'e5', label: 'Super E5' }, coreButtonTemplate('Super E5', { topLeft: '0px', topRight: '0px', bottomLeft: '0px', bottomRight: '0px' })],
        ['afd-spritpreise/fuel-tab', { fuel: 'e10', label: 'Super E10' }, coreButtonTemplate('Super E10', { topLeft: '0px', topRight: '8px', bottomLeft: '0px', bottomRight: '0px' })]
    ];

    var DEFAULT_TEMPLATE = [
        ['core/group', {
            align: 'wide',
            style: {
                spacing: { padding: { top: 'var:preset|spacing|30', bottom: 'var:preset|spacing|30', left: 'var:preset|spacing|30', right: 'var:preset|spacing|30' } },
                background: { gradient: 'linear-gradient(0deg,rgb(0,162,223) 0%,rgb(0,114,179) 100%)' },
                color: { text: '#ffffff' }
            },
            layout: { type: 'constrained' }
        }, [
            ['core/heading', {
                align: 'wide',
                content: __('Kraftstoffpreise mit der AfD', 'afd-spritpreise'),
                fitText: true,
                style: {
                    typography: { textAlign: 'center' },
                    spacing: { padding: { top: '0', bottom: '0' }, margin: { top: '0', bottom: '0', left: '0', right: '0' } }
                }
            }],
            ['core/group', {
                align: 'wide',
                style: {
                    spacing: { blockGap: '0', margin: { top: 'var:preset|spacing|20', bottom: 'var:preset|spacing|20' } },
                    border: { radius: { topLeft: '8px', topRight: '8px', bottomLeft: '8px', bottomRight: '8px' }, width: '0px', style: 'none' },
                    background: { gradient: 'linear-gradient(180deg,rgb(0,64,111) 0%,rgb(8,35,59) 99%)' }
                },
                layout: { type: 'default' }
            }, [
                ['afd-spritpreise/fuel-tabs', {
                    align: 'wide',
                    style: { border: { radius: { topLeft: '0px', topRight: '0px', bottomLeft: '0px', bottomRight: '0px' } } }
                }, FUEL_TAB_TEMPLATE],
                ['core/group', {
                    align: 'wide',
                    style: {
                        color: { text: '#ffffff' },
                        spacing: { blockGap: '0', padding: { top: '0', bottom: '0', left: '0', right: '0' } },
                        border: { radius: { bottomLeft: '8px', bottomRight: '8px' } }
                    },
                    layout: { type: 'grid', minimumColumnWidth: '21rem', autoFit: true }
                }, [
                    ['core/group', {
                        style: {
                            spacing: { blockGap: '0', padding: { top: 'var:preset|spacing|30', bottom: 'var:preset|spacing|30', left: 'var:preset|spacing|30', right: 'var:preset|spacing|30' } },
                            background: { gradient: 'linear-gradient(180deg,rgb(213,23,47) 0%,rgb(162,17,38) 98%)' }
                        },
                        layout: { type: 'flex', orientation: 'vertical', justifyContent: 'center' }
                    }, [
                        ['core/paragraph', { content: __('aktueller Preis', 'afd-spritpreise'), fontSize: 'medium' }],
                        ['afd-spritpreise/data-value', { field: 'current', tagName: 'p', fontSize: 'xx-large', style: { typography: { fontStyle: 'normal', fontWeight: '700' } } }]
                    ]],
                    ['core/group', {
                        style: {
                            spacing: { blockGap: '0', padding: { top: 'var:preset|spacing|30', bottom: 'var:preset|spacing|30', left: 'var:preset|spacing|30', right: 'var:preset|spacing|30' } },
                            background: { gradient: 'linear-gradient(180deg,rgb(0,162,223) 0%,rgb(0,114,179) 100%)' }
                        },
                        layout: { type: 'flex', orientation: 'vertical', justifyContent: 'center' }
                    }, [
                        ['core/paragraph', { content: __('AfD-Preis*', 'afd-spritpreise') }],
                        ['afd-spritpreise/data-value', { field: 'scenario', tagName: 'p', fontSize: 'xx-large', style: { typography: { fontStyle: 'normal', fontWeight: '800' } } }]
                    ]],
                    ['core/group', {
                        style: {
                            spacing: { blockGap: '0', padding: { top: 'var:preset|spacing|30', bottom: 'var:preset|spacing|30', left: 'var:preset|spacing|30', right: 'var:preset|spacing|30' } },
                            background: { gradient: 'linear-gradient(180deg,rgb(0,102,166) 0%,rgb(0,59,99) 100%)' }
                        },
                        layout: { type: 'flex', orientation: 'vertical', justifyContent: 'center' }
                    }, [
                        ['core/group', { style: { spacing: { blockGap: '0' } }, layout: { type: 'flex', orientation: 'vertical', justifyContent: 'center' } }, [
                            ['afd-spritpreise/data-value', { field: 'saving_percent', tagName: 'p' }],
                            ['afd-spritpreise/data-value', { field: 'saving', tagName: 'p', fontSize: 'xx-large', style: { typography: { fontStyle: 'normal', fontWeight: '700' } } }]
                        ]],
                        ['core/group', { fontSize: 'small', style: { spacing: { blockGap: '0' } }, layout: { type: 'flex', flexWrap: 'nowrap', justifyContent: 'center' } }, [
                            ['core/paragraph', { content: __('bei 50 Litern ', 'afd-spritpreise') }],
                            ['afd-spritpreise/data-value', { field: 'saving_50l', tagName: 'span' }]
                        ]]
                    ]]
                ]],
                ['core/group', {
                    style: {
                        typography: { fontStyle: 'italic', fontWeight: '400', fontSize: '0.65rem' },
                        spacing: {
                            margin: { top: 'var:preset|spacing|20', bottom: '0' },
                            padding: { top: '0', bottom: 'var:preset|spacing|20', right: 'var:preset|spacing|20', left: 'var:preset|spacing|20' },
                            blockGap: '0'
                        }
                    },
                    layout: { type: 'flex', flexWrap: 'nowrap', justifyContent: 'space-between' }
                }, [
                    ['afd-spritpreise/data-value', { field: 'intro', tagName: 'span' }],
                    ['core/paragraph', { content: __('Preisdaten: TankPuls · MTS-K', 'afd-spritpreise') }]
                ]]
            ]]
        ]]
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

    function tabsEdit() {
        var blockProps = useBlockProps({ className: 'afdsp-tabs afdsp-builder-tabs' });
        var innerBlocksProps = useInnerBlocksProps(blockProps, {
            template: FUEL_TAB_TEMPLATE,
            templateLock: false,
            allowedBlocks: ['afd-spritpreise/fuel-tab'],
            renderAppender: InnerBlocks.ButtonBlockAppender
        });
        return el('div', innerBlocksProps);
    }

    function tabEdit(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var fuel = ['diesel', 'e5', 'e10'].indexOf(attributes.fuel) !== -1 ? attributes.fuel : 'diesel';
        var defaults = { diesel: 'Diesel', e5: 'Super E5', e10: 'Super E10' };
        var active = ((props.context && props.context['afdsp/defaultFuel']) || 'diesel') === fuel;
        var blockProps = useBlockProps({
            className: 'afdsp-fuel-tab' + (active ? ' is-active' : ''),
            'data-afdsp-fuel-tab': fuel
        });
        var innerBlocksProps = useInnerBlocksProps(blockProps, {
            template: coreButtonTemplate(attributes.label || defaults[fuel]),
            templateLock: 'all',
            allowedBlocks: ['core/buttons']
        });

        return el(Fragment, {},
            el(InspectorControls, {},
                el(PanelBody, { title: __('Kraftstoff-Tab', 'afd-spritpreise'), initialOpen: true },
                    el(SelectControl, {
                        label: __('Kraftstoff', 'afd-spritpreise'), value: fuel,
                        options: [
                            { label: 'Diesel', value: 'diesel' },
                            { label: 'Super E5', value: 'e5' },
                            { label: 'Super E10', value: 'e10' }
                        ],
                        onChange: function (value) { setAttributes({ fuel: value, label: attributes.label || defaults[value] }); }
                    }),
                    el('p', {}, __('Beschriftung und Gestaltung bearbeitest du direkt am enthaltenen WordPress-Button.', 'afd-spritpreise'))
                )
            ),
            el('div', innerBlocksProps)
        );
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

    blocks.registerBlockType('afd-spritpreise/fuel-price', { edit: parentEdit, save: saveInnerBlocks });
    blocks.registerBlockType('afd-spritpreise/fuel-tabs', { edit: tabsEdit, save: saveInnerBlocks });
    blocks.registerBlockType('afd-spritpreise/fuel-tab', { edit: tabEdit, save: saveInnerBlocks });
    blocks.registerBlockType('afd-spritpreise/data-value', { edit: dataValueEdit, save: function () { return null; } });
}(window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n));

(function (blocks, blockEditor, components, element, i18n) {
    'use strict';

    var el = element.createElement;
    var Fragment = element.Fragment;
    var useEffect = element.useEffect;
    var useState = element.useState;
    var InspectorControls = blockEditor.InspectorControls;
    var InnerBlocks = blockEditor.InnerBlocks;
    var RichText = blockEditor.RichText;
    var useBlockProps = blockEditor.useBlockProps;
    var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var Notice = components.Notice;
    var __ = i18n.__;
    var config = window.afdspAreaPickerConfig || { area: {} };

    var DEFAULT_TEMPLATE = [
        ['afd-spritpreise/header', {}],
        ['afd-spritpreise/fuel-tabs', {}],
        ['core/group', { layout: { type: 'constrained' } }, [
            ['core/columns', {}, [
                ['core/column', {}, [['afd-spritpreise/metric', { metric: 'current' }]]],
                ['core/column', {}, [['afd-spritpreise/metric', { metric: 'scenario' }]]],
                ['core/column', {}, [['afd-spritpreise/metric', { metric: 'saving' }]]]
            ]]
        ]],
        ['core/group', { layout: { type: 'flex', flexWrap: 'wrap' } }, [
            ['afd-spritpreise/tank-saving', {}],
            ['afd-spritpreise/cheapest-station', {}]
        ]],
        ['afd-spritpreise/demands', {}],
        ['afd-spritpreise/method', {}]
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

    function headerEdit(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var area = (props.context && props.context['afdsp/areaLabel']) || config.area.areaLabel || '';
        var blockProps = useBlockProps({ className: 'afdsp-component afdsp-builder-header' });
        return el('header', blockProps,
            el(RichText, {
                tagName: 'p', className: 'afdsp-eyebrow', value: attributes.eyebrow || __('Regionale Kraftstoffpreise', 'afd-spritpreise'),
                onChange: function (value) { setAttributes({ eyebrow: value }); }
            }),
            el(RichText, {
                tagName: 'h2', className: 'afdsp-title', value: attributes.title || __('Das kostet Kraftstoff mit der AfD', 'afd-spritpreise'),
                onChange: function (value) { setAttributes({ title: value }); }
            }),
            el('p', { className: 'afdsp-intro' }, area ? __('Gebiet: ', 'afd-spritpreise') + area : __('Gebiet wird aus dem Hauptblock übernommen.', 'afd-spritpreise'))
        );
    }

    function tabsEdit(props) {
        var fuel = (props.context && props.context['afdsp/defaultFuel']) || 'diesel';
        var labels = { diesel: 'Diesel', e5: 'Super E5', e10: 'Super E10' };
        var blockProps = useBlockProps({ className: 'afdsp-component afdsp-tabs afdsp-builder-tabs' });
        return el('div', blockProps, Object.keys(labels).map(function (value) {
            return el('button', { type: 'button', key: value, className: 'afdsp-tab' + (value === fuel ? ' is-active' : ''), disabled: true }, labels[value]);
        }));
    }

    function metricEdit(props) {
        var attributes = props.attributes;
        var setAttributes = props.setAttributes;
        var metric = ['current', 'scenario', 'saving'].indexOf(attributes.metric) !== -1 ? attributes.metric : 'current';
        var labels = {
            current: __('Aktueller Kraftstoffpreis', 'afd-spritpreise'),
            scenario: __('Nach AfD-Forderungen', 'afd-spritpreise'),
            saving: __('Mögliche Ersparnis', 'afd-spritpreise')
        };
        var samples = { current: '–,––– €/l', scenario: '–,––– €/l', saving: '–,– ct/l' };
        var blockProps = useBlockProps({ className: 'afdsp-component afdsp-data-metric afdsp-data-metric--' + metric });
        return el(Fragment, {},
            el(InspectorControls, {}, el(PanelBody, { title: __('Preisfeld', 'afd-spritpreise'), initialOpen: true },
                el(SelectControl, {
                    label: __('Kennzahl', 'afd-spritpreise'), value: metric,
                    options: [
                        { label: __('Aktueller Preis', 'afd-spritpreise'), value: 'current' },
                        { label: __('AfD-Szenario', 'afd-spritpreise'), value: 'scenario' },
                        { label: __('Ersparnis', 'afd-spritpreise'), value: 'saving' }
                    ],
                    onChange: function (value) { setAttributes({ metric: value }); }
                }),
                el(TextControl, {
                    label: __('Eigene Beschriftung', 'afd-spritpreise'), value: attributes.label || '', placeholder: labels[metric],
                    onChange: function (value) { setAttributes({ label: value || undefined }); }
                })
            )),
            el('div', blockProps,
                el('span', { className: 'afdsp-metric-label' }, attributes.label || labels[metric]),
                el('strong', {}, samples[metric]),
                metric !== 'current' ? el('small', {}, __('Live-Wert im Frontend', 'afd-spritpreise')) : null
            )
        );
    }

    function tankSavingEdit() {
        var blockProps = useBlockProps({ className: 'afdsp-component afdsp-tank-saving' });
        return el('div', blockProps, el('span', {}, __('Bei 50 Litern', 'afd-spritpreise')), el('strong', {}, '–,–– € ' + __('weniger', 'afd-spritpreise')));
    }

    function stationEdit() {
        var blockProps = useBlockProps({ className: 'afdsp-component afdsp-builder-station' });
        return el('div', blockProps,
            el('span', {}, __('Günstigste Tankstelle', 'afd-spritpreise')),
            el('div', { className: 'afdsp-station-row' },
                el('div', {}, el('strong', {}, __('Live-Daten im Frontend', 'afd-spritpreise')), el('small', {}, __('Adresse wird dynamisch geladen.', 'afd-spritpreise'))),
                el('div', {}, el('small', {}, __('Preis', 'afd-spritpreise')), el('b', {}, '–,––– €/l'))
            )
        );
    }

    function demandsEdit() {
        var blockProps = useBlockProps({ className: 'afdsp-component afdsp-builder-demands' });
        var rows = [
            __('Energiesteuer auf das EU-Mindestmaß senken', 'afd-spritpreise'),
            __('CO₂-Bepreisung abschaffen', 'afd-spritpreise'),
            __('Mehrwertsteuer auf Kraftstoffe auf 7 % senken', 'afd-spritpreise')
        ];
        return el('section', blockProps,
            el('div', { className: 'afdsp-demands-heading' },
                el('h3', {}, __('Diese drei AfD-Forderungen sind eingerechnet', 'afd-spritpreise')),
                el('p', {}, __('Die Rechenwerte werden im Frontend dynamisch eingesetzt.', 'afd-spritpreise'))
            ),
            el('ol', { className: 'afdsp-demand-list' }, rows.map(function (title, index) {
                return el('li', { key: index },
                    el('div', { className: 'afdsp-demand-copy' }, el('h4', {}, title)),
                    el('div', { className: 'afdsp-demand-calc' }, el('span', {}, __('In der Rechnung', 'afd-spritpreise')), el('strong', {}, '–'))
                );
            }))
        );
    }

    function methodEdit(props) {
        var area = (props.context && props.context['afdsp/areaLabel']) || config.area.areaLabel || '';
        var blockProps = useBlockProps({ className: 'afdsp-component afdsp-builder-method' });
        return el('section', blockProps,
            el('h3', {}, __('Zur Berechnung', 'afd-spritpreise')),
            el('p', {}, area ? __('Die Methodik wird mit Live-Daten für das Gebiet ', 'afd-spritpreise') + area + __(' ausgegeben.', 'afd-spritpreise') : __('Die Methodik wird im Frontend mit Live-Daten ausgegeben.', 'afd-spritpreise'))
        );
    }

    function saveInnerBlocks() { return el(InnerBlocks.Content); }
    function saveDynamic() { return null; }

    blocks.registerBlockType('afd-spritpreise/fuel-price', { edit: parentEdit, save: saveInnerBlocks });
    blocks.registerBlockType('afd-spritpreise/header', { edit: headerEdit, save: saveDynamic });
    blocks.registerBlockType('afd-spritpreise/fuel-tabs', { edit: tabsEdit, save: saveDynamic });
    blocks.registerBlockType('afd-spritpreise/metric', { edit: metricEdit, save: saveDynamic });
    blocks.registerBlockType('afd-spritpreise/tank-saving', { edit: tankSavingEdit, save: saveDynamic });
    blocks.registerBlockType('afd-spritpreise/cheapest-station', { edit: stationEdit, save: saveDynamic });
    blocks.registerBlockType('afd-spritpreise/demands', { edit: demandsEdit, save: saveDynamic });
    blocks.registerBlockType('afd-spritpreise/method', { edit: methodEdit, save: saveDynamic });
}(window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n));

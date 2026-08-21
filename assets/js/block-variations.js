(function (blocks, i18n) {
    'use strict';

    var __ = i18n.__;

    function coreButtonTemplate(label, radius) {
        return [['core/buttons', { layout: { type: 'flex', justifyContent: 'stretch' } }, [[
            'core/button',
            {
                text: label,
                tagName: 'button',
                type: 'button',
                width: 100,
                style: { border: { radius: radius } }
            }
        ]]]];
    }

    var FUEL_TAB_TEMPLATE = [
        ['afd-spritpreise/fuel-tab', { fuel: 'diesel', label: 'Diesel' }, coreButtonTemplate('Diesel', { topLeft: '8px', topRight: '0px', bottomLeft: '0px', bottomRight: '0px' })],
        ['afd-spritpreise/fuel-tab', { fuel: 'e5', label: 'Super E5' }, coreButtonTemplate('Super E5', { topLeft: '0px', topRight: '0px', bottomLeft: '0px', bottomRight: '0px' })],
        ['afd-spritpreise/fuel-tab', { fuel: 'e10', label: 'Super E10' }, coreButtonTemplate('Super E10', { topLeft: '0px', topRight: '8px', bottomLeft: '0px', bottomRight: '0px' })]
    ];

    var FULL_TEMPLATE = [
        ['core/heading', {
            align: 'wide',
            content: __('Das kostet Kraftstoff mit der AfD', 'afd-spritpreise'),
            fitText: true
        }],
        ['core/group', {
            textColor: 'base',
            style: {
                background: { gradient: 'var:preset|gradient|custom-nachtblau' },
                elements: { link: { color: { text: 'var:preset|color|base' } } },
                border: { radius: { topLeft: '8px', topRight: '8px', bottomLeft: '8px', bottomRight: '8px' } }
            },
            layout: { type: 'default' }
        }, [
            ['afd-spritpreise/fuel-tabs', {}, FUEL_TAB_TEMPLATE],
            ['core/group', {
                style: { spacing: { blockGap: '0', margin: { top: '0', bottom: '0' } } },
                layout: { type: 'grid', minimumColumnWidth: '21rem' }
            }, [
                ['core/group', {
                    style: {
                        layout: { selfStretch: 'fit', flexSize: null },
                        spacing: { padding: { top: 'var:preset|spacing|20', bottom: 'var:preset|spacing|20', left: 'var:preset|spacing|20', right: 'var:preset|spacing|20' } },
                        background: { gradient: 'linear-gradient(180deg,rgb(213,23,47) 0%,rgb(162,17,38) 98%)' }
                    },
                    layout: { type: 'flex', orientation: 'vertical', justifyContent: 'stretch' }
                }, [
                    ['core/paragraph', { content: __('aktueller Preis', 'afd-spritpreise'), fontSize: 'small', style: { typography: { textAlign: 'center' } } }],
                    ['afd-spritpreise/data-value', { field: 'current', tagName: 'p', fontSize: 'xx-large', style: { typography: { textAlign: 'center', fontStyle: 'normal', fontWeight: '700' } } }]
                ]],
                ['core/group', {
                    style: {
                        layout: { selfStretch: 'fit', flexSize: null },
                        background: { gradient: 'linear-gradient(180deg,rgb(0,162,223) 0%,rgb(0,114,179) 100%)' },
                        spacing: { padding: { top: 'var:preset|spacing|20', bottom: 'var:preset|spacing|20', left: 'var:preset|spacing|20', right: 'var:preset|spacing|20' } }
                    },
                    layout: { type: 'flex', orientation: 'vertical', justifyContent: 'stretch' }
                }, [
                    ['core/paragraph', { content: __('Nach AfD-Forderungen', 'afd-spritpreise'), fontSize: 'small', style: { typography: { textAlign: 'center' } } }],
                    ['afd-spritpreise/data-value', { field: 'scenario', tagName: 'p', fontSize: 'xx-large', style: { typography: { textAlign: 'center', fontStyle: 'normal', fontWeight: '800' } } }],
                    ['afd-spritpreise/data-value', { field: 'scenario_note', tagName: 'p', fontSize: 'small', style: { typography: { textAlign: 'center' } } }]
                ]],
                ['core/group', {
                    style: {
                        layout: { selfStretch: 'fit', flexSize: null },
                        spacing: { padding: { top: 'var:preset|spacing|20', bottom: 'var:preset|spacing|20', left: 'var:preset|spacing|20', right: 'var:preset|spacing|20' } },
                        background: { gradient: 'linear-gradient(180deg,rgb(0,102,166) 0%,rgb(0,59,99) 100%)' }
                    },
                    layout: { type: 'flex', orientation: 'vertical', justifyContent: 'stretch' }
                }, [
                    ['core/paragraph', { content: __('Mögliche Ersparnis', 'afd-spritpreise'), fontSize: 'small', style: { typography: { textAlign: 'center' } } }],
                    ['afd-spritpreise/data-value', { field: 'saving', tagName: 'p', fontSize: 'xx-large', style: { typography: { textAlign: 'center', fontStyle: 'normal', fontWeight: '700' } } }],
                    ['afd-spritpreise/data-value', { field: 'saving_percent', tagName: 'p', fontSize: 'small', style: { typography: { textAlign: 'center' } } }]
                ]]
            ]],
            ['core/group', {
                fontSize: 'small',
                style: {
                    spacing: {
                        margin: { top: '0', bottom: '0' },
                        padding: { top: 'var:preset|spacing|20', bottom: 'var:preset|spacing|20', left: 'var:preset|spacing|20', right: 'var:preset|spacing|20' }
                    },
                    typography: { fontStyle: 'italic', fontWeight: '400' }
                },
                layout: { type: 'flex', flexWrap: 'wrap', justifyContent: 'space-between' }
            }, [
                ['afd-spritpreise/data-value', {
                    field: 'intro',
                    tagName: 'p',
                    style: { spacing: { padding: { top: '0', bottom: '0', left: '0', right: '0' }, margin: { top: '0', bottom: '0', left: '0', right: '0' } } }
                }],
                ['core/group', {
                    style: { spacing: { blockGap: 'var:preset|spacing|20' } },
                    layout: { type: 'flex', flexWrap: 'nowrap' }
                }, [
                    ['core/paragraph', { content: __('bei 50 Litern', 'afd-spritpreise') }],
                    ['afd-spritpreise/data-value', { field: 'saving_50l', tagName: 'p', style: { typography: { fontStyle: 'normal', fontWeight: '700' } } }]
                ]]
            ]]
        ]],
        ['core/group', { fontSize: 'small', layout: { type: 'constrained' } }, [
            ['core/group', { align: 'wide', layout: { type: 'flex', flexWrap: 'wrap', justifyContent: 'space-between' } }, [
                ['afd-spritpreise/data-value', { field: 'station_label', tagName: 'p' }],
                ['afd-spritpreise/data-value', { field: 'station_name', tagName: 'strong' }],
                ['afd-spritpreise/data-value', { field: 'station_address', tagName: 'p' }],
                ['afd-spritpreise/data-value', { field: 'station_price', tagName: 'strong' }]
            ]]
        ]],
        ['core/heading', { align: 'wide', level: 2, content: __('In der Rechnung', 'afd-spritpreise') }],
        ['core/group', {
            style: {
                color: { background: '#ffffff' },
                shadow: 'var:preset|shadow|shadow-1',
                border: { color: '#cccccc', width: '1px' },
                spacing: { padding: { top: 'var:preset|spacing|20', bottom: 'var:preset|spacing|20', left: 'var:preset|spacing|20', right: 'var:preset|spacing|20' } }
            },
            layout: { type: 'flex', orientation: 'vertical', justifyContent: 'stretch' }
        }, [
            ['core/group', { align: 'wide', layout: { type: 'flex', flexWrap: 'wrap', justifyContent: 'space-between', verticalAlignment: 'center' } }, [
                ['core/heading', { level: 3, content: __('Energiesteuer auf das EU-Mindestmaß senken', 'afd-spritpreise'), style: { layout: { selfStretch: 'fill', flexSize: null } } }],
                ['core/group', { style: { spacing: { blockGap: '0' } }, layout: { type: 'flex', orientation: 'vertical' } }, [
                    ['afd-spritpreise/data-value', { field: 'energy_change', tagName: 'p', fontSize: 'small', style: { layout: { selfStretch: 'fit', flexSize: null }, typography: { textAlign: 'right' } } }],
                    ['afd-spritpreise/data-value', { field: 'energy_effect', tagName: 'p', fontSize: 'small', style: { layout: { selfStretch: 'fit', flexSize: null }, typography: { textAlign: 'right' } } }]
                ]],
                ['core/buttons', { align: 'wide', style: { layout: { selfStretch: 'fit', flexSize: null } }, layout: { type: 'flex', justifyContent: 'space-between', orientation: 'horizontal', verticalAlignment: 'center' } }, [
                    ['core/button', { text: 'BT-Drs. 21/6332', url: 'https://dserver.bundestag.de/btd/21/063/2106332.pdf', style: { dimensions: { width: 'var:preset|dimension|100' } } }]
                ]]
            ]],
            ['core/separator', { align: 'wide' }],
            ['core/group', { align: 'wide', layout: { type: 'flex', flexWrap: 'wrap', justifyContent: 'space-between' } }, [
                ['core/heading', { level: 3, content: __('CO₂-Bepreisung abschaffen', 'afd-spritpreise'), style: { layout: { selfStretch: 'fill', flexSize: null } } }],
                ['core/group', { style: { spacing: { blockGap: '0' } }, layout: { type: 'flex', orientation: 'vertical' } }, [
                    ['afd-spritpreise/data-value', { field: 'co2_change', tagName: 'p', fontSize: 'small', style: { layout: { selfStretch: 'fit', flexSize: null }, typography: { textAlign: 'right' } } }],
                    ['afd-spritpreise/data-value', { field: 'co2_effect', tagName: 'p', fontSize: 'small', style: { layout: { selfStretch: 'fit', flexSize: null }, typography: { textAlign: 'right' } } }]
                ]],
                ['core/buttons', { align: 'wide', style: { layout: { selfStretch: 'fit', flexSize: null } }, layout: { type: 'flex' } }, [
                    ['core/button', { text: 'BT-Drs. 21/6334', url: 'https://dserver.bundestag.de/btd/21/063/2106334.pdf', style: { dimensions: { width: 'var:preset|dimension|100' } } }]
                ]]
            ]],
            ['core/separator', { align: 'wide' }],
            ['core/group', { align: 'wide', layout: { type: 'flex', flexWrap: 'wrap', justifyContent: 'space-between' } }, [
                ['core/heading', { level: 3, content: __('Mehrwertsteuer auf Kraftstoffe auf 7 % senken', 'afd-spritpreise'), style: { layout: { selfStretch: 'fill', flexSize: null } } }],
                ['core/buttons', { align: 'wide', style: { layout: { selfStretch: 'fit', flexSize: null } }, layout: { type: 'flex', justifyContent: 'left' } }, [
                    ['core/button', { text: 'BT-Drs. 21/5326', url: 'https://dserver.bundestag.de/btd/21/053/2105326.pdf', style: { dimensions: { width: 'var:preset|dimension|100' } } }]
                ]]
            ]],
            ['core/separator', { align: 'wide' }],
            ['core/group', { align: 'wide', layout: { type: 'flex', orientation: 'vertical' } }, [
                ['core/heading', { level: 3, content: __('Weitere Abgaben nicht eingerechnet', 'afd-spritpreise') }],
                ['core/paragraph', {
                    content: __('Erdölbevorratungsbeitrag und THG-Quote werden ohne konkrete konfigurierte Zielgröße nicht zusätzlich abgezogen.', 'afd-spritpreise'),
                    className: 'is-style-default',
                    fontSize: 'medium'
                }]
            ]]
        ]],
        ['core/group', { align: 'wide', fontSize: 'small', layout: { type: 'flex', orientation: 'vertical', justifyContent: 'stretch' } }, [
            ['core/heading', { level: 3, content: __('Methodik:', 'afd-spritpreise') }],
            ['core/group', { align: 'wide', layout: { type: 'flex', flexWrap: 'nowrap', justifyContent: 'space-between' } }, [
                ['afd-spritpreise/data-value', { field: 'method', tagName: 'p' }]
            ]]
        ]],
        ['core/paragraph', { content: __('Preisdaten: TankPuls · MTS-K', 'afd-spritpreise'), fontSize: 'small', style: { typography: { textAlign: 'right' } } }]
    ];

    blocks.registerBlockVariation('afd-spritpreise/fuel-price', {
        name: 'full',
        title: __('AfD Spritpreise', 'afd-spritpreise'),
        description: __('Ausführliche Ansicht mit Preisen, Tankstelle, Rechenbestandteilen und Methodik.', 'afd-spritpreise'),
        icon: 'money-alt',
        innerBlocks: FULL_TEMPLATE,
        isDefault: true,
        scope: ['inserter']
    });
}(window.wp.blocks, window.wp.i18n));

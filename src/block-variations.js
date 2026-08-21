(function (blocks, i18n) {
    'use strict';

    var __ = i18n.__;

    var FULL_TEMPLATE = [
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

    blocks.registerBlockVariation('afd-spritpreise/fuel-price', {
        name: 'full',
        title: __('AfD Spritpreise', 'afd-spritpreise'),
        description: __('Ausführliche Ansicht mit Preisen, Tankstelle, Rechenbestandteilen und Methodik.', 'afd-spritpreise'),
        icon: 'money-alt',
        attributes: { layoutPreset: 'full' },
        innerBlocks: FULL_TEMPLATE,
        isDefault: true,
        scope: ['inserter', 'block'],
        isActive: function (attributes) { return attributes.layoutPreset === 'full'; }
    });

    blocks.registerBlockVariation('afd-spritpreise/fuel-price', {
        name: 'compact',
        title: __('AfD Spritpreise – kompakt', 'afd-spritpreise'),
        description: __('Kompakte Preisübersicht mit Kraftstofftabs und drei Preisfeldern.', 'afd-spritpreise'),
        icon: 'index-card',
        attributes: { layoutPreset: 'compact' },
        scope: ['inserter', 'block'],
        isActive: function (attributes) { return attributes.layoutPreset === 'compact'; }
    });
}(window.wp.blocks, window.wp.i18n));

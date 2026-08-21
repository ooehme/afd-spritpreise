const registered = new Map();
const variations = new Map();

globalThis.window = {
    afdspAreaPickerConfig: { area: {} },
    wp: {
        blocks: {
            registerBlockType(name, settings) {
                if (registered.has(name)) throw new Error(`Duplicate block: ${name}`);
                registered.set(name, settings);
            },
            getBlockType(name) {
                return registered.get(name);
            },
            registerBlockVariation(blockName, variation) {
                const key = `${blockName}:${variation.name}`;
                if (variations.has(key)) throw new Error(`Duplicate variation: ${key}`);
                variations.set(key, variation);
            }
        },
        blockEditor: {},
        components: {},
        element: {},
        i18n: { __: (value) => value }
    }
};

await import('../assets/js/block.js');
await import('../assets/js/block-compact.js');
await import('../assets/js/block-variations.js');

const expected = [
    'afd-spritpreise/fuel-price',
    'afd-spritpreise/fuel-price-compact',
    'afd-spritpreise/fuel-tabs',
    'afd-spritpreise/fuel-tab',
    'afd-spritpreise/data-value'
];

for (const name of expected) {
    if (!registered.has(name)) throw new Error(`Missing block registration: ${name}`);
}
if (registered.size !== expected.length) throw new Error(`Unexpected block count: ${registered.size}`);
if (typeof registered.get('afd-spritpreise/fuel-price').save !== 'function') throw new Error('Parent save handler missing.');
if (typeof registered.get('afd-spritpreise/fuel-price-compact').save !== 'function') throw new Error('Compact parent save handler missing.');
if (registered.get('afd-spritpreise/fuel-price-compact').edit !== registered.get('afd-spritpreise/fuel-price').edit) throw new Error('Compact block must share the parent editor implementation.');
if (typeof registered.get('afd-spritpreise/fuel-tabs').save !== 'function') throw new Error('Fuel tabs must persist their child blocks.');
if (typeof registered.get('afd-spritpreise/fuel-tab').save !== 'function') throw new Error('Fuel tab must persist its native Core Button child.');

const full = variations.get('afd-spritpreise/fuel-price:full');
if (!full || variations.size !== 1) throw new Error('Detailed default variation must be registered for the main block.');
if (!full.isDefault || !Array.isArray(full.innerBlocks) || !full.innerBlocks.length) throw new Error('Full variation must be the default and provide its detailed layout.');

for (const removed of [
    'afd-spritpreise/header',
    'afd-spritpreise/metric',
    'afd-spritpreise/tank-saving',
    'afd-spritpreise/cheapest-station',
    'afd-spritpreise/demands',
    'afd-spritpreise/method',
    'afd-spritpreise/price-board',
    'afd-spritpreise/facts'
]) {
    if (registered.has(removed)) throw new Error(`Obsolete block still registered: ${removed}`);
}

console.log(`${registered.size} Gutenberg blocks registered with full and compact entry points.`);

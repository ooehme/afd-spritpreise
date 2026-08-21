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
await import('../assets/js/block-variations.js');

const expected = [
    'afd-spritpreise/fuel-price',
    'afd-spritpreise/fuel-tabs',
    'afd-spritpreise/fuel-tab',
    'afd-spritpreise/data-value'
];

for (const name of expected) {
    if (!registered.has(name)) throw new Error(`Missing block registration: ${name}`);
}
if (registered.size !== expected.length) throw new Error(`Unexpected block count: ${registered.size}`);
if (typeof registered.get('afd-spritpreise/fuel-price').save !== 'function') throw new Error('Parent save handler missing.');
if (typeof registered.get('afd-spritpreise/fuel-tabs').save !== 'function') throw new Error('Fuel tabs must persist their child blocks.');
if (typeof registered.get('afd-spritpreise/fuel-tab').save !== 'function') throw new Error('Fuel tab must persist its native Core Button child.');

const full = variations.get('afd-spritpreise/fuel-price:full');
const compact = variations.get('afd-spritpreise/fuel-price:compact');
if (!full || !compact || variations.size !== 2) throw new Error('Full and compact Gutenberg variations must be registered.');
if (!full.isDefault || !Array.isArray(full.innerBlocks) || !full.innerBlocks.length) throw new Error('Full variation must be the default and provide its detailed layout.');
if (compact.attributes?.layoutPreset !== 'compact') throw new Error('Compact variation must select the compact layout preset.');

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

console.log(`${registered.size} Gutenberg blocks registered with full and compact entry variations.`);

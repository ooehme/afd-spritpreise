const registered = new Map();

globalThis.window = {
    afdspAreaPickerConfig: { area: {} },
    wp: {
        blocks: {
            registerBlockType(name, settings) {
                if (registered.has(name)) throw new Error(`Duplicate block: ${name}`);
                registered.set(name, settings);
            }
        },
        blockEditor: {},
        components: {},
        element: {},
        i18n: { __: (value) => value }
    }
};

await import('../assets/js/block.js');

const expected = [
    'afd-spritpreise/fuel-price',
    'afd-spritpreise/fuel-tabs',
    'afd-spritpreise/data-value'
];

for (const name of expected) {
    if (!registered.has(name)) throw new Error(`Missing block registration: ${name}`);
}
if (registered.size !== expected.length) throw new Error(`Unexpected block count: ${registered.size}`);
if (typeof registered.get('afd-spritpreise/fuel-price').save !== 'function') throw new Error('Parent save handler missing.');

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

console.log(`${registered.size} Gutenberg blocks registered; fixed presentation blocks absent.`);

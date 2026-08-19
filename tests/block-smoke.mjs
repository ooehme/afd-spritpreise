const registered = new Map();

globalThis.window = {
    afdspAreaPickerConfig: { area: {}, display: {}, fontFamilies: [] },
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
        i18n: { __: (value) => value },
        serverSideRender: function ServerSideRender() {}
    }
};

await import('../assets/js/block.js');

const expected = [
    'afd-spritpreise/fuel-price', 'afd-spritpreise/header', 'afd-spritpreise/fuel-tabs',
    'afd-spritpreise/price-board', 'afd-spritpreise/metric', 'afd-spritpreise/facts',
    'afd-spritpreise/tank-saving', 'afd-spritpreise/cheapest-station',
    'afd-spritpreise/demands', 'afd-spritpreise/method'
];
for (const name of expected) {
    if (!registered.has(name)) throw new Error(`Missing block registration: ${name}`);
}
if (registered.size !== expected.length) throw new Error(`Unexpected block count: ${registered.size}`);
if (typeof registered.get('afd-spritpreise/fuel-price').save !== 'function') throw new Error('Parent save handler missing.');

console.log(`${registered.size} Gutenberg blocks registered.`);

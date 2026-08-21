const registered = new Map();
const variations = new Map();
const filters = new Map();

function applyFilters(hook, value, ...args) {
    for (const callback of filters.get(hook) || []) value = callback(value, ...args);
    return value;
}

globalThis.window = {
    afdspAreaPickerConfig: { area: {} },
    wp: {
        hooks: {
            addFilter(hook, namespace, callback) {
                const callbacks = filters.get(hook) || [];
                callbacks.push(callback);
                filters.set(hook, callbacks);
            }
        },
        blocks: {
            registerBlockType(name, settings) {
                if (registered.has(name)) throw new Error(`Duplicate block: ${name}`);
                registered.set(name, applyFilters('blocks.registerBlockType', settings, name));
            },
            getBlockType(name) {
                return registered.get(name);
            },
            registerBlockVariation(blockName, variation) {
                const key = `${blockName}:${variation.name}`;
                if (variations.has(key)) throw new Error(`Duplicate variation: ${key}`);
                variations.set(key, variation);
            },
            unregisterBlockVariation(blockName, name) {
                variations.delete(`${blockName}:${name}`);
            },
            getBlockVariations(blockName, scope) {
                return [...variations.entries()]
                    .filter(([key, variation]) => key.startsWith(`${blockName}:`) && (!scope || !variation.scope || variation.scope.includes(scope)))
                    .map(([, variation]) => variation);
            }
        },
        blockEditor: {},
        components: {},
        element: {
            createElement(type, props, ...children) { return { type, props: props || {}, children }; }
        },
        i18n: { __: (value) => value }
    }
};

await import('../assets/js/block-icon.js');
await import('../assets/js/block.js');
await import('../assets/js/block-compact.js');
await import('../assets/js/block-variations.js');
await import('../assets/js/block-variation-icon.js');

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

const mainIcon = registered.get('afd-spritpreise/fuel-price').icon;
const compactIcon = registered.get('afd-spritpreise/fuel-price-compact').icon;
if (!mainIcon || mainIcon !== compactIcon) throw new Error('Main and compact blocks must share the custom SVG icon.');
if (mainIcon.type !== 'svg' || mainIcon.props.viewBox !== '0 0 512 512') throw new Error('Custom main block SVG icon missing.');

const full = variations.get('afd-spritpreise/fuel-price:full');
if (!full || variations.size !== 1) throw new Error('Detailed default variation must be registered for the main block.');
if (!full.isDefault || !Array.isArray(full.innerBlocks) || !full.innerBlocks.length) throw new Error('Full variation must be the default and provide its detailed layout.');
if (full.icon !== mainIcon) throw new Error('Detailed default variation must use the shared SVG icon.');

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

console.log(`${registered.size} Gutenberg blocks registered with shared main icon and full/compact entry points.`);

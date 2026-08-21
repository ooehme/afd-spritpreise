(function (blocks) {
    'use strict';

    var icon = window.afdspBlockIcon;
    if (!icon) return;

    var variations = blocks.getBlockVariations('afd-spritpreise/fuel-price', 'inserter') || [];
    var full = variations.find(function (variation) { return variation.name === 'full'; });
    if (!full) return;

    blocks.unregisterBlockVariation('afd-spritpreise/fuel-price', 'full');
    blocks.registerBlockVariation(
        'afd-spritpreise/fuel-price',
        Object.assign({}, full, { icon: icon })
    );
}(window.wp.blocks));

(function (blocks) {
    'use strict';

    var base = blocks.getBlockType('afd-spritpreise/fuel-price');
    if (!base) return;

    blocks.registerBlockType('afd-spritpreise/fuel-price-compact', {
        edit: base.edit,
        save: base.save
    });
}(window.wp.blocks));

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'priceUtils',
    'priceBox',
    'jquery/ui',
    'mage/template'
], function ($, utils, priceBox, ui, mageTemplate) {
    'use strict';

    $.widget('mageworx.optionFeatures', {

        options: {
            absolutePriceOptionTemplate: '<%= data.label %>' +
            '<% if (data.finalPrice.value) { %>' +
            ' <%- data.finalPrice.formatted %>' +
            '<% } %>'
        },

        firstRun: function firstRun(optionConfig, productConfig, base, self)
        {
            return;
        },

        update: function update(option, optionConfig, productConfig, base)
        {
            if (typeof productConfig.absolute_price != "undefined" && productConfig.absolute_price == "1") {
                var regularPrice;
                var price = base.calculateSelectedOptionsPrice(true);
                var priceExclTax = base.calculateSelectedOptionsPrice(false);
                if (base.isPriceWithTax() && !base.isDisplayBothPrices()) {
                    regularPrice = priceExclTax;
                } else {
                    regularPrice = price;
                }
                base.setProductFinalPrice(price);
                base.setProductRegularPrice(regularPrice);
                base.setProductPriceExclTax(priceExclTax);
            }
        }
    });

    return $.mageworx.optionFeatures;
});

/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
define([
    'jquery',
    'priceOptions',
    'priceUtils',
    'priceBox',
    'jquery/ui',
    'uiRegistry',
    'underscore',
    'mage/template'
], function ($, price_options, utils, priceBox, ui, registry, _, mageTemplate) {
    'use strict';

    $.widget('mageworx.optionBase', {
        options: {
            optionConfig: {},
            productConfig: {},
            priceHolderSelector: '.price-box',
            optionsSelector: '.product-custom-option',
            optionHandlers: {},
            optionTemplate: '<%= data.label %>' +
            '<% if (data.finalPrice.value) { %>' +
            ' +<%- data.finalPrice.formatted %>' +
            '<% } %>',
            controlContainer: 'dd',
            priceTemplate: '<span class="price"><%- data.formatted %></span>',
            localePriceFormat: {},
            productFinalPrice: 0.0,
            productRegularPrice: 0.0,
            productFinalPriceInclTax: 0.0,
            productRegularPriceInclTax: 0.0,
            displayBothPriceWithAndWithoutTax: false,
            catalogPriceContainsTax: false
        },
        updaters: {},

        /**
         * @private
         */
        _init: function initPriceBundle()
        {
            $(this.options.optionsSelector, this.element).trigger('change');
        },

        _create: function create()
        {
            registry.set('mageworxOptionBase', this);

            var updaters = registry.get('mageworxOptionUpdaters');
            if (!updaters) {
                updaters = {};
            }

            $.extend(this.updaters, updaters);
            this.runUpdaters();
            this.addOptionChangeListeners();
        },

        /**
         * Run all updaters (first run)
         */
        runUpdaters: function () {
            $.each(this.updaters, function (i, e) {
                var handler = e.firstRun;
                if (typeof handler != 'undefined' && handler && handler instanceof Function) {
                    handler(this.options.optionConfig, this.options.productConfig, this, e);
                }
            }.bind(this));
        },

        /**
         * Add event listener on each option change (for updaters)
         */
        addOptionChangeListeners: function addListeners()
        {
            var form = this.element,
                options = $(this.options.optionsSelector, form);
            options.on('change', this.optionChanged.bind(this));
        },

        /**
         * Custom behavior on getting options:
         * now widget able to deep merge accepted configuration with instance options.
         * @param  {Object}  options
         * @return {$.Widget}
         * @private
         */
        _setOptions: function setOptions(options)
        {
            $.extend(true, this.options, options);
            this._super(options);

            return this;
        },

        /**
         * Custom option change-event handler
         * @param {Event} event
         * @private
         */
        optionChanged: function onOptionChanged(event)
        {
            var changes,
                option = $(event.target);

            option.data('optionContainer', option.closest(this.options.controlContainer));

            $.each(this.updaters, function (i, e) {
                var handler = e.update;
                if (handler && handler instanceof Function) {
                    handler(option, this.options.optionConfig, this.options.productConfig, this);
                }
            }.bind(this));
        },

        /**
         * Set product final price
         * @param finalPrice
         */
        setProductFinalPrice: function (finalPrice) {
            var config = this.options,
                format = config.priceFormat,
                template = config.priceTemplate,
                $pc = $('[data-price-type="finalPrice"]'),
                toTemplate = {};

            if (finalPrice <= 0) {
                if (this.isPriceWithTax() && !this.isDisplayBothPrices()) {
                    finalPrice = this.options.productFinalPrice;
                } else {
                    finalPrice = this.options.productFinalPriceInclTax;
                }
            }

            template = mageTemplate(template);
            toTemplate.data = {
                value: finalPrice,
                formatted: utils.formatPrice(finalPrice, format)
            };

            $pc.html(template(toTemplate))
        },

        setProductPriceExclTax: function (priceExcludeTax) {
            var config = this.options,
                format = config.priceFormat,
                template = config.priceTemplate,
                $pc = $('[data-price-type="basePrice"]'),
                toTemplate = {};

            if (priceExcludeTax <= 0) {
                priceExcludeTax = this.options.productFinalPrice;
            }

            template = mageTemplate(template);
            toTemplate.data = {
                value: priceExcludeTax,
                formatted: utils.formatPrice(priceExcludeTax, format)
            };

            $pc.html(template(toTemplate))
        },

        /**
         * Set product regular price
         * @param regularPrice
         */
        setProductRegularPrice: function (regularPrice) {
            var config = this.options,
                format = config.priceFormat,
                template = config.priceTemplate,
                $pc = $('[data-price-type="oldPrice"]'),
                toTemplate = {};

            if (regularPrice <= 0) {
                if (this.isPriceWithTax() && !this.isDisplayBothPrices()) {
                    regularPrice = this.options.productRegularPrice;
                } else {
                    regularPrice = this.options.productRegularPriceInclTax;
                }
            }

            template = mageTemplate(template);
            toTemplate.data = {
                value: regularPrice,
                formatted: utils.formatPrice(regularPrice, format)
            };

            $pc.html(template(toTemplate))
        },

        /**
         * Get summary price from all selected options
         *
         * @returns {number}
         */
        calculateSelectedOptionsPrice: function (withTax) {
            var form = this.element,
                options = $(this.options.optionsSelector, form),
                config = this.options,
                price = 0;

            options.filter('select').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    optionConfig = config.optionConfig && config.optionConfig[optionId],
                    values = $element.val();

                if (typeof values == 'undefined' || !values) {
                    return;
                }

                if (!Array.isArray(values)) {
                    values = [values];
                }

                $(values).each(function (i, e) {
                    if (withTax) {
                        price += parseFloat(optionConfig[e].prices.finalPrice.amount);
                    } else {
                        price += parseFloat(optionConfig[e].prices.basePrice.amount);
                    }
                });
            });

            options.filter('input[type="radio"], input[type="checkbox"]').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    optionConfig = config.optionConfig && config.optionConfig[optionId],
                    value = $element.val();

                if (!$element.is(':checked')) {
                    return;
                }

                if (typeof value == 'undefined' || !value) {
                    return;
                }

                if (withTax) {
                    price += parseFloat(optionConfig[value].prices.finalPrice.amount);
                } else {
                    price += parseFloat(optionConfig[value].prices.basePrice.amount);
                }
            });

            options.filter('input[type="text"], textarea, input[type="file"]').each(function (index, element) {
                var $element = $(element),
                    optionId = utils.findOptionId($element),
                    optionConfig = config.optionConfig && config.optionConfig[optionId],
                    value = $element.val();

                if (typeof value == 'undefined' || !value) {
                    return;
                }

                if (withTax) {
                    price += parseFloat(optionConfig.prices.finalPrice.amount);
                } else {
                    price += parseFloat(optionConfig.prices.basePrice.amount);
                }
            });

            return price;
        },

        /**
         * Get price from html
         *
         * @param element
         * @returns {number}
         */
        getPriceFromHtmlElement: function getPrice(element)
        {
            var pricePattern = this.options.localePriceFormat,
                ds = pricePattern.decimalSymbol,
                gs = pricePattern.groupSymbol,
                pf = pricePattern.pattern,
                ps = pricePattern.priceSymbol,
                price = 0,
                html = $(element).text(),
                priceCalculated;

            priceCalculated = parseFloat(html.replace(new RegExp("'\'" + gs, 'g'), '')
                .replace(new RegExp("'\'" + ds, 'g'), '.')
                .replace(/[^0-9\.,]/g, ''));

            if (priceCalculated) {
                price = priceCalculated;
            }

            return price;
        },

        /**
         * Check is product catalog price already contains tax
         * @returns {number}
         */
        isPriceWithTax: function () {
            return this.toBoolean(this.options.catalogPriceContainsTax);
        },

        /**
         * Check is displayed both prices on the product view page: with tax & without tax
         * @returns {number}
         */
        isDisplayBothPrices: function () {
            return this.toBoolean(this.options.displayBothPriceWithAndWithoutTax);
        },

        /**
         * Convert value to the boolean type
         * @param value
         * @returns {boolean}
         */
        toBoolean: function (value) {
            if (value == 0 ||
                value == "0" ||
                value == false
            ) {
                return false;
            }

            return true;
        }
    });

    return $.mageworx.optionBase;
});

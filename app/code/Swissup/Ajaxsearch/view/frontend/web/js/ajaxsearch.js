define([
    'jquery',
    'uiComponent',
    'Magento_Catalog/js/price-utils',
    'AjaxsearchLoader',
    'mage/template'
], function ($, Component, utils, loader, mageTemplate) {
    var _options = {}, bloodhound, _element;

    function _template(id) {
        return mageTemplate(id);
    }

    function _renderSuggestion(item) {
        // debugger;
        // console.log(item);
        var type = item['_type'] || false;
        if ('debug' == type) {
            console.log(item._select);
        }

        var template = _template('#swissup-ajaxsearch-autocomplete-template');
        if ('product' == type) {
            // item.description = item.short_description + '' || '';
            // if (50 < item.description.lenght) {
            //     item.description = item.description.substr(0, 50) + '...';
            // }
            template = _template('#swissup-ajaxsearch-product-template');
        }

        if ('category' == type) {
            template = _template('#swissup-ajaxsearch-category-template');
        }

        if ('page' == type) {
            template = _template('#swissup-ajaxsearch-page-template');
        }
        return template({
            item: item,
            formatPrice: utils.formatPrice,
            priceFormat: _options.options.priceFormat
        });
    }

    // var _source = function (filter) {
    //     return function (q, sync, async) {
    //         bloodhound.remote.transform = function(datum) {
    //             return $.grep(datum, filter);
    //         };
    //         bloodhound.search(q, sync, async);
    //     }
    // }

    function _init() {
        var loaderCall = 0;
        _element = $('#search');

        bloodhound = new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('title'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            remote: {
                url: _options.url,
                wildcard: _options.wildcard
            }
        });
        bloodhound.initialize();

        loader
            .setContainer(_options.loader.container)
            .setLoaderImage(_options.loader.loaderImage);

        _element.typeahead(_options.typeahead.options, {
            name: 'product',
            source: bloodhound.ttAdapter(),
            // source: _source(function(item) {
            //     console.log('pro');
            //     return item['_type'] == 'product';
            // }),
            displayKey: 'title',
            limit: _options.typeahead.limit,
            templates: {
                notFound: _template('#swissup-ajaxsearch-template-not-found'),
                // pending: _template('#swissup-ajaxsearch-product-template-pending'),
                // header: _template('#swissup-ajaxsearch-product-template-header'),
                // footer: _template('#swissup-ajaxsearch-template-footer'),
                suggestion: _renderSuggestion
            }
        // }, {
        //     name: 'autocomplete',
        //     // source: bloodhound.ttAdapter(),
        //     // source: _source(function(item) {
        //     //     console.log('autocomplete');
        //     //     return !item['_type'];
        //     // }),
        //     displayKey: 'title',
        //     limit: _options.typeahead.limit,
        //     templates: {
        //         header: _template('#swissup-ajaxsearch-autocomplete-template-header'),
        //         suggestion: _renderSuggestion
        //     }
        }
        ).bind('typeahead:selected', function() {
            this.form.submit();
        }).on('typeahead:asyncrequest', function() {
            if (0 === loaderCall) {
                loader.startLoader();
            }
            loaderCall++;
        }).on('typeahead:asynccancel typeahead:asyncreceive', function() {
            loaderCall--;
            if (0 === loaderCall) {
                loader.stopLoader();
            }
        });

        _element.on('blur', $.proxy(function () {
            setTimeout($.proxy(function () {
                _element.closest('div').addClass('inactive');
            }, this), 250);
        }, this));
        _element.trigger('blur');

        _element.on('focus', function () {
            _element.closest('div').removeClass('inactive');
        });
        $('#search_mini_form .search label').on('click', function () {
            _element.closest('div').removeClass('inactive');
        });

        if ($('.block-swissup-ajaxsearch').hasClass('folded')) {
            initFoldedDesign();
        }
    }

    function initFoldedDesign() {
        $('.block-swissup-ajaxsearch').append('<div class="ajaxsearch-mask"></div>');

        var search = {
            show: function() {
                search.calculateFieldPosition();

                // show fields
                $('.block-swissup-ajaxsearch').addClass('shown');
                $('.ajaxsearch-mask').addClass('shown');
                $('#search').focus();
            },
            hide: function() {
                $('.block-swissup-ajaxsearch').removeClass('shown');
                $('.ajaxsearch-mask').removeClass('shown');
            },
            calculateFieldPosition: function() {
                // calculate offsetTop dynamically to guarantee that field
                // will appear in the right place (dynamic header height, etc.)
                var header = $('.header.content');
                if (0 === header.length) {
                    header = $('.header .container');
                }
                var headerOffset = header.offset(),
                    zoomOffset = $('.action.search', '.block-swissup-ajaxsearch').offset(),
                    offsetTop = zoomOffset.top - headerOffset.top;


                if ($('body').width() < 768) {
                    // reset for small screens
                    offsetTop = '';
                } else if (offsetTop <= 0) {
                    return;
                }
                $('.action.close', '.block-swissup-ajaxsearch').css({
                    top: offsetTop
                });
                $('.field.search', '.block-swissup-ajaxsearch').css({
                    paddingTop: offsetTop
                });
            }
        }

        $(document.body).keydown(function(e) {
            if (e.which == 27) {
                search.hide();
            }
        })
        $(window).resize(function() {
            search.calculateFieldPosition();
        });
        $('.ajaxsearch-mask').click(function() {
            search.hide();
        });
        $('.action.search', '.block-swissup-ajaxsearch').click(function(e) {
            e.preventDefault();
            search.show();
        });
        $('.action.close', '.block-swissup-ajaxsearch').click(function(e) {
            e.preventDefault();
            search.hide();
        });
    }

    return Component.extend({
        options: {
            url: "",
            wildcard: "_QUERY",
            loader: {
                container: ".block-swissup-ajaxsearch .actions .action",
                loaderImage: ""
            },
            typeahead: {
                options: {
                    highlight: true,
                    hint: true,
                    minLength: 3,
                    classNames: {}
                },
                limit: 10
            },
            options: {}
        },

        initialize: function () {
            this._super();
            this.setOptions(this.options);
            require([
                'typeaheadbundle'
            ], function () {
                require([
                    'bloodhound', 'typeahead.js'
                ], function (Bloodhound, typeahead) {
                    $(_init);
                });
            });
            return this;
        },
        version: function() {
            return this.options.options.version;
        },
        // getOptions: function() {
        //     return _options;
        // },
        setOptions: function(options) {
            $.extend(_options, options);
            return this;
        }
    });
});
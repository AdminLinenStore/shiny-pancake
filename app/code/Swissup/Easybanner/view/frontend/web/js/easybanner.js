define([
  'jquery',
  'mage/cookies'
], function( $ ) {
    var Easybanner = {};
    //Cookies Class
    Easybanner.Cookie = function() {
        var _cookie = {};

        function write() {
            $.mage.cookies.set(
                'easybanner',
                JSON.stringify(_cookie),
                new Date('Tue, 19 Jan 2038 03:14:07 GMT')
            );
        }
        (function read() {
            var jsonString = $.mage.cookies.get('easybanner');
            if (jsonString && jsonString.length) {
                try {
                    _cookie = JSON.parse(jsonString);
                } catch (e) {}
            }
        }());

        return {
            get: function(bannerId, key, defaultValue) {
                defaultValue = defaultValue || 0;
                if ('undefined' === typeof _cookie[bannerId]) {
                    _cookie[bannerId] = {};
                }

                if (key) {
                    if (undefined !== _cookie[bannerId][key]) {
                        return _cookie[bannerId][key];
                    } else {
                        return defaultValue;
                    }
                } else {
                    return _cookie[bannerId];
                }
            },
            set: function(bannerId, key, value) {
                _cookie[bannerId][key] = value;
                write();
            }
        };
    }();

    //Timer Class
    Easybanner.Timer = function() {
        var _frequency = 1000,
            _timers    = {
                inactivity: 0,
                activity  : 0,
                browsing  : localStorage.getItem('easybanner_timer_browsing') || 0
            };

        function tick() {
            for (var i in _timers) {
                _timers[i]++;
            }

            if (_timers.inactivity >= 10) {
                reset('activity');
            }
        }

        function reset(timer) {
            _timers[timer] = 0;
        }

        setInterval(tick.bind(this), _frequency);

        var events = ['mousemove', 'click', 'scroll', 'keyup'];
        for (var index in events) {
            $(document).on(events[index], $.proxy(reset, this, 'inactivity'));
        }

        $(document).ready( function() {
            // reset browsing time, if last visit was more that two hours ago
            var lastVisit = localStorage.getItem('easybanner_last_visit'),
                now = new Date();

            localStorage.setItem('easybanner_last_visit', now.toISOString());

            if (!lastVisit) {
                return;
            }

            lastVisit = new Date(lastVisit);
            if (isNaN(lastVisit.getTime())) {
                return;
            }
            if (((Math.abs(now - lastVisit) / 1000) / 60) > 120) {
                reset('browsing');
            }
        });
        $(window).on('beforeunload', function() {
            localStorage.setItem('easybanner_timer_browsing', _timers.browsing);
        });

        return {
            getInactivityTime: function() {
                return _timers.inactivity;
            },
            getActivityTime: function() {
                return _timers.activity;
            },
            getBrowsingTime: function() {
                return _timers.browsing;
            }
        };
    }();

    //Rule Class
    Easybanner.Rule = function() {
        var _conditions = {},
            _timer      = Easybanner.Timer,
            _cookie     = Easybanner.Cookie,
            _currentId;

        function _compareCondition(v1, v2, op) {
            var result = false;
            switch (op) {
                case '>':
                    result = (parseInt(v2) > parseInt(v1));
                    break;
                case '<':
                    result = (parseInt(v2) < parseInt(v1));
                    break;
            }
            return result;
        }

        function _validateConditions(filter, aggregator, value) {
            var result = true;
            if (filter.aggregator && filter.conditions) {
                for (var i = 0; i < filter.conditions.length; i++) {
                    var condition = filter.conditions[i];
                    result = _validateConditions(
                        condition, filter.aggregator, filter.value
                    );

                    if ((filter.aggregator == 'all' && filter.value == '1' && !result) ||
                        (filter.aggregator == 'any' && filter.value == '1' && result)) {

                        break;
                    } else if ((filter.aggregator == 'all' && filter.value == '0' && result) ||
                        (filter.aggregator == 'any' && filter.value == '0' && !result)) {

                        result = !result;
                        break;
                    }
                }
            } else if (filter.attribute) {
                var comparator;
                switch (filter.attribute) {
                    case 'browsing_time':
                        comparator = _timer.getBrowsingTime();
                        break;
                    case 'inactivity_time':
                        comparator = _timer.getInactivityTime();
                        break;
                    case 'activity_time':
                        comparator = _timer.getActivityTime();
                        break;
                    case 'display_count_per_customer':
                        comparator = _cookie.get(_currentId, 'display_count');
                        break;
                    case 'scroll_offset':
                        comparator  = $(window).scrollTop();
                        break;
                    default:
                        return true;
                }
                result = _compareCondition(filter.value, comparator, filter.operator);
            }
            return result;
        }

        return {
            validate: function(id) {
                _currentId = id;
                return _validateConditions(_conditions[id]);
            },
            addConditions: function(conditions) {
                for (var i in conditions) {
                    _conditions[i] = conditions[i];
                }
            }
        };
    }();

    //Popup Class
    Easybanner.Popup = function() {
        var _cookie = Easybanner.Cookie,
            _rule   = Easybanner.Rule,
            _bannerIds = [];

        var _lightbox = {
            overlayId: 'easybanner-overlay-el',
            id       : 'easybanner-lightbox-el',
            markup   : [
                '<div id="easybanner-overlay-el" class="easybanner-overlay-el" style="display:none;"></div>',
                '<div id="easybanner-lightbox-el" class="easybanner-lightbox-el" style="display:none;">',
                    '<a href="javascript:void(0)" class="close close-icon">x</a>',
                    '<div class="easybanner-lightbox-content"></div>',
                '</div>'
            ].join(''),
            create: function() {
                $("body").append(this.markup);
                this.overlay = $('#' + this.overlayId);
                this.el      = $('#' + this.id);
            },
            addObservers: function() {
                if (!this._onKeyPressBind) {
                    this._onKeyPressBind = this._onKeyPress.bind(this);
                    this._hideBind = this.hide.bind(this);
                }

                $(this.el).find('.close').on('click', this._hideBind);

                $(this.el).find('img').each(function() {
                    $(this).onload = this.center.bind(this);
                }.bind(this));

                $(document).off('keyup', this._onKeyPressBind);
                $(document).on('keyup', this._onKeyPressBind);

                if ('addEventListener' in window) {
                    window.addEventListener('resize', this.center.bind(this));
                } else {
                    window.attachEvent('onresize', this.center.bind(this));
                }
            },
            getContentEl: function() {
                return $(this.el).children('.easybanner-lightbox-content');
            },
            show: function (html) {
                if (!html) {
                    return;
                }
                if (!this.el) {
                    this.create();
                }
                this.getContentEl().append(html);
                this.addObservers();
                $(this.overlay).fadeIn();
                $(this.el).fadeIn();
                this.center();
            },
            hide: function() {
                if (this._onKeyPressBind) {
                    $(document).off('keyup', this._onKeyPressBind);
                }
                $('.placeholder-lightbox').first().append({
                    bottom: this.getContentEl().next()
                });
                $(this.overlay).hide();
                $(this.el).hide();
            },
            resetLayout: function() {
                this.getContentEl().css({
                    height: 'auto'
                });
                $(this.el).css({
                    width : 0,
                    height: 0
                });
                $(this.el).css({
                    width : 'auto',
                    height: 'auto',
                    margin: 0,
                    left: 0,
                    top : 0
                });
            },
            center: function() {
                this.resetLayout();

                var viewportSize = {
                        'width': $(window).width(),
                        'height': $(window).height()
                    },
                //document.viewport.getDimensions(),
                    width = $(this.el).width(),
                    gap = {
                        horizontal: 50,
                        vertical  : 50
                    };

                if (viewportSize.width < (width + gap.horizontal)) {
                    width = viewportSize.width - gap.horizontal;
                }

                $(this.el).css({
                    width: width +'px',
                    left: '50%',
                    marginLeft: - width / 2 + 'px'
                });

                var height = $(this.el).height();
                if (viewportSize.height < (height + gap.vertical)) {
                    height = viewportSize.height - gap.vertical;
                }
                this.getContentEl().css({
                    height: height + 'px'
                });

                var newHeight = height + parseInt($(this.el).css('paddingTop')) +
                    parseInt($(this.el).css('paddingBottom'));
                $(this.el).css({
                    top: '50%',
                    marginTop: - newHeight / 2 + 'px'
                });
            },
            _onKeyPress: function(e) {
                if (e.keyCode == 27) {
                    this.hide();
                }
            }
        };

        var _awesomebar = {
            id    : 'easybanner-awesomebar-el',
            markup: [
                '<div id="easybanner-awesomebar-el" class="easybanner-awesomebar-el" style="display:none;">',
                    '<a href="javascript:void(0)" class="close close-icon">x</a>',
                    '<div class="easybanner-awesomebar-content"></div>',
                '</div>'
            ].join(''),
            create: function() {
                $("body").append(this.markup);
                this.el = $("#" + this.id);
            },
            addObservers: function() {
                if (!this._hideBind) {
                    this._hideBind = this.hide.bind(this);
                }

                $(this.el).find('.close').on('click', this._hideBind);
            },
            getContentEl: function() {
                return $(this.el).children('.easybanner-awesomebar-content');
            },
            getTransitionDuration: function() {
                var duration = $(this.el).css('transition-duration');
                if (duration) {
                    duration = parseFloat(duration) * 1000;
                } else {
                    return 0;
                }
                return duration;
            },
            show: function (html) {
                if (!html) {
                    return;
                }
                if (!this.el) {
                    this.create();
                }

                this.getContentEl().append(html);
                this.addObservers();

                $(this.el).show();
                setTimeout(function() {
                    $(this.el).css({
                        top: 0
                    });
                }.bind(this), 10);
            },
            hide: function() {
                $(this.el).css({
                    top: - $(this.el).height() - 20 + 'px'
                });
                // time to hide the bar before move it
                setTimeout(function() {
                    $('.placeholder-awesomebar').first().append({
                        bottom: this.getContentEl().next()
                    });
                    this.el.hide();
                }.bind(this), this.getTransitionDuration());
            }
        };

        return {
            init: function() {
                $(".easybanner-popup-banner .easybanner-banner").each(function() {
                    _bannerIds.push($(this).attr('id'));
                });
                this.initBanners();
            },
            initBanners: function() {
                var shownIds = [],
                    limit = _bannerIds.length;
                for (var i = 0; i < limit; ++i) {
                    if (_rule.validate(_bannerIds[i])) {
                        this.show(_bannerIds[i]);
                        shownIds.push(_bannerIds[i]);
                    }
                }
                for (i = 0; i < shownIds.length; ++i) {
                    _bannerIds.splice(_bannerIds.indexOf(shownIds[i]), 1);
                }

                if (_bannerIds.length) {
                    setTimeout(this.initBanners.bind(this), 1000);
                }
            },
            show: function(id) {
                var el = $('#' + id);
                if (!el) {
                    return;
                }

                if ($(el).hasClass('placeholder-lightbox')) {
                    popupObject = _lightbox;
                } else if ($(el).hasClass('placeholder-awesomebar')) {
                    popupObject = _awesomebar;
                } else {
                    return;
                }

                // show only one banner at once
                if (popupObject.el && popupObject.el.visible()) {
                    return;
                }
                popupObject.show(el);

                var count = _cookie.get(id, 'display_count');
                if (!count) {
                    count = 0;
                }
                _cookie.set(id, 'display_count', ++count);
            },
            hide: function(id) {
                var el = $(id);

                if (el.up('.easybanner-lightbox-el')) {
                    popupObject = _lightbox;
                } else if (el.up('.easybanner-awesomebar-el')) {
                    popupObject = _awesomebar;
                } else {
                    return;
                }

                popupObject.hide();
            }
        };
    }();
    return {
        init: function($_conditions) {
            Easybanner.Rule.addConditions($_conditions);
            $( document ).ready(function() {
                Easybanner.Popup.init();
            });
        }
    }
});
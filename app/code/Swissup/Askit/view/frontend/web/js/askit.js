define([
    "jquery"
], function ($) {
    // Askit = function () {
        var _config = {};

        return {
            version: function(){
                return '2.0';
            },
            config: function() {
                return _config;
            },
            setConfig: function(config) {
                jQuery.extend(_config, config );
                // Object.extend(_config, config);
                return this;
            },
            init: function(){
                $(".askit-item-trigger").click(function() {
                    $(this).parent().parent().toggleClass("askit-item--commenting");
                });
            }
        }
    // }();
});
//onready
//document.observe("dom:loaded", function(){
//    Askit.init();
//});
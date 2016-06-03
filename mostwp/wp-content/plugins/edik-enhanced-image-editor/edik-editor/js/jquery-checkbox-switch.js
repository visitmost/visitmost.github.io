(function( $ ) {
    $.fn.CheckboxSwitch = function(options) {

        var settings = $.extend({
            'border-color': '#111',
            'off-gradient-start': '#fff',
            'off-gradient-end': '#ddd',
            'on-gradient-off-start': '#555',
            'on-gradient-off-end': '#333',
            'on-gradient-on-start': '#62c462',
            'on-gradient-on-end': '#57a957',
            'on-text': 'On',
            'off-text': 'Off',
            'onChange': function() {}
        }, options);

        var style_gradiant = function(element, start_color, end_color) {
            element
                .css("background-color", end_color)
                .css("background-repeat", "repeat-x")
                .css("background-image", "-khtml-gradient(linear, left top, left bottom, from(" + start_color + "), to(" + end_color + "))")
                .css("background-image", "-moz-linear-gradient(top, " + start_color + ", " + end_color + ")")
                .css("background-image", "-ms-linear-gradient(top, " + start_color + ", " + end_color + ")")
                .css("background-image", "-webkit-gradient(linear, left top, left bottom, color-stop(0%, " + start_color + "), color-stop(100%, " + end_color + "))")
                .css("background-image", "-webkit-linear-gradient(top, " + start_color + ", " + end_color + ")")
                .css("background-image", "-o-linear-gradient(top, " + start_color + ", " + end_color + ")")
                .css("background-image", "linear-gradient(top, " + start_color + ", " + end_color + ")")
                .css("filter", "e(%(\"progid:DXImageTransform.Microsoft.gradient(startColorstr='%d', endColorstr='%d', GradientType=0)\"," + start_color + "," + end_color + "))");
        };
  
        var style_main = function(element) {
            element.css({
                "border": "1px solid " + settings['border-color'],
                "margin": "0"
            });
        };

        var style_switch_general = function(element) {
            element.css({
                "cursor": "pointer"
            });
        };

        var style_off = function(element) {
            element
                .css("color", "#888");
            style_gradiant(element, settings['off-gradient-start'], settings['off-gradient-end']);
        };

        var style_on_off = function(element) {
            element
                .css("color", "#fff");
            style_gradiant(element, settings['on-gradient-off-start'], settings['on-gradient-off-end']);
        };

        var style_on_on = function(element) {
            element
                .css("color", "#fff");
            style_gradiant(element, settings['on-gradient-on-start'], settings['on-gradient-on-end']);
        };

        return this.each(function() {

            var checkbox = $(this);
            var parent_div = $("<div />").addClass('sliderswitcher-cont');
            var parent_span = $('<span />').addClass('sliderswitcher-cont-span');
            var on_switch = $('<span>' + settings['on-text'] + '</span>').addClass('sliderswitcher-btn sliderswitcher-btn-left');
            var off_switch = $('<span>' + settings['off-text'] + '</span>').addClass('sliderswitcher-btn sliderswitcher-btn-right');

            style_main(parent_span);
            style_switch_general(on_switch);
            style_switch_general(off_switch);
            if (checkbox.prop("checked") == "checked") {
                style_on_on(on_switch);
                style_off(off_switch);
            } else {
                style_off(on_switch);
                style_on_off(off_switch);
            }

            $(this).css("display", "none");
            $(this).wrapAll(parent_div).wrapAll(parent_span);
            $(this).before(on_switch, off_switch);

            on_switch.on("click", function() {
                style_on_on(on_switch);
                style_off(off_switch);
                checkbox.prop("checked", true);
                settings.onChange(checkbox);
             });

            off_switch.on("click", function() {
                style_on_off(off_switch);
                style_off(on_switch);
                checkbox.prop("checked", false);
                settings.onChange(checkbox);
            });
            
            checkbox.on("change", function() {
                if ($(this).prop("checked") === true) {
                    style_on_on(on_switch);
                    style_off(off_switch);
                } else {
                    style_off(on_switch);
                    style_on_off(off_switch);
                }
            });

            
        });

    };
})( jQuery );

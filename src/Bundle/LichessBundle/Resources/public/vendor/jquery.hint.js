(function($) {
    $.fn.hints = function(options) {
        var opts = $.extend({}, $.fn.hints.defaults, options);

        return this.each(function() {

            var input = $(this);
            var initial = input.data('hint');

            input.blur(function() {
                if (input.val() == '' || input.val() == initial) { input.addClass(opts['className']).val(initial); }
            }).focus(function() {
                if (input.val() == initial) { input.removeClass(opts['className']).val(''); }
            }).blur();

            input.closest('form').submit(function() {
                input.focus();
            });
        });
    };

    $.fn.hints.defaults = {
        className: "hinted",
    }
})(jQuery);

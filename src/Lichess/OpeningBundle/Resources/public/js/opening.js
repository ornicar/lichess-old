$(function() {

    var $startButtons = $('#start_buttons');
    if (!$startButtons.length) {
        return;
    }

    function prepareForm() {
        var $form = $('div.lichess_overboard');
        $form.find('div.buttons').buttonset().disableSelection();
        $form.find('button.submit').button().disableSelection();
        $form.find('.time_choice input, .increment_choice input').each(function() {
            var $input = $(this), $value = $input.parent().find('span');
            $input.hide().after($('<div>').slider({
                value: $input.val(),
                min: $input.data('min'),
                max: $input.data('max'),
                range: 'min',
                step: 1,
                animate: true,
                slide: function( event, ui ) {
                    $value.text(ui.value);
                    $input.attr('value', ui.value);
                    $form.find('.color_submits button').toggle(
                        $form.find('.time_choice input').val() > 0 || $form.find('.increment_choice input').val() > 0
                        );
                }
            }));
        });
        $form.find('.clock_choice input').on('change', function() {
            $form.find('.time_choice, .increment_choice').toggle($(this).is(':checked'));
            $.centerOverboard();
        }).trigger('change');
        $form.prepend($('<a class="close"></a>').click(function() {
            $form.remove();
            $startButtons.find('a.active').removeClass('active');
        }));
    }

    $startButtons.find('a').click(function() {
        $startButtons.find('a.active').removeClass('active');
        $(this).addClass('active');
        $('div.lichess_overboard').remove();
        $.ajax({
            url: $(this).attr('href'), 
            success: function(html) {
                $('div.lichess_overboard').remove();
                $('div.lichess_board_wrap').prepend(html);
                prepareForm();
                $.centerOverboard();
            }
        });
        return false;
    });
    $('#lichess').on('submit', 'form', $.lichessOpeningPreventClicks);

    if (window.location.hash) {
        $startButtons.find('a.config_'+window.location.hash.replace(/#/, '')).click();
    }
});

$.lichessOpeningPreventClicks = function() {
    $('div.lichess_overboard, div.hooks_wrap').hide();
};

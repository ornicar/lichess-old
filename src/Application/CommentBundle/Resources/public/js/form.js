$('form.fos_comment_comment_form').live('submit', function() {
    var $form = $(this).addClass('processing').ajaxSubmit({
        success: function(html) {
            $form.closest('div.fos_comment_thread_show').replaceWith(html);
        },
        error: function(xhr, status, error) {
            $form.addClass('error').removeClass('processing');
        }
    });
    return false;
});
$('button.fos_comment_comment_reply_show_form').live('click', function() {
    var $button = $(this);
    var $container = $button.parent().addClass('replying');
    var $reply = $('div.fos_comment_reply_prototype').clone()
        .removeClass('fos_comment_reply_prototype')
        .find('.fos_comment_reply_name_placeholder').text($button.attr('data-name')).end()
        .find('input[name=reply_to]').val($button.attr('data-id')).end()
        .find('.fos_comment_reply_cancel').click(function() {
            $reply.remove();
            $container.removeClass('replying');
        }).end()
        .appendTo($container)
        .find('textarea').focus().end();
});

$(function() {
    $('#lichess div.game_share').on('submit', 'form.fos_comment_comment_form', function() {
        var $form = $(this).addClass('processing');
        var $body = $form.find('textarea.comment_body');
        var $author = $form.find('input.comment_author');
        var data = {};
        data[$body.attr('name')] = $body.val();
        data[$author.attr('name')] = $author.val();
        $.ajax($form.attr('action'), {
            type: 'POST',
            data: data,
            success: function(html) {
                $form.closest('div.fos_comment_thread_show').replaceWith(html);
            },
            error: function(xhr, status, error) {
                $form.addClass('error').removeClass('processing');
            }
        });
        return false;
    }).on('click', 'button.fos_comment_comment_reply_show_form', function() {
        var $button = $(this);
        var $container = $button.parent().addClass('replying');
        var $reply = $('div.fos_comment_reply_prototype').clone()
            .removeClass('fos_comment_reply_prototype')
            .find('.fos_comment_reply_name_placeholder').text($button.attr('data-name')).end()
            .find('.fos_comment_reply_cancel').click(function() {
                $reply.remove();
                $container.removeClass('replying');
            }).end()
            .appendTo($container)
            .find('textarea').focus().end();
        // add the comment parent id to the form action url
        $reply.find('form').attr('action', $reply.find('form').attr('action')+'/'+$button.attr('data-id'));
    });
});

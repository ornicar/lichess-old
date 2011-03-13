$('form.fos_comment_form').live('submit', function() {
    $(this).ajaxSubmit({
        dataType: "json",
        success: function(data, status, xhr, form) {
            if (data.success) {
                $('.fos_comment_thread_comments').append(data.comment);
                form.find('textarea').val("");
            } else {
                form.replaceWith(data.form);
            }
        }
    });
    return false;
});

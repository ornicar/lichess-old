$(function() { 

    var $wrap = $('div.hooks_wrap');
    var $hooks = $wrap.find('div.hooks');
    var url = $hooks.data('url');
    var slowDelay = 3000, fastDelay = delay = 800;
    var timeout;

    function reload() {
        timeout = setTimeout(function() {
            timeout = false;
            $.ajax(url, {
                success: function(html) {
                    // redirections
                    if (/^\//.test(html)) {
                        location.href = html;
                        return;
                    }
                    $wrap.removeClass('hidden');
                    $hooks.html(html);
                },
                complete: function() {
                    reload();
                },
                dataType: 'html',
                type: "GET",
                cache: false,
                timeout: 9000
            });
        },
        delay);
    };
    reload();

    $wrap.delegate('tr.joinable', 'click', function() {
        location.href = $(this).find('a.join').attr('href');
    });
    $wrap.delegate('tr.empty', 'click', function() {
        $('#start_buttons a.config_hook').click();
    });

    $(window).bind('blur', function() {
        delay = slowDelay;
    }).bind('focus', function() {
        delay = fastDelay;
        if (timeout) {
            clearTimeout(timeout);
            reload();
        }
    });
});

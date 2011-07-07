$(function() { 

    var $wrap = $('div.hooks_wrap');
    var $hooks = $wrap.find('div.hooks');
    var $new = $wrap.find('div.new_hook');
    var $overboard = $('div.lichess_overboard');
    var url = $hooks.data('url');

    (function reload() {
        setTimeout(function() {
            $.ajax(url, {
                success: function(html) {
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
        900);
    })();

    $wrap.delegate('tr.joinable', 'click', function() {
        location.href = $(this).find('a.join').attr('href');
    });
    $wrap.delegate('a.new_hook', 'click', function() {
        $new.load($(this).attr('href'), function() {
            var $config = $(this).show();
            $config.find('div.buttons').buttonset().disableSelection();
            $config.find('button.submit').button().disableSelection();
            $overboard.css('top', (238 - $overboard.height() / 2) + 'px').show();
        });
        return false;
    });
});

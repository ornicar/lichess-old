$(function() { 

    var $wrap = $('div.hooks_wrap');
    if (!$wrap.length) {
        return;
    }
    var $hooks = $wrap.find('div.hooks');
    var pollUrl = $hooks.data('poll-url');
    var actionUrls = {
        'cancel': $hooks.data('cancel-url'),
        'join': $hooks.data('join-url')
    };
    var slowDelay = 3000, fastDelay = delay = 100;
    var state = 0;
    var auth = $hooks.data('auth');
    var frozen = false;

    function reload() {
        setTimeout(function() {
            if (frozen) return;
            $.ajax(pollUrl, {
                success: function(data) {
                    if (frozen) return;
                    if (data.redirect) {
                        freeze();
                        location.href = 'http://'+location.hostname+'/'+data.redirect;
                    } else {
                        renderHooks(data);
                    }
                },
                complete: function() {
                    reload();
                },
                dataType: 'json',
                type: "GET",
                cache: false,
                data: {
                    'state': state,
                    'auth': auth
                },
                timeout: 15000
            });
        },
        500);
    };
    reload();

    function renderHooks(data) {
        state = data.state;
        if (data.hooks) {
            var hook, html = '<table>';
            for (id in data.hooks) {
                hook = data.hooks[id];
                html += '<tr'+(hook.action == 'join' ? ' class="joinable"' : '')+'>';
                html += '<td class="color"><span class="'+hook.color+'"></span></td>';
                if (hook.elo) {
                    html += '<td><a href="/@/'+hook.username+'">'+hook.username+'<br />('+hook.elo+')</a></td>';
                } else {
                    html += '<td>'+hook.username+'</td>';
                }
                html += '</td>';
                if (hook.variant == 'Chess960') {
                    html += '<td><a href="http://en.wikipedia.org/wiki/Chess960"><strong>960</strong></a></td>';
                } else {
                    html += '<td></td>';
                }
                html += '<td>'+hook.mode+'</td>';
                html += '<td>'+hook.clock+'</td>';
                html += '<td class="action">';
                html += '<a href="'+actionUrls[hook.action].replace(/\/0{8,12}\//, '/'+hook.id+'/')+'" class="'+hook.action+'"></a>';
                html += '</td></tr>';
            }
        } else {
            var html = '<table class="empty_table"><tr><td colspan="5">'+data.message+'</td></tr></table>';
        }
        $hooks.html(html).find('a.join').click(freeze);
        $wrap.removeClass('hidden');
    }

    function freeze() {
        $.lichessOpeningPreventClicks();
        frozen = true;
    }

    $hooks.on('click', 'table.empty_table tr', function() {
        $('#start_buttons a.config_hook').click();
    });

    if (data = $hooks.data('hooks')) {
        renderHooks(data);
    }
    if (hookId = $hooks.data('my-hook')) {
        $.data(document.body, 'lichess_ping').setData('hook_id', hookId);
    }

    $(window).on('blur', function() {
        delay = slowDelay;
    }).on('focus', function() {
        delay = fastDelay;
    });
});

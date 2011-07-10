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
    var slowDelay = 5000, fastDelay = delay = 100;
    var timeout;
    var state = 0;
    var auth = $hooks.data('auth');

    function reload() {
        timeout = setTimeout(function() {
            timeout = false;
            $.ajax(pollUrl, {
                success: function(data) {
                    if (data.redirect) {
                        redirect('http://'+location.hostname+'/'+data.redirect);
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
                timeout: 10000
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
                html += '<td>'+hook.username;
                if (hook.elo) html += '<br />('+hook.elo+')';
                html += '</td>';
                html += '<td>'+hook.variant+'</td>';
                html += '<td>'+hook.mode+'</td>';
                html += '<td>'+hook.clock+'</td>';
                html += '<td class="action">';
                html += '<a href="'+actionUrls[hook.action].replace(/\/0{8,12}\//, '/'+hook.id+'/')+'" class="'+hook.action+'"></a>';
                html += '</td></tr>';
            }
        } else {
            var html = '<table class="empty_table"><tr><td colspan="5">'+data.message+'</td></tr></table>';
        }
        $hooks.html(html);
        $wrap.removeClass('hidden');
    }

    function redirect(url) {
        $.lichessOpeningPreventClicks();
        clearTimeout(timeout);
        location.href = url;
    }

    $hooks.delegate('tr.joinable', 'click', function() {
        $(this).find('a.join').trigger('click');
    });
    $hooks.delegate('tr.joinable a.join', 'click', function() {
        redirect($(this).attr('href'));
    });
    $hooks.delegate('table.empty_table tr', 'click', function() {
        $('#start_buttons a.config_hook').click();
    });

    if (data = $hooks.data('hooks')) {
        renderHooks(data);
    }
    if (hookId = $hooks.data('my-hook')) {
        $.data(document.body, 'lichess_ping').setData('hook_id', hookId);
    }

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

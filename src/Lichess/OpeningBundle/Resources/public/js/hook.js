$(function() { 

    var $wrap = $('div.hooks_wrap');
    if (!$wrap.length) {
        return;
    }
    var $chat = $("div.lichess_chat");
    var chatExists = $chat.length > 0;
    var $bot = $("div.lichess_bot");
    var $newposts = $("div.new_posts");
    var $hooks = $wrap.find('div.hooks');
    var pollUrl = $hooks.data('poll-url');
    var actionUrls = {
        'cancel': $hooks.data('cancel-url'),
        'join': $hooks.data('join-url')
    };
    var slowDelay = 3000, fastDelay = delay = 100;
    var state = 0;
    var messageId = 0;
    var entryId = 0;
    var auth = $hooks.data('auth');
    var frozen = false;
    var isRegistered = $('#user_tag').length > 0

    function chat() {
        var $form = $chat.find('form');
        $chat.find('.lichess_messages').scrollable();
        var $input = $chat.find('input.lichess_say').one("focus", function() {
            $input.val('').removeClass('lichess_hint');
        });

        // send a message
        $form.submit(function() {
            if ($input.hasClass("lichess_hint")) return false;
            text = $.trim($input.val());
            if (!text) return false;
            if (text.length > 140) {
                alert('Max length: 140 chars. ' + text.length + ' chars used.');
                return false;
            }
            $input.val('');
            $.ajax($form.attr("action"), {
                data: {
                    message: text
                },
                type: 'POST',
                timeout: 8000
            });
            return false;
        });

        $chat.find('a.send').click(function() {
            $input.trigger('click');
            $form.submit();
        });

        // toggle the chat
        $chat.find('input.toggle_chat').change(function() {
            $chat.toggleClass('hidden', ! $(this).attr('checked'));
        }).trigger('change');
    };
    chat();

    function bot() {
      $bot.find('.undertable_inner').scrollable();
      $bot.on("click", "tr", function() {
        location.href = $(this).find('a.watch').attr("href");
      });
    }
    bot();

    var $newpostsinner = $newposts.find('.undertable_inner');
    $newpostsinner[0].scrollTop = 9999999;
    $newpostsinner.scrollable();
    setInterval(function() { 
        $newpostsinner.find('ol').load($newposts.data('url')); 
        $newpostsinner[0].scrollTop = 9999999;
        $('body').trigger('lichess.content_loaded');
    }, 60 * 1000);

    function reload() {
        if (frozen) return;
        $.ajax(pollUrl, {
            success: function(data) {
                if (frozen) return;
                renderPollData(data);
            },
            complete: function() {
                setTimeout(reload, 300);
            },
            dataType: 'json',
            type: "GET",
            cache: false,
            data: $.extend({
                'state': state,
                'entryId': entryId,
                'auth': auth
            }, chatExists ? {'messageId': messageId} : {}),
            timeout: 20000
        });
    };

    function renderPollData(data) {
        if (data.redirect) {
            freeze();
            location.href = 'http://'+location.hostname+'/'+data.redirect;
        } else {
            state = data.state
            renderHooks(data.pool);
            if (chatExists && data.chat) renderChat(data.chat);
            renderTimeline(data.timeline);
            $('body').trigger('lichess.content_loaded');
        }
    }
    var $preload = $("textarea.hooks_preload");
    renderPollData($.parseJSON($preload.val()));
    $preload.remove();
    setTimeout(reload, 2000);

    function renderChat(data) {
        messageId = data.id;
        var html = "", user = "", text = "";
        for (i in data.messages) {
            msg = data.messages[i];
            user = msg["u"];
            html += '<li><span>'
            html += '<a class="user_link" href="/@/'+user+'">'+user.substr(0, 12) + '</a>';
            html += '</span>' + msg["m"] + '</li>';
        }
        if (html != "") {
            $chat.find('.lichess_messages').append(html)[0].scrollTop = 9999999;
        }
    }

    function renderTimeline(data) {
        entryId = data.id;
        var html = "";
        for (i in data.entries) {
          html += '<tr>' + data.entries[i] + '</tr>';
        }
        if (html != "") {
            $bot.find('.lichess_messages').append(html).parent()[0].scrollTop = 9999999;
        }
    }

    function renderHooks(data) {
        if (data.hooks) {
            var hook, html = "", mode;
            $hooks.find('tr').addClass("hideme");
            for (id in data.hooks) {
                if ($tr = $("#" + id).orNot()) {
                    $tr.removeClass("hideme");
                } else {
                    hook = data.hooks[id];
                    html += '<tr id="'+id+'"'+(hook.action == 'join' ? ' class="joinable"' : '')+'>';
                    html += '<td class="color"><span class="'+hook.color+'"></span></td>';
                    if (hook.elo) {
                        html += '<td><a class="user_link" href="/@/'+hook.username+'">'+hook.username.substr(0, 12)+'<br />('+hook.elo+')</a></td>';
                    } else {
                        html += '<td>'+hook.username+'</td>';
                    }
                    html += '</td>';
                    mode = isRegistered ? hook.mode : ""
                    if (hook.variant == 'Chess960') {
                        html += '<td><a href="http://en.wikipedia.org/wiki/Chess960"><strong>960</strong></a> ' + mode + '</td>';
                    } else {
                        html += '<td>'+mode+'</td>';
                    }
                    html += '<td>'+hook.clock+'</td>';
                    html += '<td class="action">';
                    html += '<a href="'+actionUrls[hook.action].replace(/\/0{8,12}\//, '/'+hook.id+'/')+'" class="'+hook.action+'"></a>';
                    html += '</td></tr>';
                }
            }
            $hooks.find("table").removeClass("empty_table").append(html);
        } else {
            var html = '<table class="empty_table"><tr><td colspan="5">'+data.message+'</td></tr></table>';
            $hooks.html(html);
        }
        $hooks.find('a.join').click(freeze);
        $hooks.find("tr.hideme").remove();
        if ($hooks.find("tr").length > 6) {
            $wrap.addClass("large");
        } else {
            $wrap.removeClass("large");
        }
    }

    function freeze() {
        $.lichessOpeningPreventClicks();
        frozen = true;
    }

    $hooks.on('click', 'table.empty_table tr', function() {
        $('#start_buttons a.config_hook').click();
    });

    if (hookId = $hooks.data('my-hook')) {
        $.data(document.body, 'lichess_ping').setData('hook_id', hookId);
    }

    $(window).on('blur', function() {
        delay = slowDelay;
    }).on('focus', function() {
        delay = fastDelay;
    });
});

if (typeof console == "undefined" || typeof console.log == "undefined") console = {
    log: function() {}
};

$(function() {

    // Start ping
    var pingDelay = 3000;
    var pingDelay = pingDelay * 20;
    var connectivity = new $.connectivity($('#connectivity'), {
        delay: pingDelay,
        tolerance: 300
    });
    var ping = new $.ping($('#connectivity').data('ping-url'), {
        delay: pingDelay,
    });
    ping.pushCallback(function() { connectivity.ping(); });
    $.data(document.body, 'lichess_ping', ping);

    // Start game
    var $game = $('div.lichess_game').orNot();
    if ($game) {
        $game.game(lichess_data);
        if (!lichess_data.player.spectator) {
            $('a.blank_if_play').click(function() {
                if ($game.game('isPlayable')) {
                    $(this).attr('target', '_blank');
                }
            });
        }
    }

    var $nbConnectedPlayers = $('#nb_connected_players');
    $nbConnectedPlayers.html($nbConnectedPlayers.html().replace(/(\d+)/, '<strong>$1</strong>'));
    ping.pushCallback(function(data) { $nbConnectedPlayers.html($nbConnectedPlayers.html().replace(/\d+/, data.nbp)); });

    var $nbViewers = $('.nb_viewers');
    if ($nbViewers.length) {
        ping.pushCallback(function(data) {
            $nbViewers.html($nbViewers.html().replace(/(\d+|-)/, data.nbw)).toggle(data.nbw > 0);
        });
    }

    if ($('#user_tag').length) {
        ping.setData('username', $('#user_tag').attr('data-username'));
        ping.pushCallback(function(data) { 
          if (typeof data.nbm != "undefined") $('#nb_messages').text(data.nbm).toggleClass('unread', data.nbm > 0); 
        });
    }

    $('input.lichess_id_input').select();

    // Append marks 1-8 && a-h
    if ($bw = $('div.lichess_board_wrap').orNot()) {
        if ($('div.lichess_homepage').length == 0)
          $.displayBoardMarks($bw, $('#lichess > div.lichess_player_white').length);

        // board color
        var $board = $bw.find('> div.lichess_board');
        var $picker = $('#top a.colorpicker');
        var colors = ['brown', 'grey', 'green', 'blue'];
        var color;
        function setColor(c) {
          color = c;
          $picker.add($board).removeClass(colors.join(' ')).addClass(c);
        }
        setColor($picker.data('color'));
        $picker.click(function() {
          var c = colors[(colors.indexOf(color) + 1) % colors.length];
          setColor(c);
          $.ajax($picker.attr("href"), {
            type: 'POST',
            data: {color: c}
          });
          return false;
        });
    } else {
        $('#top a.colorpicker').remove();
    }

    $.centerOverboard = function() {
        if ($overboard = $('div.lichess_overboard.auto_center').orNot()) {
            $overboard.css('top', (238 - $overboard.height() / 2) + 'px').show();
        }
    };
    $.centerOverboard();

    $('div.lichess_language').click(function() {
        $(this).toggleClass('toggled');
    });

    $('.js_email').one('click', function() {
        var email = ['thibault.', 'dupl', 'essis@', 'gmail.com'].join('');
        $(this).replaceWith($('<a/>').text(email).attr('href', 'mailto:'+email));
    });

    function loadUserLinks() {
        $('a.user_link:not(.qtiped)').each(function() {
            var $this = $(this).addClass("qtiped");
            $this.qtip({
                content: {
                    text: $this.clone(), 
                    ajax: {
                        loading: true,
                        url: $this.attr("href").replace(/@/, "preview"), 
                        type: 'GET',
                        cache: false,
                        success: function(content, status) {
                            this.set('content.text', content);
                            $('body').trigger('lichess.content_loaded');
                        }
                    }
                },
                show: {
                    effect: false,
                    event: 'click'
                },
                hide: {
                    effect: false,
                    event: 'mouseleave',
                    fixed: true,
                    delay: 0
                },
                position: {
                    my: 'top left',  
                    at: 'top left', 
                    target: $this,
                    adjust: {
                        x: -9,
                        y: -6
                    }
                }
            }).click(function() { return false; });
        });
    }
    loadUserLinks();
    $('body').on('lichess.content_loaded', loadUserLinks);

    $.tipsyfy = function($elem) {
        $elem.find('a:not(div.game_list_inner a):not(.notipsy):not(#boardTable a), input, label, div.tipsyme, button').filter('[title]').tipsy({
            fade: true,
            html: false,
            live: true
        });
    };
    $.tipsyfy($('body'));

    if ($autocomplete = $('input.autocomplete').orNot()) {
        $autocomplete.autocomplete({
            source: $autocomplete.attr('data-provider'),
            minLength: 2,
            delay: 100
        });
    }

    $('a.toggle_signin').toggle(function() {
        $('#top').find('div.security').addClass('show_signin_form').find('input:first').focus();
    },
    function() {
        $('#top').find('div.security').removeClass('show_signin_form');
    });

    $('#lichess_message input[value=""]:first, #fos_user_registration_form_username').focus();

    $('#lichess_translation_form_code').change(function() {
        if ("0" != $(this).val()) {
            location.href = $(this).closest('form').attr('data-change-url').replace(/__/, $(this).val());
        }
    });

    $('#incomplete_translation a.close').one('click', function() {
        $(this).parent().remove();
    });

    $('a.delete, input.delete').click(function() {
        return confirm('Delete?');
    });
    $('input.confirm').click(function() {
        return confirm('Confirm this action?');
    });

    $.fn.hints && $('input.hint_me').hints();

    var elem = document.createElement('audio');
    var canPlayAudio = !! elem.canPlayType && elem.canPlayType('audio/ogg; codecs="vorbis"');

    $.playSound = function() {
        if (canPlayAudio && 'on' == $('body').attr('data-sound-enabled')) {
            var sound = $('#lichess_sound_player').get(0);
            sound.play();
            setTimeout(function() {
                sound.pause();
            },
            1000);
        }
    }

    if (canPlayAudio) {
        $('body').append($('<audio id="lichess_sound_player">').attr('src', $('body').attr('data-sound-file')));
        $('#sound_state').click(function() {
            var $toggler = $(this);
            $.post($toggler.attr('href'), {},
            function(data) {
                $toggler.attr('class', 'sound_state_' + data);
                $('body').attr('data-sound-enabled', data);
                $.playSound();
            });
            return false;
        });
        $game && $game.trigger('lichess.audio_ready');
    } else {
        $('#sound_state').addClass('unavailable');
    }

    if (false || /.+\.lichess\.org/.test(document.domain)) {
        var homeUrl = $('#site_title').attr('href');
        setTimeout(function() {
            if ($gameSharing = $('div.game_share_widgets').orNot()) {
                $gameSharing.find('div.plusone_placeholder').replaceWith('<div class="lichess_plusone"><g:plusone size="medium" href="'+homeUrl+'"></g:plusone></div>');
                $gameSharing.find('div.facebook_placeholder').replaceWith('<div class="lichess_facebook"><iframe src="http://www.facebook.com/plugins/like.php?href=' + encodeURIComponent(homeUrl) + '&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe></div>');
                $.getScript('http://platform.twitter.com/widgets.js', function() {
                    $gameSharing.addClass('loaded')
                });
            } else {
                $('div.lichess_social').append('<div class="lichess_stumbleupon"><iframe src="http://www.stumbleupon.com/badge/embed/2/?url='+homeUrl+'"></iframe></div><div class="lichess_facebook"><iframe src="http://www.facebook.com/plugins/like.php?href='+encodeURIComponent(homeUrl)+'%2F&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe></div><div><g:plusone size="medium" href="'+homeUrl+'"></g:plusone></div>');
            }
            $.getScript('https://apis.google.com/js/plusone.js');
        },
        2000);
    }

    if(Boolean(window.chrome)) {
      $("div.addtochrome").show();
    }

});

$.fn.scrollable = function() {
  this.mousewheel(function(e, delta) {
    this.scrollTop -= delta * 30;
    return false;
  });
};

$.fn.orNot = function() {
    return this.length == 0 ? false: this;
};

$.trans = function(text) {
  return lichess_translations[text] ? lichess_translations[text] : text;
}

$.displayBoardMarks = function($board, isWhite) {
    if (isWhite) {
        factor = 1;
        base = 0;
    } else {
        factor = - 1;
        base = 575;
    }
    $board.find('span.board_mark').remove();
    letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    marks = '';
    for (i = 1; i < 9; i++) {
        marks += '<span class="board_mark vert" style="bottom:' + (factor * i * 64 - 38 + base) + 'px;">' + i + '</span>';
        marks += '<span class="board_mark horz" style="left:' + (factor * i * 64 - 35 + base) + 'px;">' + letters[i - 1] + '</span>';
    }
    $board.append(marks);
};

if (/.+\.lichess\.org/.test(document.domain)) {
    //analytics
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-7935029-3']);
    _gaq.push(['_trackPageview']);
    (function() {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = 'http://www.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
}

function urlToLink(text) {
    var exp = /\bhttp:\/\/[a-z]{0,3}\.(lichess\.org[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig;
    return text.replace(exp,"<a href='http://$1'>$1</a>"); 
}

if (!Array.prototype.indexOf) {
    Array.prototype.indexOf = function (obj) {
        for (var i = 0, j = this.length; i < j; i++) {
            if (this[i] === obj)
                return i;
        }
        return -1;
    };
}

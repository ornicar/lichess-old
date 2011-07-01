if (typeof console == "undefined" || typeof console.log == "undefined") console = {
    log: function() {}
};

$(function() {
    var $game = $game = $('div.lichess_game').orNot();
    if ($game) {
        $game.game(lichess_data);
        if (!lichess_data.player.spectator) {
            $('a.blank_if_play').click(function() {
                if ($game.game('isPlayable')) {
                    $(this).attr('target', '_blank');
                }
            });
        }
    } else if ($homeBoard = $('div.lichess_homepage div.lichess_board').orNot()) {
        $homeBoard.find('div.lichess_piece').draggable({
            containment: $homeBoard,
            helper: function() {
                return $('<div>').attr("class", $(this).attr("class")).appendTo($homeBoard);
            },
            revert: true,
            revertDuration: 2000
        }).css('cursor', 'pointer');
    }
    var $nbConnectedPlayers = $('#nb_connected_players').orNot();
    if ($nbConnectedPlayers) {
        $nbConnectedPlayers.html($nbConnectedPlayers.html().replace(/(\d+)/, '<strong>$1</strong>'));
    }
    var $userTag = $userTag = $('#user_tag').orNot();
    var $connectivity = $("#connectivity");
    var showNbConnectedPlayers = function(nb) {
        $nbConnectedPlayers && $nbConnectedPlayers.html($nbConnectedPlayers.html().replace(/\d+/, nb));
    }
    if ($userTag) {
        pingConfig = {
            url: $userTag.attr('data-online-url'),
            dataType: "json",
            onResponse: function(data) {
                showNbConnectedPlayers(data.nbp);
                if (typeof data.nbm != 'undefined') {
                    $('#nb_messages').text(data.nbm).toggleClass('unread', data.nbm > 0);
                }
            }
        };
    } else if ($nbConnectedPlayers) {
        pingConfig = {
            url: $nbConnectedPlayers.attr('data-url'),
            dataType: "text",
            onResponse: showNbConnectedPlayers
        };
    }
    pingConfig.delay = 7000;
    var connectivity = new $.connectivity($connectivity, {
        delay: pingConfig.delay,
        tolerance: 300
    });

    (function ping(config) {
        setTimeout(function() {
            $.ajax(config.url, {
                success: function(data) {
                    config.onResponse(data);
                    connectivity.ping();
                },
                complete: function() {
                    ping(config);
                },
                dataType: config.dataType,
                type: "POST",
                timeout: 5000
            });
        },
        config.delay);
    })(pingConfig);

    if ($config = $('div.game_config_form').orNot()) {
        $('div.lichess_overboard').show();
        $config.find('div.buttons').buttonset().disableSelection();
        $config.find('button.submit').button().disableSelection();
        $config.find('a.show_advanced').one('click', function() {
            $(this).hide();
            $config.find('div.advanced, p.explanations').show();
            centerOverboard();
        });
    }

    if ($overboard = $('div.lichess_overboard').orNot()) {
        var centerOverboard = function() {
            $overboard.css('top', (238 - $overboard.height() / 2) + 'px').show();
        };
        centerOverboard();
    }

    $('input.lichess_id_input').select();

    // Append marks 1-8 && a-h
    if ($bw = $('div.lichess_board_wrap').orNot()) {
        $.displayBoardMarks($bw, $('#lichess > div.lichess_player_white').length);
    }

    function loadLanguageList() {
        $div = $('div.lichess_language');
        if (!$div.hasClass('loaded')) {
            $.get($div.attr('data-path'), function(html) {
                $div.append(html);
            });
            $div.addClass('loaded');
        }
    }
    $('div.lichess_language').click(function() {
        $(this).toggleClass('toggled');
        loadLanguageList();
    }).mouseenter(function() {
        loadLanguageList();
    });

    $('.js_email').one('click', function() {
        var email = ['thibault.', 'duplessis@', 'gmail.com'].join('');
        $(this).replaceWith($('<a/>').text(email).attr('href', 'mailto:'+email));
    });

    $('a:not(div.game_list_inner a):not(.notipsy):not(#boardTable a), input, label, div.tipsyme').filter('[title]').tipsy({
        fade: true,
        html: false,
        live: true
    });

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

    $('#lichess_message input[value=""]:first, #fos_user_user_form_username').focus();

    $('#lichess_translation_form_code').change(function() {
        if ("0" != $(this).val()) {
            location.href = $(this).closest('form').attr('data-change-url').replace(/__/, $(this).val());
        }
    });

    $('#incomplete_translation a.close').one('click', function() {
        $(this).parent().remove();
    });

    $('a.delete').click(function() {
        return confirm('Delete?');
    });

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

    if (false || document.domain == 'lichess.org') {
        setTimeout(function() {
            if ($gameSharing = $('div.game_share_widgets').orNot()) {
                $gameSharing.find('div.plusone_placeholder').replaceWith('<div class="lichess_plusone"><g:plusone size="medium" href="http://lichess.org"></g:plusone></div>');
                $gameSharing.find('div.facebook_placeholder').replaceWith('<div class="lichess_facebook"><iframe src="http://www.facebook.com/plugins/like.php?href=' + encodeURIComponent(document.location.href) + '&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe></div>');
                $.getScript('http://platform.twitter.com/widgets.js', function() {
                    $gameSharing.addClass('loaded')
                });
            } else {
                $('ul.lichess_social').prepend('<li class="lichess_stumbleupon"><iframe src="http://www.stumbleupon.com/badge/embed/2/?url=http://lichess.org/"></iframe></li><li class="lichess_facebook"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Flichess.org%2F&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe></li><li><g:plusone size="medium" href="http://lichess.org"></g:plusone></li>');
            }
            gapi.plusone.go();
        },
        1200);
    }
});

$.fn.orNot = function() {
    return this.length == 0 ? false: this;
};

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

if (document.domain == 'lichess.org') {
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

/* connectivity indicator */
$.connectivity = function(element, options) {
    this.element = element;
    this.options = $.extend({
        tolerance: 300,
        max: 5,
        refreshDelay: 300
    },
    options);
    this.state = this.options.max;
    this.lastPingDate = new Date().getTime();
    this.$bars = this.element.find('span');
    this.start();
};
$.connectivity.prototype = {
    start: function() {
        var self = this;
        setInterval(function() {
            self.refresh()
        },
        self.options.refreshDelay);
    },
    ping: function() {
        var self = this;
        // ping is not disconnected, show at least one bar
        self.state = Math.max(1, self.getStateForCurrentLatency());
        self.show();
        self.lastPingDate = new Date().getTime();
    },
    refresh: function() {
        var self = this;
        state = self.getStateForCurrentLatency();
        if (state < self.state) {
            self.state = state;
            self.show();
        }
    },
    getStateForCurrentLatency: function() {
        self = this;
        latency = new Date().getTime() - self.lastPingDate - self.options.delay;
        if (latency <= 0) {
            return self.options.max;
        }
        waitFactor = self.getLatencyFactor(latency);
        state = Math.max(0, self.options.max - waitFactor);
        return state;
    },
    getLatencyFactor: function(latency) {
        threshold = 0;
        for (factor = 1; factor <= self.options.max; factor++) {
            threshold = factor + threshold;
            limit = threshold * self.options.tolerance;
            if (latency < limit) {
                return factor - 1;
            }
        }
        return self.options.max;
    },
    show: function() {
        this.$bars.removeClass('on').toggleClass('alert', 0 == this.state);
        for (i = 0; i < this.state; i++) {
            $(this.$bars[i]).addClass('on');
        }
    }
};

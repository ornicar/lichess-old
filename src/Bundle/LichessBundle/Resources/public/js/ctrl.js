$(function()
{
    $game = $('div.lichess_game');
    if ($game.length) {
        $game.game(lichess_data);
        $('input').click(function() { this.select(); });
    }
    else {
        // Update number of connected players
        var $nbConnectedPlayers = $('div.nb_connected_players');
        if($nbConnectedPlayers.length) {
            function reloadNbConnectedPlayers() {
                setTimeout(function() {
                    $.get($nbConnectedPlayers.attr('data-url'), function(nb) {
                        $nbConnectedPlayers.text($nbConnectedPlayers.text().replace(/\d+/, nb));
                        reloadNbConnectedPlayers();
                    });
                }, 5000);
            }
            reloadNbConnectedPlayers();
        }
    }

    $('div.lichess_language').hover(function() { $(this).find('ul').fadeIn(300); }, function() { $(this).find('ul').fadeOut(300); });

    // Append marks 1-8 && a-h
    if(($bw = $('div.lichess_board_wrap')).length) {
        $.displayBoardMarks($bw, $('#lichess > div.lichess_player_white').length);
    }

    $('.js_email').text(['thibault.', 'duplessis@', 'gmail.com'].join(''));

    $.fn.tipsy && $('a, input, label, div.lichess_server').not('.notipsy').tipsy({fade: true});

    $('#translation_code').change(function() {
        location.href = $(this).closest('form').attr('data-change-url').replace(/__/, $(this).val());
    });

    var elem = document.createElement('audio');
    var canPlayAudio = !!elem.canPlayType && elem.canPlayType('audio/ogg; codecs="vorbis"');
    
    $.playSound = function() {
        if (canPlayAudio && 'on' == $('body').attr('data-sound-enabled')) {
            var sound = $('#lichess_sound_player').get(0);
            sound.play();
            setTimeout(function() {sound.pause();}, 1000);
        }
    }

    if(canPlayAudio) {
        $('body').append($('<audio id="lichess_sound_player">').attr('src', $('body').attr('data-sound-file')));
        $('#sound_state').css('display', 'block').click(function() {
            var $toggler = $(this);
            $.post($toggler.attr('href'), {}, function(data) {
                $toggler.attr('class', 'sound_state_'+data);
                $('body').attr('data-sound-enabled', data);
                $.playSound();
            });
            return false;
        });
        $game.trigger('lichess.audio_ready');
    }
    else {
        $('div.lichess_goodies_wrap').append('<br />Your browser is deprecated, please consider upgrading!<br /><a href="http://getfirefox.com" target="_blank"><img src="http://sfx-images.mozilla.org/firefox/3.6/96x31_edit_green.png" width="96" height="31" /></a>');
    }

    //uservoice
    if(document.domain == 'lichess.org') {
        setTimeout(function() {
            // share links
            $('ul.lichess_social').html('<li class="lichess_stumbleupon"><iframe src="http://www.stumbleupon.com/badge/embed/2/?url=http://lichess.org/"></iframe></li><li class="lichess_facebook"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Flichess.org%2F&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe></li><li class="lichess_add2any"><a class="a2a_dd" href="http://www.addtoany.com/share_save?linkurl=http%3A%2F%2Flichess.org%2F&amp;linkname=Best%20web%20Chess%20game%20ever!"><img src="http://static.addtoany.com/buttons/share_save_171_16.png" width="171" height="16" alt="Share/Bookmark"/></a></li>');
            // uservoice
            (function() {
                var uservoice = document.createElement('script'); uservoice.type = 'text/javascript'; uservoice.async = true; uservoice.src = 'http://cdn.uservoice.com/javascripts/widgets/tab.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(uservoice, s);
            })();
            $('a.lichess_uservoice').click(function() {
                UserVoice.Popin.show({ key: 'lichess', host: 'lichess.uservoice.com', forum: '62479', showTab: false });
            });
            //add2any
            var a2a_config = a2a_config || {};
            a2a_config.linkname = "I'm playing Chess on lichess.org";
            a2a_config.linkurl = "http://lichess.org/";
            (function() {
                var a = document.createElement('script'); a.type = 'text/javascript'; a.async = true; a.src = 'http://static.addtoany.com/menu/page.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(a, s);
            })();
        }, 800);
    }
});

var _jQueryAjax = $.ajax;
$.ajax = function(o) {
    if($.isFunction(o.url)) {
        o.url = o.url();
    }
    return _jQueryAjax(o);
}

$.displayBoardMarks = function($board, isWhite)
{
    if(isWhite) {
        factor = 1;
        base = 0;
    } else {
        factor = -1;
        base = 575;
    }
    $board.find('span.lichess_mark').remove();
    letters = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    for(i=1; i<9; i++) {
        $board.append($('<span>').addClass('lichess_mark').text(i).css({'right': -10, 'bottom': factor*i*64 - 38 + base}));
        $board.append($('<span>').addClass('lichess_mark').text(letters[i-1]).css({'bottom': -14, 'left': factor*i*64 - 35 + base}));
    }
};

if(document.domain == 'lichess.org') {
    //analytics
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-7935029-3']);
    _gaq.push(['_trackPageview']);
    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = 'http://www.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
}

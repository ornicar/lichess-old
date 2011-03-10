$(function() {
	if ($game = $('div.lichess_game').orNot()) {
		$game.game(lichess_data);
		$('input').click(function() {
			this.select();
		});
		if (!lichess_data.player.spectator) $('a.blank_if_play').attr('target', '_blank');
	}
    var $nbConnectedPlayers = $('#nb_connected_players').orNot();
    var $userTag = $userTag = $('#user_tag').orNot();
    var $connectivity = $("#connectivity");
    var pingDelay = 5000;
    var connectivity = new $.connectivity($connectivity, { delay: pingDelay, tolerance: 300 });
	if ($userTag) {
		function onlinePing() {
			setTimeout(function() {
				$.get($userTag.attr('data-online-url'), function(data) {
					showNbConnectedPlayers(data.nbp);
					if (typeof data.nbm != 'undefined') {
						$('#nb_messages').text(data.nbm).toggleClass('unread', data.nbm > 0);
					}
                    connectivity.ping();
					onlinePing();
				},
				"json");
			},
			pingDelay);
		};
		onlinePing();
	}
	else if ($nbConnectedPlayers) {
		function reloadNbConnectedPlayers() {
			setTimeout(function() {
				$.get($nbConnectedPlayers.attr('data-url'), function(nb) {
					$nbConnectedPlayers.text($nbConnectedPlayers.text().replace(/\d+/, nb));
                    connectivity.ping();
					reloadNbConnectedPlayers();
				});
			},
			pingDelay);
		};
		reloadNbConnectedPlayers();
	}

	function showNbConnectedPlayers(nb) {
		if ($nbConnectedPlayers) $nbConnectedPlayers.text($nbConnectedPlayers.text().replace(/\d+/, nb));
	}

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

	// Append marks 1-8 && a-h
	if ($bw = $('div.lichess_board_wrap').orNot()) {
		$.displayBoardMarks($bw, $('#lichess > div.lichess_player_white').length);
	}

    function loadLanguageList() {
        $div = $('div.lichess_language');
		if (!$div.hasClass('loaded')) {
			$.get($div.attr('data-path'), function(html) { $div.append(html); });
            $div.addClass('loaded');
		}
    }
	$('div.lichess_language').click(function() {
        $(this).toggleClass('toggled');
        loadLanguageList();
	}).mouseenter(function() {
        loadLanguageList();
    });

	$('.js_email').text(['thibault.', 'duplessis@', 'gmail.com'].join('')).removeClass('js_email');

	$.fn.tipsy && $('a, input, label, div.tipsyme').not('.notipsy').tipsy({ fade: true });

	if ($autocomplete = $('input.autocomplete').orNot()) {
		$autocomplete.autocomplete({
			source: $autocomplete.attr('data-provider'),
			minLength: 2,
			delay: 100
		});
	}

	$('a.toggle_signin').toggle(function() {
		$('#top').find('div.security').addClass('show_signin_form').find('input:first').focus();
	}, function() {
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
		if ($('a.lichess_table_not_started').length) {
			$('div.lichess_goodies_wrap').append('<br />Your browser does not support latest HTML5 features, please consider upgrading.<br /><a href="http://getfirefox.com" target="_blank"><img src="http://sfx-images.mozilla.org/firefox/3.6/96x31_edit_green.png" width="96" height="31" /></a>');
		}
	}

	if (document.domain == 'lichess.org') {
		setTimeout(function() {
			// share links
			$('ul.lichess_social').html('<li class="lichess_stumbleupon"><iframe src="http://www.stumbleupon.com/badge/embed/2/?url=http://lichess.org/"></iframe></li><li class="lichess_facebook"><iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Flichess.org%2F&amp;layout=button_count&amp;show_faces=false&amp;width=110&amp;action=like&amp;font=lucida+grande&amp;colorscheme=light&amp;height=22"></iframe></li>');
		},
		800);
	}
});

jQuery.fn.orNot = function() {
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
		marks += '<span class="board_mark vert" style="bottom:'+ (factor * i * 64 - 38 + base) +'px;">'+ i +'</span>';
		marks += '<span class="board_mark horz" style="left:'+ (factor * i * 64 - 35 + base) +'px;">'+ letters[i-1] +'</span>';
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

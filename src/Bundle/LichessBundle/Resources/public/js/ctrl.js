$(function()
{
    $game = $('div.lichess_game');
    if ($game.length)
    {
        $game.game(lichess_data);

        $('input').click(function() { this.select(); });

        setTimeout(function() {
            $('a.invite_tools').css('display', 'block').click(function() { $('div.invite_tools').toggle(); });
        }, 3000);
    }

    $('div.lichess_language').hoverIntent({
        over: function() { $(this).find('ul').show(); },
        out: function() { $(this).find('ul').hide(); },
        timeout: 100
    });

    $('.js_email').text(['thibault.', 'duplessis@', 'gmail.com'].join(''));

    $('a, input, label, div.lichess_server').tipsy({fade: true});

    $('#translation_code').change(function() {
        location.href = $(this).closest('form').attr('data-change-url').replace(/__/, $(this).val());
    });

    $('#sound_state').click(function() {
        var $toggler = $(this);
        $.post($toggler.attr('href'), {}, function(data) {
            $toggler.attr('class', 'sound_state_'+data);
            $('body').attr('data-sound-enabled', data);
            $.playSound();
        });
        return false;
    });
    
    $.playSound = function(callback) {
        if ('on' == $('body').attr('data-sound-enabled')) {
            $('body').append('<audio id="lichess_sound" src="'+$('body').attr('data-sound-file')+'" autoplay="autoplay"></audio>');
            setTimeout(function() {
                $('#lichess_sound').remove();
                $.isFunction(callback || null) && callback();
            }, 1000);
        }
        else {
            $.isFunction(callback || null) && callback();
        }
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
        }, 300);
    }
});

var _jQueryAjax = $.ajax;
$.ajax = function(o) {
    if($.isFunction(o.url)) {
        o.url = o.url();
    }
    return _jQueryAjax(o);
}

if(document.domain == 'lichess.org') {
    // chartbeat
    var _sf_async_config={uid:2506,domain:"lichess.org"};
    (function(){
    function loadChartbeat() {
        window._sf_endpt=(new Date()).getTime();
        var e = document.createElement('script');
        e.setAttribute('language', 'javascript');
        e.setAttribute('type', 'text/javascript');
        e.setAttribute('src', "http://static.chartbeat.com/js/chartbeat.js");
        document.body.appendChild(e);
    }
    var oldonload = window.onload;
    window.onload = (typeof window.onload != 'function') ?
        loadChartbeat : function() { oldonload(); loadChartbeat(); };
    })();
    //analytics
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-7935029-3']);
    _gaq.push(['_trackPageview']);
    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = 'http://www.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
}

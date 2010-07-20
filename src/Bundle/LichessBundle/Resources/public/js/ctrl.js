$(function()
{
    $game = $('div.lichess_game');
    if ($game.length)
    {
        $game.game(lichess_data);

        $('input').click(function() { this.select(); });

        setTimeout(function() {
            $('a.invite_tools').css('display', 'block').click(function() { $('div.invite_tools').toggle(); });
        }, 2000);
    }

    $('.js_email').text(['thibault.', 'duplessis@', 'gmail.com'].join(''));

    $('a, input, label, div.lichess_server').tipsy({fade: true});

    //uservoice
    if(document.domain == 'lichess.org') {
        (function() {
            var uservoice = document.createElement('script'); uservoice.type = 'text/javascript'; uservoice.async = true; uservoice.src = 'http://cdn.uservoice.com/javascripts/widgets/tab.js';
            var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(uservoice, s);
        })();
        $('a.lichess_uservoice').click(function()
        {
            UserVoice.Popin.show({
                key: 'lichess',
                host: 'lichess.uservoice.com', 
                forum: '62479',
                showTab: false
            });
        });
    }
});

var _jQueryAjax = $.ajax;
$.ajax = function(o) {
    if($.isFunction(o.url)) {
        o.url = o.url();
    }
    return _jQueryAjax(o);
}

//add2any
var a2a_config = a2a_config || {};
a2a_config.linkname = "I'm playing Chess on lichess.org";
a2a_config.linkurl = "http://lichess.org/";
(function() {
    var a = document.createElement('script'); a.type = 'text/javascript'; a.async = true; a.src = 'http://static.addtoany.com/menu/page.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(a, s);
})();

//analytics
if(document.domain == 'lichess.org') {
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', 'UA-7935029-3']);
    _gaq.push(['_trackPageview']);
    (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = 'http://www.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
    })();
}

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

$(function()
{
    $game = $('div.lichess_game');
    if ($game.length)
    {
        $game.game(lichess_data);

        $game.find('a.lichess_toggle_join_url').click(function()
        {
            $game.find('div.lichess_join_url').toggle(100);
        });
    }

    $('.js_email').text(['thibault.', 'duplessis@', 'gmail.com'].join(''));

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

/*
 * Queued Ajax requests.
 * A new Ajax request won't be started until the previous queued 
 * request has finished.
 */
$.ajaxQueue = function(o){
	var _old = o.success;
	o.success = function(){
		if (_old) _old.apply( this, arguments );
		$.dequeue($.ajaxQueue, "ajax");
	};

    var send = function() {
        if($.isFunction(o.url)) {
            o.url = o.url();
        }
        $.ajax(o);
    }

    if($.queue($.ajaxQueue, "ajax").length) {
        $.queue($.ajaxQueue, "ajax", function() {
            send(o);
        });
    }
    else {
        send(o);
    }
};

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

if(typeof lichess_data != 'undefined') {
    lichess_socket = {
        time: lichess_data.time,
        connect: function(url, callback)
        {
            $.ajax({
                dataType:   'json',
                url:        url,
                success:    function(data) {
                    if(data && data.time > lichess_socket.time) {
                        lichess_socket.time = data.time;
                        callback(data);
                    }
                    else {
                        callback(false);
                    }
                },
                cache:      false,
                error:      function(XMLHttpRequest, textStatus, errorThrown) {
                    location.href=location.href;
                }
            });
        }
    };
}

$(function()
{
  $game = $('div.lichess_game');
  if ($game.length)
  {
    if(lichess_data.game.started)
    {
      $game.game(lichess_data);
    }
    else
    {
      $game.find('a.lichess_toggle_join_url').click(function()
      {
        $game.find('div.lichess_join_url').toggle(100);
      });
      
      setTimeout(waitForOpponent = function()
      {
          lichess_socket.connect(lichess_data.url.socket, function(data) {
              if(data && data.url) {
                  location.href = data.url;
              }
              else {
                  setTimeout(waitForOpponent, lichess_data.beat_delay);
              }
          });
      }, lichess_data.beat_delay);
    }
  }
  $('.js_email').text(['thibault.', 'duplessis@', 'gmail.com'].join(''));

    //uservoice
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
});

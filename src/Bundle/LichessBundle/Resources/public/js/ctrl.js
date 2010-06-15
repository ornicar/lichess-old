if(typeof lichess_data != 'undefined') {
    lichess_socket = {
        time: lichess_data.time,
        connect: function(url, callback)
        {
            $.ajax({
                dataType:   'json',
                url:        url,
                success:    function(data) {
                    if(data.time > lichess_socket.time) {
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
      
      setTimeout(function()
      {
        setTimeout(waitForOpponent = function()
        {
            lichess_socket.connect(lichess_data.url.socket, function(data) {
                if(data && data.url) {
                    location.href = data.url;
                }
                else {
                    setTimeout(waitForOpponent, lichess_data.beat.delay);
                }
            });
        }, lichess_data.delay);
      }, 6000);
    }
  }
  $('.js_email').text(['thibault.', 'duplessis@', 'gmail.com'].join(''));
});

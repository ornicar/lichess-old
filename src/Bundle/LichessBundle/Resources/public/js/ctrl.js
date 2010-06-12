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
                setTimeout(waitForOpponent, lichess_data.beat.delay);
            }
        });
      }, lichess_data.delay);
    }
  }
});

$(function()
{
  $game = $game = $('div.lichess_game');
  if ($game.length)
  {
    if(lichess_data.game.started)
    {
      $game.game(lichess_data);
    }
    else
    {
      $game.find('a.toggle_join_url').click(function()
      {
        $game.find('div.lichess_join_url').toggle(100);
      });
      
      setTimeout(waitForOpponent = function()
      {
        $.ajax({
          url:       lichess_data.url.wait,
          success:   function(response)
          {
            response == 'wait' ? setTimeout(waitForOpponent, lichess_data.beat.delay) : location.href = response;
          }
        });
      }, lichess_data.delay);
    }
  }
});

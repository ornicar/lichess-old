$(function()
{
    if($users = $('#lichess_user div.online_users').orNot()) {
        // Update online users
        var onlineUserUrl = $users.attr('data-reload-url');
        function reloadOnlineUsers() {
            setTimeout(function() {
                $.get(onlineUserUrl, function(html) {
                    $users.find('div.online_users_inner').html(html);
                    reloadOnlineUsers();
                });
            }, 5000);
        };
        reloadOnlineUsers();

        var $nbPlayersTag = $users.find('.players_count');
        var $anonsTag = $users.find('.anonymous_users');
        var oldNbpFn = $.websocketSettings.events.nbp
        $.websocketSettings.events.nbp = function(e) {
          oldNbpFn(e);
          $nbPlayersTag.html(e).removeClass('none');
          $anonsTag.html(str_repeat('<li></li>', e));
        };
    }

    if($searchForm = $('form.search_user_form').orNot()) {
        $searchInput = $searchForm.find('input.search_user');
        $searchInput.on('autocompleteselect', function(e, ui) {
            setTimeout(function() {$searchForm.submit();},10);
        });
        $searchForm.submit(function() {
            location.href = $searchForm.attr('action')+'/'+$searchInput.val();
            return false;
        });
    }

    new $.websocket("ws://127.0.0.1:9000/socket", 0);
});
function str_repeat(input, multiplier) { 
  return new Array(multiplier + 1).join(input);
}

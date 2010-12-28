$(function()
{
    if($gamelist = $('div.game_list').orNot()) {
        refreshUrl = $gamelist.attr('data-url');
        // Update games
        function reloadGameList() {
            setTimeout(function() {
                $.get(refreshUrl, function(html) {
                    $gamelist.html(html);
                    reloadGameList();
                });
            }, 2500);
        };
        reloadGameList();
    }
});

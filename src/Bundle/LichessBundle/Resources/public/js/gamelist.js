$(function()
{
    var $gamelist = $('div.game_list');
    var refreshUrl = $gamelist.attr('data-url');

    // Update games
    function reloadGameList() {
        setTimeout(function() {
            $.get(refreshUrl, function(html) {
                $gamelist.html(html);
                reloadGameList();
            });
        }, 4500);
    };
    reloadGameList();
});

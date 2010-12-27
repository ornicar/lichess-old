$(function()
{
    if($users = $('#lichess_user div.online_users').orNot()) {
        // Update online users
        var onlineUserUrl = $users.attr('data-reload-url');
        function reloadOnlineUsers() {
            setTimeout(function() {
                $.get(onlineUserUrl, function(html) {
                    $users.find('ul.users').html(html);
                    reloadOnlineUsers();
                });
            }, 3000);
        };
        reloadOnlineUsers();
    }
});

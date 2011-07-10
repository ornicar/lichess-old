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
            }, 2100);
        };
        reloadOnlineUsers();
    }

    if($searchForm = $('form.search_user_form').orNot()) {
        $searchInput = $searchForm.find('input.search_user');
        $searchInput.bind('autocompleteselect', function(e, ui) {
            setTimeout(function() {$searchForm.submit();},10);
        });
        $searchForm.submit(function() {
            location.href = $searchForm.attr('action')+'/'+$searchInput.val();
            return false;
        });
    }
});

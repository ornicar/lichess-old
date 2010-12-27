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

    if($searchForm = $('form.search_user_form').orNot()) {
        $searchInput = $searchForm.find('input.search_user').focus();
        $searchInput.bind('autocompleteselect', function(e, ui) {
            $searchForm.submit();
        });
        $searchForm.submit(function() {
            location.href = $searchForm.attr('action')+$searchInput.val();
            return false;
        });
    }
});

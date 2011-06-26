$(function() {
	if ($gamelist = $('div.game_list').orNot()) {
		refreshUrl = $gamelist.attr('data-url');
		// Update games
		function reloadGameList() {
			setTimeout(function() {
				$.get(refreshUrl, function(html) {
					$gamelist.html(html);
					reloadGameList();
				});
			},
			2100);
		};
		reloadGameList();
	}

    $('div.infinitescroll').each(function() {
        $(this).infinitescroll({
            navSelector: "div.pager",
            nextSelector: "div.pager a.next",
            itemSelector: "div.infinitescroll div.game_row",
            loadingText: ""
        }).find('div.pager').hide();
    });
});

$(function() {
	if ($timeline = $('.timeline_entries').orNot()) {
		refreshUrl = $timeline.attr('data-url');
		(function refresh() {
			setTimeout(function() {
				$.get(refreshUrl, function(html) {
					$timeline.html(html);
					refresh();
				});
			},
			2500);
		})();
	}
});


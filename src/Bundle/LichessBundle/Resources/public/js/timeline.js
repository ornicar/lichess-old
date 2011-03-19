$(function() {
	$timeline = $('.timeline_entries');
    refreshUrl = $timeline.attr('data-url');
    $filters = $filters = $('div.timeline_filters a');
    var activeFilter = $filters.filter('.active').attr('data-filter');
    (function refresh() {
        setTimeout(function() {
            $.get(refreshUrl, function(html) {
                $timeline.html(html);
                filterEntries();
                refresh();
            });
        },
        2500);
    })();
    $filters.click(function() {
        $filters.removeClass('active');
        $(this).addClass('active');
        activeFilter = $(this).attr('data-filter');
        filterEntries();
    });
    filterEntries = function() {
        $timeline.find('> li').show().not('.'+activeFilter).hide();
    }
});

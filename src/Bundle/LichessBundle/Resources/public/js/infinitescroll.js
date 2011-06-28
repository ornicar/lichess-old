$(function() {
    $('div.infinitescroll').each(function() {
        $(this).infinitescroll({
            navSelector: "div.pager",
            nextSelector: "div.pager a.next",
            itemSelector: "div.infinitescroll div.paginated_element",
            loadingText: "",
            donetext: "---"
        }).find('div.pager').hide();
    });
});

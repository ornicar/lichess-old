(function($) {
$(function() {
    SetImagePath ("/bundle/lichess/vendor/pgn4web/merida/64"); // use "" path if images are in the same folder as this javascript file
    SetImageType("png");
});
})(jQuery);

function customFunctionOnPgnGameLoad() {
    var it = 0;
    $('#GameText span.move').each(function() {
        it++;
        if(0 == it%3) $(this).addClass('break');
    });
}

(function($) {
$(function() {
    SetImagePath("/bundle/lichess/vendor/pgn4web/lichess/64"); // use "" path if images are in the same folder as this javascript file
    SetImageType("png");
});
})(jQuery);

function customFunctionOnPgnGameLoad() {
    var it = 0;
    $('#GameText span.move').each(function() {
        it++;
        if(0 == it%3) $(this).addClass('break');
    });

    function rotateBoard(duration) {
        $('#GameBoard').animate({ rotate: '+=180deg' }, duration);
        $('#GameBoard img.pieceImage').animate({ rotate: '-=180deg' }, duration);
    }
    $('div.lichess_goodies a.rotate_board').click(function() {
        rotateBoard(1000);
        return false;
    });
    if($('div.lichess_goodies a.rotate_board').hasClass('black')) {
        rotateBoard(1);
    }
}

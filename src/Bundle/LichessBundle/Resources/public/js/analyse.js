jQuery(function() {
    SetImagePath("/bundles/lichess/vendor/pgn4web/lichess/64"); // use "" path if images are in the same folder as this javascript file
    SetImageType("png");
    setTimeout(function() {
        SetAutoplayDelay(2000);
        SetAutoPlay(true);
    }, 1000);
});

function customFunctionOnPgnGameLoad() {
    var it = 0;
    $('#GameText span.move').each(function() {
        it++;
        if(0 == it%3) $(this).addClass('break');
    });
    $('div.lichess_goodies a.rotate_board').click(function() {
        $('#GameBoard').toggleClass('flip');
        redrawBoardMarks();
        return false;
    });
    redrawBoardMarks();
}

function redrawBoardMarks()
{
    jQuery.displayBoardMarks($('#GameBoard'), !$('#GameBoard').hasClass('flip'));
}

$(function() {
	if ($gamelist = $('div.game_list').orNot()) {
		refreshUrl = $gamelist.attr('data-url');
		// Update games
		function reloadGameList() {
			setTimeout(function() {
				$.get(refreshUrl, function(html) {
					$gamelist.html(html);
                    $('body').trigger('lichess.content_loaded');
					reloadGameList();
				});
			},
			2100);
		};
        reloadGameList();
	}

    function parseFen() {
        $('.parse_fen').each(function() {
            var fen = $(this).data('fen').replace(/\//g, '');
            var x, y, html = '', scolor, pcolor, pclass, c, d, increment;
            var pclasses = {'p':'pawn', 'r':'rook', 'n':'knight', 'b':'bishop', 'q':'queen', 'k':'king'};
            var pregex = /(p|r|n|b|q|k)/;

            if ('white' == $(this).data('color')) {
                x = 8; y = 1;
                increment = function() { y++; if(y > 8) { y = 1; x--; } };
            } else {
                x = 1; y = 8;
                increment = function() { y--; if(y < 1) { y = 8; x++; } };
            }
            function openSquare(x, y) {
                scolor = (x+y)%2 ? 'white' : 'black';
                return '<div class="lmcs '+scolor+'" style="top:'+(24*(8-x))+'px;left:'+(24*(y-1))+'px;">';
            }
            function closeSquare() {
                return '</div>';
            }

            for(var fenIndex in fen) {
                c = fen[fenIndex];
                html += openSquare(x, y);
                if (!isNaN(c)) { // it is numeric
                    html += closeSquare();
                    increment();
                    for (d=1; d<c; d++) {
                        html += openSquare(x, y) + closeSquare();
                        increment();
                    }
                } else {
                    pcolor = pregex.test(c) ? 'black' : 'white';
                    pclass = pclasses[c.toLowerCase()];
                    html += '<div class="lcmp '+pclass+' '+pcolor+'"></div>';
                    html += closeSquare();
                    increment();
                }
            }

            $(this).html(html).removeClass('parse_fen');
            // attempt to free memory
            html = pclasses = increment = pregex = fen = 0;
        });
    }
    parseFen();

    $('body').on('lichess.content_loaded', parseFen);
});

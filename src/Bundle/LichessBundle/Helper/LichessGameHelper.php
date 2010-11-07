<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Routing\Router;
use Bundle\LichessBundle\Document\Game;

class LichessGameHelper extends Helper
{
    protected $generator;
    protected $translator;

    /**
     * Constructor.
     *
     * @param Router $router A Router instance
     * @param Translator $translator A Translator instance
     */
    public function __construct(Router $router, $translator)
    {
        $this->generator = $router->getGenerator();
        $this->translator = $translator;
    }

    public function renderMini(Game $game)
    {
        $player = $game->getCreator();
        $board = $player->getGame()->getBoard();
        $squares = $board->getSquares();

        if ($player->isBlack()) {
            $squares = array_reverse($squares, true);
        }

        $x = $y = 1;

        $html = sprintf('<a href="%s" title="%s" class="mini_board notipsy">',
            $this->generator->generate('lichess_game', array('id' => $game->getId())),
            $this->translator->_('View in full size')
        );

        foreach($squares as $squareKey => $square) {
            $html .= sprintf('<div class="lmcs %s" style="top:%dpx;left:%dpx;">',
                $square->getColor(), 24*(8-$x), 24*($y-1)
            );
            $html .= '<div class="lmcsi"></div>';
            if($piece = $board->getPieceByKey($squareKey)) {
                $html .= sprintf('<div class="lcmp %s %s"></div>',
                    strtolower($piece->getClass()), $piece->getColor()
                );
            }
            $html .= '</div>';
            if (++$x === 9) {
                $x = 1;
                ++$y;
            }
        }
        $html .= '</a>';

        return $html;
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'lichess_game';
    }
}

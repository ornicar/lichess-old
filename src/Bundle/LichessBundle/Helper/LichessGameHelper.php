<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Bundle\LichessBundle\Model\Game;
use Bundle\LichessBundle\Model\Player;
use Bundle\DoctrineUserBundle\Model\User;

class LichessGameHelper extends Helper
{
    protected $container;
    protected $generator;
    protected $translator;

    /**
     * Constructor.
     *
     * @param Router $router A Router instance
     * @param TranslatorInterface $translator A Translator instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->generator = $container->get('router')->getGenerator();
        $this->translator = $container->get('translator');
    }

    public function renderData(Player $player, $possibleMoves, $isOpponentConnected)
    {
        $game = $player->getGame();
        $gameId = $game->getId();
        $color = $player->getColor();
        $opponent = $player->getOpponent();
        $playerFullId = $player->getFullId();
        $data = array(
            'game' => array(
                'id'       => $game->getId(),
                'started'  => $game->getIsStarted(),
                'finished' => $game->getIsFinishedOrAborted(),
                'clock'    => $game->hasClock(),
                'player'   => $game->getTurnPlayer()->getColor(),
                'turns'    => $game->getTurns()
            ),
            'player' => array(
                'color'     => $player->getColor(),
                'version'   => $player->getStack()->getVersion(),
                'spectator' => false
            ),
            'opponent' => array(
                'color'     => $opponent->getColor(),
                'ai'        => $opponent->getIsAi(),
                'connected' => $isOpponentConnected
            ),
            'url' => array(
                'sync'      => $this->generator->generate('lichess_sync', array('id' => $gameId, 'color' => $color, 'version' => 9999999, 'playerFullId' => $playerFullId)),
                'table'     => $this->generator->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => $playerFullId)),
                'opponent'  => $this->generator->generate('lichess_opponent', array('id' => $gameId, 'color' => $color, 'playerFullId' => $playerFullId)),
                'move'      => $this->generator->generate('lichess_move', array('id' => $playerFullId, 'version' => 9999999)),
                'say'       => $this->generator->generate('lichess_say', array('id' => $playerFullId, 'version' => 9999999)),
                'ai_level'  => $opponent->getIsAi() ? $this->generator->generate('lichess_ai_level', array('id' => $playerFullId)) : null,
                'outoftime' => $game->hasClock() ? $this->generator->generate('lichess_outoftime', array('id' => $playerFullId, 'version' => 9999999)) : null
            ),
            'i18n' => array(
                'Game Over'            => $this->translator->trans('Game Over'),
                'Waiting for opponent' => $this->translator->trans('Waiting for opponent'),
                'Your turn'            => $this->translator->trans('Your turn'),
            ),
            'possible_moves'  => $possibleMoves,
            'sync_delay'      => $this->container->getParameter('lichess.synchronizer.delay') * 1000,
            'animation_delay' => $this->container->getParameter('lichess.animation.delay'),
            'debug'           => $this->container->getParameter('kernel.debug')
        );

        return sprintf('<script type="text/javascript">var lichess_data = %s;</script>', json_encode($data));
    }

    public function renderWatchData(Player $player, $possibleMoves)
    {
        $game = $player->getGame();
        $gameId = $game->getId();
        $color = $player->getColor();
        $opponent = $player->getOpponent();
        $data = array(
            'game' => array(
                'id'       => $game->getId(),
                'started'  => $game->getIsStarted(),
                'finished' => $game->getIsFinishedOrAborted(),
                'clock'    => $game->hasClock(),
                'player'   => $game->getTurnPlayer()->getColor(),
                'turns'    => $game->getTurns()
            ),
            'player' => array(
                'color'     => $player->getColor(),
                'version'   => $player->getStack()->getVersion(),
                'spectator' => true
            ),
            'opponent' => array(
                'color'     => $opponent->getColor(),
                'ai'        => $opponent->getIsAi(),
                'connected' => true
            ),
            'sync_delay'      => $this->container->getParameter('lichess.synchronizer.delay') * 1000,
            'animation_delay' => $this->container->getParameter('lichess.animation.delay'),
            'url' => array(
                'sync'     => $this->generator->generate('lichess_sync', array('id' => $gameId, 'color' => $color, 'version' => 9999999, 'playerFullId' => '')).'/',
                'table'    => $this->generator->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => '')).'/',
                'opponent' => $this->generator->generate('lichess_opponent', array('id' => $gameId, 'color' => $color, 'playerFullId' => '')).'/'
            ),
            'i18n' => array(
                'Game Over'            => $this->translator->trans('Game Over'),
                'Waiting for opponent' => $this->translator->trans('Waiting for opponent'),
                'Your turn'            => $this->translator->trans('Your turn')
            ),
            'possible_moves' => $possibleMoves
        );

        return sprintf('<script type="text/javascript">var lichess_data = %s;</script>', json_encode($data));
    }

    public function renderMini(Game $game, User $user = null)
    {
        $player = $game->getPlayerByUserOrCreator($user);
        $board = $player->getGame()->getBoard();
        $squares = $board->getSquares();

        if ($player->isBlack()) {
            $squares = array_reverse($squares, true);
        }

        $x = $y = 1;

        $html = sprintf('<a href="%s" title="%s" class="mini_board notipsy">',
            $this->generator->generate('lichess_game', array('id' => $game->getId())),
            $this->translator->trans('View in full size')
        );

        foreach($squares as $squareKey => $square) {
            $html .= sprintf('<div class="lmcs %s" style="top:%dpx;left:%dpx;">',
                $square->getColor(), 24*(8-$x), 24*($y-1)
            );
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

    public function renderBoard(Player $player, $checkSquareKey)
    {
        $board = $player->getGame()->getBoard();
        $squares = $board->getSquares();
        $isGameStarted = $player->getGame()->getIsStarted();
        if ($player->isBlack()) {
            $squares = array_reverse($squares, true);
        }
        $x = $y = 1;
        $html = '<div class="lichess_board">';
        foreach($squares as $squareKey => $square) {
            $html .= sprintf('<div class="lcs %s%s" id="%s" style="top:%dpx;left:%dpx;">',
                $square->getColor(), $checkSquareKey === $squareKey ? ' check' : '', $squareKey, 64*(8-$x), 64*($y-1)
            );
            $html .= '<div class="lcsi"></div>';
            if($piece = $board->getPieceByKey($squareKey)) {
                if($isGameStarted || $piece->getPlayer() === $player) {
                    $html .= sprintf('<div class="lichess_piece %s %s"></div>',
                        strtolower($piece->getClass()), $piece->getColor()
                    );
                }
            }
            $html .= '</div>';
            if (++$x === 9) {
                $x = 1;
                ++$y;
            }
        }
        $html .= '</div>';

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

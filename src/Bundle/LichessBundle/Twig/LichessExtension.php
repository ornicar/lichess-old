<?php

namespace Bundle\LichessBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Application\UserBundle\Document\User;
use Bundle\LichessBundle\Document\Player;
use Bundle\LichessBundle\Document\Game;
use Bundle\LichessBundle\Document\History;
use Twig_Extension;
use Twig_Function_Method;
use Twig_Filter_Method;
use DateTime;
use IntlDateFormatter;

class LichessExtension extends Twig_Extension
{
    protected $container;
    protected $dateFormatter;

    /**
     * Constructor.
     *
     * @param Router $router A Router instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        $mappings = array(
            'lichess_link_player'          => 'linkPlayer',
            'lichess_link_user'            => 'linkUser',
            'lichess_elo_chart_url'        => 'eloChartUrl',
            'lichess_choices'              => 'choices',
            'lichess_game_data'            => 'renderGameData',
            'lichess_game_watch_data'      => 'renderGameWatchData',
            'lichess_game_board'           => 'renderGameBoard',
            'lichess_game_mini'            => 'renderGameMini',
            'lichess_locale_name'          => 'getLocaleName',
            'lichess_session'              => 'getSession',
            'lichess_nb_connected_players' => 'getNbConnectedPlayers',
            'lichess_load_average'         => 'getLoadAverage',
            'lichess_user_text'            => 'userText',
            'lichess_shorten'              => 'shorten',
            'lichess_current_url'          => 'getCurrentUrl',
            'lichess_room_message'         => 'roomMessage',
            'lichess_room_messages'        => 'roomMessages',
            'lichess_debug_assets'         => 'debugAssets',
            'lichess_date'                 => 'formatDate'
        );

        $functions = array();
        foreach($mappings as $twigFunction => $method) {
            $functions[$twigFunction] = new Twig_Function_Method($this, $method, array('is_safe' => array('html')));
        }

        return $functions;
    }

    /**
     * Returns a list of filters to add to the existing list.
     *
     * @return array An array of filters
     */
    public function getFilters()
    {
        $filters = array(
            // formatting filters
            'date'    => new Twig_Filter_Method($this, 'formatDate'),
        );

        return $filters;
    }

    public function formatDate($date, $format = null)
    {
        if (!$date instanceof DateTime) {
            $date = new DateTime((ctype_digit($date) ? '@' : '').$date);
        }
        if ($format) {
            return $date->format($format);
        }
        if (null === $this->dateFormatter) {
            $this->dateFormatter = new IntlDateFormatter(
                $this->container->get('session')->getLocale(),
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );
        }

        // for compatibility with PHP 5.3.3
        $date = $date->getTimestamp();

        return $this->dateFormatter->format($date);
    }

    public function choices($choices)
    {
        $translated = array();
        $translator = $this->getTranslator();
        foreach($choices as $choice) {
            $translated[] = $translator->trans($choice);
        }

        return implode(', ', $translated);
    }

    public function linkPlayer(Player $player, $class = null)
    {
        if(!$user = $player->getUser()) {
            return $this->escape($player->getUsernameWithElo());
        }

        $url = $this->getRouterGenerator()->generate('fos_user_user_show', array('username' => $user->getUsername()));

        $username = $player->getUsernameWithElo();
        if($eloDiff = $player->getEloDiff()) {
            $username = sprintf('%s (%s)', $username, $eloDiff < 0 ? $eloDiff : '+'.$eloDiff);
        }
        return sprintf('<a class="user_link%s" href="%s"%s>%s</a>', $user->getIsOnline() ? ' online' : '', $url, null === $class ? '' : ' class="'.$class.'"', $username);
    }

    public function linkUser(User $user, $class = null)
    {
        $url = $this->getRouterGenerator()->generate('fos_user_user_show', array('username' => $user->getUsername()));

        return sprintf('<a class="user_link%s" href="%s"%s>%s</a>', $user->getIsOnline() ? ' online' : '', $url, null === $class ? '' : ' class="'.$class.'"', $user->getUsernameWithElo());
    }

    public function eloChartUrl(History $history, $size)
    {
        $elos = $history->getEloByTs();
        $min = 20*round((min($elos) - 10)/20);
        $max = 20*round((max($elos) + 10)/20);
        $dots = array_map(function($e) use($min, $max) { return round(($e - $min) / ($max - $min) * 100); }, $elos);
        $yStep = ($max - $min) / 4 ;
        return sprintf('%scht=lc&chs=%s&chd=t:%s&chxt=y&chxr=%s&chf=%s',
            'http://chart.apis.google.com/chart?',
            $size,
            implode(',', $dots),
            implode(',', array(0, $min, $max, $yStep)),
            'bg,s,65432100' // Transparency
        );
    }

    protected function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public function renderGameData(Player $player, $possibleMoves, $isOpponentConnected)
    {
        $game = $player->getGame();
        $gameId = $game->getId();
        $color = $player->getColor();
        $opponent = $player->getOpponent();
        $playerFullId = $player->getFullId();
        $generator = $this->getRouterGenerator();
        $translator = $this->getTranslator();

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
                'sync'      => $generator->generate('lichess_sync', array('id' => $gameId, 'color' => $color, 'version' => 9999999, 'playerFullId' => $playerFullId)),
                'table'     => $generator->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => $playerFullId)),
                'opponent'  => $generator->generate('lichess_opponent', array('id' => $gameId, 'color' => $color, 'playerFullId' => $playerFullId)),
                'move'      => $generator->generate('lichess_move', array('id' => $playerFullId, 'version' => 9999999)),
                'say'       => $generator->generate('lichess_say', array('id' => $playerFullId, 'version' => 9999999)),
                'ai_level'  => $opponent->getIsAi() ? $generator->generate('lichess_ai_level', array('id' => $playerFullId)) : null,
                'outoftime' => $game->hasClock() ? $generator->generate('lichess_outoftime', array('id' => $playerFullId, 'version' => 9999999)) : null
            ),
            'i18n' => array(
                'Game Over'            => $translator->trans('Game Over'),
                'Waiting for opponent' => $translator->trans('Waiting for opponent'),
                'Your turn'            => $translator->trans('Your turn'),
            ),
            'possible_moves'  => $possibleMoves,
            'sync_delay'      => $this->container->getParameter('lichess.synchronizer.delay') * 1000,
            'animation_delay' => $this->container->getParameter('lichess.animation.delay'),
            'debug'           => $this->container->getParameter('kernel.debug')
        );

        return sprintf('<script type="text/javascript">var lichess_data = %s;</script>', json_encode($data));
    }

    public function renderGameWatchData(Player $player, $possibleMoves)
    {
        $game = $player->getGame();
        $gameId = $game->getId();
        $color = $player->getColor();
        $opponent = $player->getOpponent();
        $generator = $this->getRouterGenerator();
        $translator = $this->getTranslator();

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
                'sync'     => $generator->generate('lichess_sync', array('id' => $gameId, 'color' => $color, 'version' => 9999999, 'playerFullId' => '')).'/',
                'table'    => $generator->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => '')).'/',
                'opponent' => $generator->generate('lichess_opponent', array('id' => $gameId, 'color' => $color, 'playerFullId' => '')).'/'
            ),
            'i18n' => array(
                'Game Over'            => $translator->trans('Game Over'),
                'Waiting for opponent' => $translator->trans('Waiting for opponent'),
                'Your turn'            => $translator->trans('Your turn')
            ),
            'possible_moves' => $possibleMoves
        );

        return sprintf('<script type="text/javascript">var lichess_data = %s;</script>', json_encode($data));
    }

    public function debugAssets()
    {
        return $this->container->getParameter('lichess.debug_assets');
    }

    public function renderGameMini(Game $game, User $user = null)
    {
        $player = $game->getPlayerByUserOrCreator($user);
        $board = $player->getGame()->getBoard();
        $squares = $board->getSquares();
        $generator = $this->getRouterGenerator();
        $translator = $this->getTranslator();

        if ($player->isBlack()) {
            $squares = array_reverse($squares, true);
        }

        $x = $y = 1;

        $html = sprintf('<a href="%s" title="%s" class="mini_board notipsy">',
            $generator->generate('lichess_game', array('id' => $game->getId(), 'color' => $player->getColor())),
            $translator->trans('View in full size')
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

    public function renderGameBoard(Player $player, $checkSquareKey)
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

    public function getNbConnectedPlayers()
    {
        return $this->container->get('lichess.synchronizer')->getNbConnectedPlayers();
    }

    public function getLoadAverage()
    {
        return round($this->container->get('lichess.hardware')->getLoadAverage()).'%';
    }

    protected function autoLink($text)
    {
        return preg_replace_callback('~
            (                       # leading text
                <\w+.*?>|             #   leading HTML tag, or
                [^=!:\'"/]|           #   leading punctuation, or
                ^                     #   beginning of line
            )
            (
                (?:https?://)|        # protocol spec, or
                (?:www\.)             # www.*
            )
            (
                [-\w]+                   # subdomain or domain
                (?:\.[-\w]+)*            # remaining subdomains or domain
                (?::\d+)?                # port
                (?:/(?:(?:[\~\w\+%-\@]|(?:[,.;:][^\s$]))+)?)* # path
                (?:\?[\w\+%&=.;-]+)?     # query string
                (?:\#[\w\-]*)?           # trailing anchor
            )
            ([[:punct:]]|\s|<|$)    # trailing text
            ~x',
            function($matches)
            {
                if (preg_match("/<a\s/i", $matches[1]))
                {
                    return $matches[0];
                }
                else
                {
                    return $matches[1].'<a href="'.($matches[2] == 'www.' ? 'http://www.' : $matches[2]).$matches[3].'" target="_blank">'.$matches[2].$matches[3].'</a>'.$matches[4];
                }
            },
            $text
        );
    }

    public function userText($text)
    {
        return nl2br($this->autoLink($this->escape($text)));
    }

    public function shorten($text, $length = 140)
    {
        return mb_substr(str_replace("\n", ' ', $this->escape($text)), 0, 140);
    }

    public function getCurrentUrl()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'http://test/';
    }

    public function roomMessage(array $message)
    {
        if('system' === $message[0]) {
            $message[1] = $this->container->get('translator')->trans($message[1]);
        }

        return sprintf('<li class="%s">%s</li>', $message[0], $this->userText($message[1]));
    }

    public function roomMessages(array $messages)
    {
        $html = '';
        foreach($messages as $message) {
            $html .= $this->roomMessage($message);
        }

        return $html;
    }
    public function getSession($key, $default = null)
    {
        return $this->container->get('session')->get($key, $default);
    }

    public function getLocaleName()
    {
        $locale = $this->container->get('session')->getLocale();

        return $this->container->get('lichess.translation.manager')->getAvailableLanguageName($locale);
    }

    public function getLocales()
    {
        return $this->container->get('lichess.translation.manager')->getAvailableLanguages();
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'lichess';
    }

    protected function getRouterGenerator()
    {
        return $this->container->get('router')->getGenerator();
    }

    protected function getTranslator()
    {
        return $this->container->get('translator');
    }
}

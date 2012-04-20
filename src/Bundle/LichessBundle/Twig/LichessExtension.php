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
use Bundle\LichessBundle\Notation\Forsyth;

class LichessExtension extends Twig_Extension
{
    protected $container;
    protected $lilaBasePath;
    protected $dateFormatter;

    /**
     * Constructor.
     *
     * @param Router $router A Router instance
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->lilaBasePath = $container->getParameter('lichess.sync.path');
    }

    /**
     * Returns a list of global functions to add to the existing list.
     *
     * @return array An array of global functions
     */
    public function getFunctions()
    {
        $mappings = array(
            'lichess_link_player'       => 'linkPlayer',
            'lichess_link_user'         => 'linkUser',
            'lichess_choices'           => 'choices',
            'lichess_game_data'         => 'renderGameData',
            'lichess_game_watch_data'   => 'renderGameWatchData',
            'lichess_game_board'        => 'renderGameBoard',
            'lichess_game_fen'          => 'renderGameFen',
            'lichess_user_text'         => 'userText',
            'lichess_shorten'           => 'shorten',
            'lichess_current_url'       => 'getCurrentUrl',
            'lichess_debug_assets'      => 'debugAssets',
            'lichess_date'              => 'formatDate',
            'lila_path'                 => 'lilaPath',
            'lichess_user_setting'      => 'setting'
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
            'lichess_remove_language_prefix'    => new Twig_Filter_Method($this, 'removeLanguagePrefix'),
        );

        return $filters;
    }

    public function removeLanguagePrefix($url)
    {
        return preg_replace('#://\w{2,3}\.#', '://', $url);
    }

    public function setting($name, $default)
    {
        return $this->container->get('lichess_user.settings')->get($name, $default);
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

    public function linkPlayer(Player $player, $class = null, $withElo = true)
    {
        if(!$user = $player->getUser()) {
            return $this->escape($player->getUsernameWithElo());
        }

        $url = $this->getUrlGenerator()->generate('fos_user_user_show', array('username' => $user->getUsername()));

        $username = $withElo ? $player->getUsernameWithElo() : $player->getUsername();
        if($eloDiff = $player->getEloDiff()) {
            $username = sprintf('%s (%s)', $username, $eloDiff < 0 ? $eloDiff : '+'.$eloDiff);
        }
        return sprintf('<a class="user_link%s" href="%s"%s>%s</a>', $user->getIsOnline() ? ' online' : '', $url, null === $class ? '' : ' class="'.$class.'"', $username);
    }

    public function linkUser(User $user, $class = null, $withElo = false)
    {
        $username = $withElo ? $user->getUsernameWithElo() : $user->getUsername();
        $url = $this->getUrlGenerator()->generate('fos_user_user_show', array('username' => $user->getUsername()));

        return sprintf('<a class="user_link%s%s" href="%s">%s</a>', $user->getIsOnline() ? ' online' : '', null === $class ? '' : ' '.$class, $url, $username);
    }

    public function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public function lilaPath($path)
    {
        return $this->lilaBasePath . "/" . $path;
    }

    public function renderGameData(Player $player, $version, $possibleMoves)
    {
        $game         = $player->getGame();
        $gameId       = $game->getId();
        $color        = $player->getColor();
        $opponent     = $player->getOpponent();
        $playerFullId = $player->getFullId();
        $generator    = $this->getUrlGenerator();
        $translator   = $this->getTranslator();

        $data = array(
            'game' => array(
                'id'        => $game->getId(),
                'started'   => $game->getIsStarted(),
                'finished'  => $game->getIsFinishedOrAborted(),
                'clock'     => $game->hasClock(),
                'player'    => $game->getTurnPlayer()->getColor(),
                'turns'     => $game->getTurns(),
                'last_move' => $game->getLastMove()
            ),
            'player' => array(
                'id'        => $player->getId(),
                'color'     => $player->getColor(),
                'version'   => $version,
                'spectator' => false
            ),
            'opponent' => array(
                'color'  => $opponent->getColor(),
                'ai'     => $opponent->getIsAi(),
            ),
            'url' => array(
                'table'     => $generator->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => $playerFullId))
            ),
            'possible_moves'  => $possibleMoves,
            'animation_delay' => round($this->container->getParameter('lichess.animation.delay') * 1000 * self::animationDelayFactor($game->estimateTotalTime())),
            'debug'           => $this->container->getParameter('kernel.debug')
        );

        return sprintf('<script type="text/javascript">var lichess_data = %s;</script>', json_encode($data));
    }

    public static function animationDelayFactor($time)
    {
        return max(0, min(1.2, (($time - 60) / 60) * 0.2));
    }

    public function renderGameWatchData(Player $player, $version, $possibleMoves)
    {
        $game       = $player->getGame();
        $gameId     = $game->getId();
        $color      = $player->getColor();
        $opponent   = $player->getOpponent();
        $generator  = $this->getUrlGenerator();
        $translator = $this->getTranslator();

        $data = array(
            'game' => array(
                'id'        => $game->getId(),
                'started'   => $game->getIsStarted(),
                'finished'  => $game->getIsFinishedOrAborted(),
                'clock'     => $game->hasClock(),
                'player'    => $game->getTurnPlayer()->getColor(),
                'turns'     => $game->getTurns(),
                'last_move' => $game->getLastMove()
            ),
            'player' => array(
                'color'     => $player->getColor(),
                'version'   => $version,
                'spectator' => true,
                'unique_id' => uniqid()
            ),
            'opponent' => array(
                'color'  => $opponent->getColor(),
                'ai'     => $opponent->getIsAi(),
                'active' => true
            ),
            'url' => array(
                'table'    => $generator->generate('lichess_table', array('id' => $gameId, 'color' => $color, 'playerFullId' => ''))
            ),
            'possible_moves'    => $possibleMoves,
            'animation_delay' => round($this->container->getParameter('lichess.animation.delay') * 1000 * self::animationDelayFactor($game->estimateTotalTime()))
        );

        return sprintf('<script type="text/javascript">var lichess_data = %s;</script>', json_encode($data));
    }

    public function debugAssets()
    {
        return $this->container->getParameter('lichess.debug_assets');
    }

    public function renderGameFen(Game $game, User $user = null)
    {
        $player     = $game->getPlayerByUserOrCreator($user);
        $authUser   = $this->container->get('security.context')->getToken()->getUser();
        if ($authUser instanceof User && ($authPlayer = $game->getPlayerByUser($authUser))) {
            $gameUrl = $this->getUrlGenerator()->generate('lichess_player', array('id' => $authPlayer->getFullId()));
        } else {
            $gameUrl = $this->getUrlGenerator()->generate('lichess_game', array('id' => $game->getId(), 'color' => $player->getColor()));
        }

        return sprintf('<a href="%s" title="%s" class="mini_board parse_fen" data-color="%s" data-fen="%s"></a>',
            $gameUrl,
            $this->getTranslator()->trans('View in full size'),
            $player->getColor(),
            Forsyth::export($game)
        );
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
        $html = '<div class="lichess_board grey">';
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
        return mb_substr(str_replace("\n", ' ', $this->escape($text)), 0, $length, 'utf-8');
    }

    public function getCurrentUrl()
    {
        return isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'http://test/';
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

    protected function getUrlGenerator()
    {
        return $this->container->get('router');
    }

    protected function getTranslator()
    {
        return $this->container->get('translator');
    }
}

<?php

namespace Bundle\LichessBundle\Helper;

use Symfony\Component\Templating\Helper\Helper;
use Symfony\Component\Routing\Router;
use Application\DoctrineUserBundle\Document\User;
use Bundle\LichessBundle\Document\Player;

class LichessUserHelper extends Helper
{
    protected $generator;

    /**
     * Constructor.
     *
     * @param Router $router A Router instance
     */
    public function __construct(Router $router)
    {
        $this->generator = $router->getGenerator();
    }

    public function link($player, $class = null)
    {
        if($player instanceof Player) {
            if(!$user = $player->getUser()) {
                return $this->escape($player->getUsernameWithElo());
            }
        } elseif($player instanceof User) {
            $user = $player;
        } else {
            throw new \InvalidArgumentException($player.' is not a user nor a player');
        }

        $username = $this->escape($user->getUsernameWithElo());
        $url = $this->generator->generate('doctrine_user_user_show', array('username' => $user->getUsername()));

        return sprintf('<a href="%s"%s>%s</a>', $url, null === $class ? '' : ' class="'.$class.'"', $username);
    }

    public function eloChartUrl(User $user, $size)
    {
        $elos = $user->getEloHistory();
        $min = 100*round((min($elos) - 100)/100);
        $max = 100*round((max($elos) + 50)/100);
        $dots = array_map(function($e) use($min, $max) { return round(($e - $min) / ($max - $min) * 100); }, $elos);
        $yStep = ($max - $min) / 4 ;
        return sprintf('%scht=lc&chs=%s&chd=t:%s&chxt=y&chxr=%s&chf=%s',
            'http://chart.apis.google.com/chart?',
            $size,
            implode(',', $dots),
            implode(',', array(0, $min, $max, $yStep)),
            'bg,s,65432100'
        );
    }

    protected function escape($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns the canonical name of this helper.
     *
     * @return string The canonical name
     */
    public function getName()
    {
        return 'lichess_user';
    }
}

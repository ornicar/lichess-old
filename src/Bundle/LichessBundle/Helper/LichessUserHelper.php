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
        $username = $this->escape($player->getUsernameWithElo());

        if($player instanceof Player) {
            if(!$user = $player->getUser()) {
                return $username;
            }
        } elseif($player instanceof User) {
            $user = $player;
        } else {
            throw new \InvalidArgumentException($player.' is not a user nor a player');
        }

        $url = $this->generator->generate('doctrine_user_user_show', array('username' => $user->getUsername()));

        return sprintf('<a href="%s"%s>%s</a>', $url, null === $class ? '' : ' class="'.$class.'"', $username);
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

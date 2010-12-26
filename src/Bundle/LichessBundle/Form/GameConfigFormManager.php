<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Security\SecurityContext;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Validator\Validator;

class GameConfigFormManager
{
    protected $security;
    protected $session;
    protected $validator;

    public function __construct(SecurityContext $security, Session $session, Validator $validator)
    {
        $this->security  = $security;
        $this->session   = $session;
        $this->validator = $validator;
    }

    public function createFriendForm()
    {
        $isAuthenticated = $this->security->vote('IS_AUTHENTICATED_FULLY');
        $config = new FriendGameConfig();
        $config->fromArray($this->session->get('lichess.game_config.friend', array()));
        if(!$isAuthenticated) {
            $config->mode = 0;
        }
        $formClass = 'Bundle\LichessBundle\Form\\'.($isAuthenticated ? 'FriendWithModeGameConfigForm' : 'FriendGameConfigForm');

        return new $formClass('config', $config, $this->validator);
    }

    public function createAnybodyForm()
    {
        $isAuthenticated = $this->security->vote('IS_AUTHENTICATED_FULLY');
        $config = new AnybodyGameConfig();
        $config->fromArray($this->session->get('lichess.game_config.anybody', array()));
        if(!$isAuthenticated) {
            $config->modes = array(0);
        }
        $formClass = 'Bundle\LichessBundle\Form\\'.($isAuthenticated ? 'AnybodyWithModesGameConfigForm' : 'AnybodyGameConfigForm');

        return new $formClass('config', $config, $this->validator);
    }

    public function createAiForm()
    {
        $config = new AiGameConfig();
        $config->fromArray($this->session->get('lichess.game_config.ai', array()));

        return new AiGameConfigForm('config', $config, $this->validator);
    }
}

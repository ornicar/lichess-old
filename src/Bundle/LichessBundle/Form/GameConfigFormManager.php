<?php

namespace Bundle\LichessBundle\Form;

use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Session;
use Symfony\Component\Form\FormContext;

class GameConfigFormManager
{
    protected $security;
    protected $session;
    protected $formContext;

    public function __construct(SecurityContext $security, Session $session, FormContext $formContext)
    {
        $this->security  = $security;
        $this->session   = $session;
        $this->formContext = $formContext;
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

        return $this->createForm($formClass, $config);
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

        return $this->createForm($formClass, $config);
    }

    public function createAiForm()
    {
        $config = new AiGameConfig();
        $config->fromArray($this->session->get('lichess.game_config.ai', array()));

        return $this->createForm('Bundle\LichessBundle\Form\AiGameConfigForm', $config);
    }

    protected function createForm($class, $config)
    {
        $form = call_user_func_array(array($class, 'create'), array($this->formContext, 'config'));

        $form->setVariantChoices($config->getVariantChoices());
        $form->setTimeChoices($config->getTimeChoices());
        $form->setIncrementChoices($config->getIncrementChoices());

        $form->setData($config);

        if ($form instanceof GameConfigFormWithModeInterface) {
            $form->addModeChoices($config->getModeChoices());
        }

        return $form;
    }
}

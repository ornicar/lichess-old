<?php

namespace Lichess\OpeningBundle\Form;

use Lichess\OpeningBundle\Config\GameConfig;
use Lichess\OpeningBundle\Config\AiGameConfig;
use Lichess\OpeningBundle\Config\Persistence;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormFactory;

class GameConfigFormManager
{
    protected $security;
    protected $configPersistence;
    protected $formFactory;

    public function __construct(SecurityContext $security, Persistence $configPersistence, FormFactory $formFactory)
    {
        $this->security            = $security;
        $this->configPersistence   = $configPersistence;
        $this->formFactory         = $formFactory;
    }

    public function createFriendForm()
    {
        return $this->createGenericForm('friend', false);
    }

    public function createHookForm()
    {
        return $this->createGenericForm('hook', true);
    }

    protected function createGenericForm($persistedName, $rangeable = false)
    {
        $isAuthenticated = $this->security->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $config = new GameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor($persistedName));

        return $this->formFactory->create(new GameConfigFormType($isAuthenticated, true, $rangeable), $config);
    }

    public function createAiForm()
    {
        $config = new AiGameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor('ai'));

        return $this->formFactory->create(new AiGameConfigFormType(), $config);
    }
}

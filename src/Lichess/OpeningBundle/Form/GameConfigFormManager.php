<?php

namespace Lichess\OpeningBundle\Form;

use Lichess\OpeningBundle\Config\FriendGameConfig;
use Lichess\OpeningBundle\Config\AnybodyGameConfig;
use Lichess\OpeningBundle\Config\AiGameConfig;
use Lichess\OpeningBundle\Config\Persistence;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\FormFactory;
use Lichess\HookBundle\Config\HookGameConfig;

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
        $isAuthenticated = $this->security->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $config = new FriendGameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor('friend'));
        if(!$isAuthenticated) {
            $config->setMode(0);
        }
        $formClass = 'Lichess\OpeningBundle\Form\\'.($isAuthenticated ? 'FriendWithModeGameConfigFormType' : 'FriendGameConfigFormType');

        return $this->createForm($formClass, $config);
    }

    public function createHookForm()
    {
        $isAuthenticated = $this->security->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $config = new HookGameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor('hook'));
        if(!$isAuthenticated) {
            $config->setModes(array(0));
        }
        $formClass = 'Lichess\HookBundle\Form\\'.($isAuthenticated ? 'HookWithModeGameConfigFormType' : 'HookGameConfigFormType');

        return $this->createForm($formClass, $config);
    }

    public function createAiForm()
    {
        $config = new AiGameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor('ai'));

        return $this->createForm('Lichess\OpeningBundle\Form\AiGameConfigFormType', $config);
    }

    protected function createForm($class, $config)
    {
        $formType = new $class($config);

        return $this->formFactory->createNamed($formType, 'config', $config);
    }
}

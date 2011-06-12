<?php

namespace Bundle\LichessBundle\Form;

use Bundle\LichessBundle\Config\FriendGameConfig;
use Bundle\LichessBundle\Config\AnybodyGameConfig;
use Bundle\LichessBundle\Config\AiGameConfig;
use Bundle\LichessBundle\Config\Persistence;
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
        $isAuthenticated = $this->security->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $config = new FriendGameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor('friend'));
        if(!$isAuthenticated) {
            $config->setMode(0);
        }
        $formClass = 'Bundle\LichessBundle\Form\\'.($isAuthenticated ? 'FriendWithModeGameConfigFormType' : 'FriendGameConfigFormType');

        return $this->createForm($formClass, $config);
    }

    public function createAnybodyForm()
    {
        $isAuthenticated = $this->security->isGranted('IS_AUTHENTICATED_REMEMBERED');
        $config = new AnybodyGameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor('anybody'));
        if(!$isAuthenticated) {
            $config->setModes(array(0));
        }
        $formClass = 'Bundle\LichessBundle\Form\\'.($isAuthenticated ? 'AnybodyWithModesGameConfigFormType' : 'AnybodyGameConfigFormType');

        return $this->createForm($formClass, $config);
    }

    public function createAiForm()
    {
        $config = new AiGameConfig();
        $config->fromArray($this->configPersistence->loadConfigFor('ai'));

        return $this->createForm('Bundle\LichessBundle\Form\AiGameConfigFormType', $config);
    }

    protected function createForm($class, $config)
    {
        $formType = new $class($config);

        return $this->formFactory->createNamed($formType, 'config', $config);
    }
}

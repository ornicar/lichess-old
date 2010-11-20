<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Translation\Manager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Form\TextareaField;

class TranslationForm extends Form
{
    protected $translationManager;

    public function __construct($name, $data, $validator, Manager $translationManager)
    {
        $this->translationManager = $translationManager;

        parent::__construct($name, $data, $validator);
    }

    public function configure()
    {
        $languages = $this->translationManager->getLanguages();
        unset($languages['en']);
        array_unshift($languages, 'Choose a language');
        $this->add(new ChoiceField('code', array('choices' => $languages)));
        $this->add(new TextareaField('yaml'));
        $this->add(new TextField('author'));
        $this->add(new TextField('comment'));
    }
}

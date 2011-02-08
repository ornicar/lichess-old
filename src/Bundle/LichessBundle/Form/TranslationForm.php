<?php

namespace Bundle\LichessBundle\Form;
use Bundle\LichessBundle\Translation\Manager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\TextField;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Form\CollectionField;

class TranslationForm extends Form
{
    public function __construct($title, array $options)
    {
        $this->addOption('translation_manager');

        parent::__construct($title, $options);
    }

    public function configure()
    {
        $languages = $this->getOption('translation_manager')->getLanguages();
        unset($languages['en']);
        array_unshift($languages, 'Choose a language');
        $this->add(new ChoiceField('code', array('choices' => $languages)));
        $this->add(new TextField('author'));
        $this->add(new TextField('comment'));
    }

    public function setData($data)
    {
        parent::setData($data);

        $translations = new CollectionField(new TextField('messagesValues'));
        $this->add($translations);
    }
}

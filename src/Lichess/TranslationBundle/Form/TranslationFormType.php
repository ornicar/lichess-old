<?php

namespace Lichess\TranslationBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Lichess\TranslationBundle\TranslationManager;

class TranslationFormType extends AbstractType
{
    protected $languages;

    public function __construct(TranslationManager $translationManager)
    {
        $this->languages = $translationManager->getLanguages();
    }

    public function buildForm(FormBuilder $builder, array $options)
    {
        $languages = $this->languages;
        unset($languages['en']);
        array_unshift($languages, 'Choose a language');

        $builder
            ->add('code', 'choice', array('choices' => $languages))
            ->add('author', 'text')
            ->add('comment', 'text')
            ->add('messagesValues', 'collection', array('type' => 'text'))
        ;
    }
}

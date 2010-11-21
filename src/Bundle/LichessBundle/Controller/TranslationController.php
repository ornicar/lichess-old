<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Form\TextareaField;
use Symfony\Component\Form\TextField;
use Bundle\LichessBundle\Document\Translation;
use Symfony\Component\Finder\Finder;

class TranslationController extends Controller
{
    public function indexAction()
    {
        $form = $this->get('lichess.form.translation');

        return $this->render('LichessBundle:Translation:index.twig', array(
            'form' => $form,
            'locale' => '__'
        ));
    }

    public function localeAction($locale)
    {
        $manager = $this->get('lichess.translation.manager');
        $translation = new Translation();
        $translation->setCode($locale);
        $translation->setName($manager->getLanguageName($locale));
        try {
            $translation->setMessages($manager->getMessagesWithReferenceKeys($locale));
        }
        catch(\InvalidArgumentException $e) {
            $translation->setMessages($manager->getEmptyMessages());
        }
        $form = $this->get('lichess.form.translation');
        $form->setData($translation);

        if ($this->get('request')->getMethod() == 'POST')
        {
            $form->bind($this->get('request')->request->get($form->getName()));
            if($form->isValid()) {
                $this->get('lichess.object_manager')->persist($translation);
                $this->get('lichess.object_manager')->flush();
                $this->get('session')->setFlash('notice', 'Your translation has been submitted, thanks!');

                return $this->redirect($this->generateUrl('lichess_translate_locale', array('locale' => $locale)));
            } else {
                $error = $translation->getYamlError();
            }
        }

        return $this->render('LichessBundle:Translation:locale.twig', array(
            'form' => $form,
            'locale' => $locale,
            'error' => isset($error) ? $error : false
        ));
    }

    public function listAction()
    {
        $translations = $this->get('lichess.object_manager')->getRepository('LichessBundle:Translation')->createQuery()
            ->sort('createdAt', 'DESC');

        return $this->render('LichessBundle:Translation:list.twig', array('translations' => $translations));
    }
}

<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\ChoiceField;
use Symfony\Component\Form\TextareaField;
use Symfony\Component\Form\TextField;
use Symfony\Component\Finder\Finder;

class TranslationController extends Controller
{
    public function incompleteAction($locale)
    {
        $status = $this->get('lichess.translation.manager')->getTranslationStatus($locale);
        if(!$status['missing']) {
            return $this->createResponse('');
        }

        return $this->render('LichessBundle:Translation:incomplete.twig', array(
            'locale' => $locale,
            'status' => $status
        ));
    }

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
        $translationClass = $this->container->getParameter('lichess.model.translation.class');
        $translation = new Translation();
        $translation->setCode($locale);
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
                $this->get('session')->setFlash('notice', "Your translation has been submitted, thanks!\nI will review it and include it soon to the game.");

                return $this->redirect($this->generateUrl('lichess_translate_locale', array('locale' => $locale)));
            } else {
                $error = $translation->getYamlError();
            }
        }

        return $this->render('LichessBundle:Translation:locale.twig', array(
            'form' => $form,
            'locale' => $locale,
            'status' => $manager->getTranslationStatus($locale),
            'error' => isset($error) ? $error : false
        ));
    }

    public function exportAction()
    {
        $start = $this->get('request')->query->get('start', 1);
        $translations = $this->get('lichess.translation.provider')->getTranslations($start);

        return $this->createResponse(json_encode($translations), 200, array('Content-Type' => 'application/json'));
    }

    public function listAction()
    {
        $translations = $this->get('lichess.object_manager')->getRepository('LichessBundle:Translation')->createQueryBuilder()
            ->sort('createdAt', 'DESC')->getQuery()->execute();

        return $this->render('LichessBundle:Translation:list.twig', array(
            'translations' => $translations,
            'manager' => $this->get('lichess.translation.manager')
        ));
    }
}

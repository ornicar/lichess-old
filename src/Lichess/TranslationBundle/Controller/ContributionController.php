<?php

namespace Lichess\TranslationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Lichess\TranslationBundle\Document\Translation;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ContributionController extends Controller
{
    public function onMissingAction($locale)
    {
        $status = $this->get('lichess_translation.manager')->getTranslationStatus($locale);
        if($status['available']) {
            return new Response('');
        }

        return $this->render('LichessTranslationBundle:Contribution:missing.html.twig', array(
            'locale' => $locale,
            'name' => $status['name']
        ));
    }

    public function onChangeAction($locale)
    {
        $status = $this->get('lichess_translation.manager')->getTranslationStatus($locale);
        if(!$status['missing']) {
            return new Response('');
        }

        return $this->render('LichessTranslationBundle:Contribution:incomplete.html.twig', array(
            'locale' => $locale,
            'status' => $status
        ));
    }

    public function indexAction()
    {
        $form = $this->get('form.factory')->createNamed($this->get('lichess_translation.form_type.translation'), 'lichess_translation_form');

        return $this->render('LichessTranslationBundle:Contribution:index.html.twig', array(
            'form' => $form->createView(),
            'locale' => '__'
        ));
    }

    public function localeAction($locale)
    {
        $manager = $this->get('lichess_translation.manager');
        $translation = new Translation();
        $translation->setCode($locale);
        try {
            $translation->setMessages($manager->getMessagesWithReferenceKeys($locale));
        } catch(\InvalidArgumentException $e) {
            $translation->setMessages($manager->getEmptyMessages());
        }
        $form = $this->get('form.factory')->createNamed($this->get('lichess_translation.form_type.translation'), 'lichess_translation_form');
        $form->setData($translation);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bindRequest($this->get('request'));
            if($form->isValid()) {
                $this->get('doctrine.odm.mongodb.document_manager')->persist($translation);
                $this->get('doctrine.odm.mongodb.document_manager')->flush(array('safe' => true));
                $this->get('session')->setFlash('notice', "Your translation has been submitted, thanks!\nI will review it and include it soon to the game.");

                return new RedirectResponse($this->generateUrl('lichess_translation_contribution_locale', array('locale' => $locale)));
            }
        }

        return $this->render('LichessTranslationBundle:Contribution:locale.html.twig', array(
            'form' => $form->createView(),
            'object' => $translation,
            'messageKeys' => $manager->getMessageKeys(),
            'locale' => $locale,
            'status' => $manager->getTranslationStatus($locale)
        ));
    }

    public function exportAction()
    {
        $start = $this->get('request')->query->get('start', 1);
        $translations = $this->get('lichess_translation.provider')->getTranslations($start);

        return new Response(json_encode($translations), 200, array('Content-Type' => 'application/json'));
    }
}

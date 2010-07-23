<?php

namespace Bundle\LichessBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller;
use Symfony\Components\Form\Form;
use Symfony\Components\Form\ChoiceField;
use Symfony\Components\Form\TextareaField;
use Bundle\LichessBundle\Entities\Translation;
use Symfony\Components\Finder\Finder;

class TranslationController extends Controller
{

    public function indexAction($locale)
    {
        $locales = include(__DIR__.'/../I18N/locales.php');
        unset($locales['en']);
        ksort($locales);
        array_unshift($locales, 'Choose a language');
        $translation = new Translation();
        if(' ' === $locale) {
            $locale = null;
        }
        else {
            $translation->setCode($locale);
            $translation->setName($locales[$locale]);
        }
        try {
            $translation->setMessages($this->container->getLichessTranslatorService()->getMessages($locale));
        }
        catch(\InvalidArgumentException $e) {
            $translation->setEmptyMessages($this->container->getLichessTranslatorService()->getMessages('fr'));
        }
        $form = new Form('translation', $translation, $this->container->getValidatorService());
        $form->add(new ChoiceField('code', array('choices' => $locales)));
        $form->add(new TextareaField('yamlMessages'));

        if ($this->getRequest()->getMethod() == 'POST')
        {
            try {
                $form->bind($this->getRequest()->request->get('translation'));
                $fileName = sprintf("%s_%s-%d", $translation->getCode(), date("Y-m-d_h-i-s"), time());
                $fileContent = sprintf("#%s\n%s\n", $translation->getName(), $translation->getYamlMessages());
                $file = sprintf('%s/translation/%s', $this->container->getParameter('kernel.root_dir'), $fileName);
                if(!@file_put_contents($file, $fileContent)) {
                    throw new \Exception('Submit failed due to an internal error. please send a mail containing your translation to thibault.duplessis@gmail.com');
                }
                $message = 'Your translation has been submitted, thanks!';
            }
            catch(\Exception $e) {
                $error = $e->getMessage();
            }
        }

        return $this->render('LichessBundle:Translation:index', array(
            'form' => $form,
            'locale' => $locale,
            'message' => isset($message) ? $message : null,
            'error' => isset($error) ? $error : null,
        ));
    }

    public function listAction()
    {
        $finder = new Finder;
        $files = $finder->files()->in(sprintf('%s/translation', $this->container->getParameter('kernel.root_dir')));

        return $this->render('LichessBundle:Translation:list', array('files' => $files));
    }
}

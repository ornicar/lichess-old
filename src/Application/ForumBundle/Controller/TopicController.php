<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\TopicController as BaseTopicController;
use Bundle\ForumBundle\Form\TopicForm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Bundle\ForumBundle\DAO\Topic;
use Bundle\ForumBundle\DAO\Category;

class TopicController extends BaseTopicController
{
    public function newAction($categorySlug)
    {
        $category = $this['forum.category_repository']->findOneBySlug($categorySlug);
        if (!$category) {
            throw new NotFoundHttpException('The category does not exist.');
        }

        $form = $this->createForm('forum_topic_new', $category);

        return $this->render('ForumBundle:Topic:new.'.$this->getRenderer(), array(
            'form' => $this['templating.form']->get($form),
            'category' => $category
        ));
    }

    public function createAction($categorySlug)
    {
        $form = $this->createForm('forum_topic_new');
        $form->bind($this['request']->request->get($form->getName()));

        if(!$form->isValid()) {
            return $this->render('ForumBundle:Topic:new.'.$this->getRenderer(), array(
                'form' => $this['templating.form']->get($form),
                'category' => $category
            ));
        }

        $topic = $form->getData();
        $this->saveTopic($topic);

        $this['session']->setFlash('forum_topic_create/success', true);
        $url = $this['templating.helper.forum']->urlForTopic($topic);

        $response = $this->redirect($url);
        $response->headers->setCookie('lichess_forum_authorName', urlencode($topic->getLastPost()->getAuthorName()), null, new \DateTime('+ 6 month'), $this->generateUrl('forum_index'));

        return $response;
    }

    protected function createForm($name, Category $category = null)
    {
        $form = parent::createForm($name, $category);

        if($authorName = $this['request']->cookies->get('lichess_forum_authorName')) {
            $form['firstPost']['authorName']->setData($authorName);
        }

        return $form;
    }
}

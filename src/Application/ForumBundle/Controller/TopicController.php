<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\TopicController as BaseTopicController;
use Bundle\ForumBundle\Form\TopicForm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Category;

class TopicController extends BaseTopicController
{
    public function createAction(Category $category = null)
    {
        $form = $this->createForm($category);
        $form->bind($this->get('request')->request->get($form->getName()));

        if(!$form->isValid()) {
            return $this->render('ForumBundle:Topic:new.'.$this->getRenderer(), array(
                'form'      => $form,
                'category'  => $category
            ));
        }

        $topic = $form->getData();
        $this->get('forum.blamer.topic')->blame($topic);
        $this->get('forum.blamer.post')->blame($topic->getFirstPost());
        $this->saveTopic($topic);

        $this->get('session')->setFlash('forum_topic_create/success', true);
        $url = $this->get('forum.templating.helper.forum')->urlForTopic($topic);

        $response = $this->redirect($url);
        if(!$this->get('fos_user.templating.helper.security')->isAuthenticated()) {
            $response->headers->setCookie('lichess_forum_authorName', urlencode($topic->getLastPost()->getAuthorName()), null, new \DateTime('+ 6 month'), $this->generateUrl('forum_index'));
        }

        return $response;
    }

    protected function createForm(Category $category = null)
    {
        $form = parent::createForm($category);

        if($this->get('fos_user.templating.helper.security')->isAuthenticated()) {
            unset($form['firstPost']['authorName']);
        } elseif($authorName = $this->get('request')->cookies->get('lichess_forum_authorName')) {
            $form['firstPost']['authorName']->setData(urldecode($authorName));
        }

        return $form;
    }

    /**
     * Compatibility layer with old topic urls
     */
    public function showCompatAction($categorySlug, $slug, $id)
    {
        $topic = $this->get('forum.repository.topic')->findOneById($id);
        if(!$topic) {
            throw new NotFoundHttpException(sprintf('The topic with id "%s" does not exist', $id));
        }
        return $this->redirect($this->generateUrl('forum_topic_show', array(
            'categorySlug' => $categorySlug,
            'slug' => $topic->getSlug()
        )));
    }
}

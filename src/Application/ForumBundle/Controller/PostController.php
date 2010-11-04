<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\PostController as BasePostController;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Post;

class PostController extends BasePostController
{
    public function newAction(Topic $topic)
    {
        $form = $this->createForm('forum_post_new', $topic);

        return $this->render('ForumBundle:Post:new.'.$this->getRenderer(), array(
            'form' => $form,
            'topic' => $topic
        ));
    }

    public function createAction(Topic $topic)
    {
        $form = $this->createForm('forum_post_new', $topic);
        $form->bind($this['request']->request->get($form->getName()));

        if(!$form->isValid()) {
            $lastPage = $this['templating.helper.forum']->getTopicNumPages($topic);
            return $this->forward('ForumBundle:Topic:show', array(
                'categorySlug' => $topic->getCategory()->getSlug(),
                'slug' => $topic->getSlug(),
                'id' => $topic->getId()
            ), array('page' => $lastPage));
        }

        $post = $form->getData();
        $this->savePost($post);

        $this['session']->setFlash('forum_post_create/success', true);
        $url = $this['templating.helper.forum']->urlForPost($post);

        $response = $this->redirect($url);
        $response->headers->setCookie('lichess_forum_authorName', urlencode($post->getAuthorName()), null, new \DateTime('+ 6 month'), $this->generateUrl('forum_index'));

        return $response;
    }

    protected function createForm($name, Topic $topic)
    {
        $form = parent::createForm($name, $topic);

        if($authorName = $this['request']->cookies->get('lichess_forum_authorName')) {
            $form->getData()->setAuthorName(urldecode($authorName));
        }

        return $form;
    }

}

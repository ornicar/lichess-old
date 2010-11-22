<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\PostController as BasePostController;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Post;

class PostController extends BasePostController
{
    public function createAction(Topic $topic)
    {
        $form = $this->createForm('forum_post_new', $topic);
        $form->bind($this->get('request')->request->get($form->getName()));

        if(!$form->isValid()) {
            $lastPage = $this->get('forum.templating.helper.forum')->getTopicNumPages($topic);
            return $this->forward('ForumBundle:Topic:show', array(
                'categorySlug' => $topic->getCategory()->getSlug(),
                'slug' => $topic->getSlug(),
                'id' => $topic->getId()
            ), array('page' => $lastPage));
        }

        $post = $form->getData();
        $post->setTopic($topic);
        $this->get('forum.blamer.post')->blame($post);
        $this->savePost($post);

        $this->get('session')->setFlash('forum_post_create/success', true);
        $url = $this->get('forum.templating.helper.forum')->urlForPost($post);

        $response = $this->redirect($url);
        if(!$this->get('lichess.security.helper')->isAuthenticated()) {
            $response->headers->setCookie('lichess_forum_authorName', urlencode($post->getAuthorName()), null, new \DateTime('+ 6 month'), $this->generateUrl('forum_index'));
        }

        return $response;
    }

    protected function createForm($name, Topic $topic)
    {
        $form = parent::createForm($name, $topic);

        if($this->get('lichess.security.helper')->isAuthenticated()) {
            unset($form['authorName']);
        } elseif($authorName = $this->get('request')->cookies->get('lichess_forum_authorName')) {
            $form['authorName']->setData(urldecode($authorName));
        }

        return $form;
    }

}

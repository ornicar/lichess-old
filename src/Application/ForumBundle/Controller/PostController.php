<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\PostController as BasePostController;
use Bundle\ForumBundle\DAO\Topic;
use Bundle\ForumBundle\DAO\Post;

class PostController extends BasePostController
{
    public function newAction($topicId)
    {
        $topic = $this['forum.topic_repository']->findOneById($topicId);
        if (!$topic) {
            throw new NotFoundHttpException('The topic does not exist.');
        }

        $form = $this->createForm('forum_post_new', $topic);

        return $this->render('ForumBundle:Post:new.'.$this->getRenderer(), array(
            'form' => $form,
            'topic' => $topic
        ));
    }

    public function createAction($topicId)
    {
        $topic = $this['forum.topic_repository']->findOneById($topicId);
        if (!$topic) {
            throw new NotFoundHttpException('The topic does not exist.');
        }

        $form = $this->createForm('forum_post_new', $topic);
        $form->bind($this['request']->request->get($form->getName()));

        if(!$form->isValid()) {
            $lastPage = $this['templating.helper.forum']->getTopicNumPages($topic);
            return $this->forward('ForumBundle:Topic:show', array(
                'categorySlug' => $topic->getCategory()->getSlug(),
                'id' => $topicId
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
            $form->getData()->setAuthorName($authorName);
        }

        return $form;
    }

}

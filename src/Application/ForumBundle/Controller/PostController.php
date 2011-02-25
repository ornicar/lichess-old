<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\PostController as BasePostController;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Form\Error;

class PostController extends BasePostController
{
    public function createAction(Topic $topic)
    {
        $form = $this->get('forum.form.post');
        $post = $this->get('forum.repository.post')->createNewPost();
        $post->setTopic($topic);
        $form->bind($this->get('request'), $post);

        if(!$form->isValid()) {
            return $this->invalidCreate($topic);
        }

        $this->get('forum.blamer.post')->blame($post);

        if ($this->get('forum.akismet')->isPostSpam($post)) {
            $form['message']->addError(new Error('Sorry, but your post looks like spam. If you think it is an error, send me an email.'));
            return $this->invalidCreate($topic);
        }

        $this->get('forum.creator.post')->create($post);

        $objectManager = $this->get('forum.object_manager');
        $objectManager->persist($post);
        $objectManager->flush();

        $url = $this->get('forum.router.url_generator')->urlForPost($post);
        $response = new RedirectResponse($url);

        if(!$this->get('security.context')->vote('IS_AUTHENTICATED_FULLY')) {
            $response->headers->setCookie(new Cookie('lichess_forum_authorName', urlencode($post->getAuthorName()), null, $this->generateUrl('forum_index'), '', false, new \DateTime('+ 6 month')));
        }

        return $response;
    }

    protected function invalidCreate(Topic $topic)
    {
        $lastPage = $this->get('forum.router.url_generator')->getTopicNumPages($topic);

        return $this->forward('ForumBundle:Topic:show', array(
            'categorySlug' => $topic->getCategory()->getSlug(),
            'slug'         => $topic->getSlug(),
            'id'           => $topic->getId()
        ), array('page' => $lastPage));
    }
}

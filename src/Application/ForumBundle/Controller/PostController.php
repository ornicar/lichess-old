<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\PostController as BasePostController;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Form\FormError;

class PostController extends BasePostController
{
    public function newAction(Topic $topic)
    {
        $post = $this->get('forum.repository.post')->createNewPost();
        $this->get('forum.authorname_persistence')->loadPost($post);
        $form = $this->get('form.factory')->createNamed($this->get('lichess_forum.form_type.post'), 'forum_post_form', $post);

        return $this->get('templating')->renderResponse('ForumBundle:Post:new.html.'.$this->getRenderer(), array(
            'form'  => $form->createView(),
            'topic' => $topic,
        ));
    }

    public function createAction(Topic $topic)
    {
        $post = $this->get('forum.repository.post')->createNewPost();
        $post->setTopic($topic);
        $form = $this->get('form.factory')->createNamed($this->get('lichess_forum.form_type.post'), 'forum_post_form', $post);
        $form->bindRequest($this->get('request'));

        if(!$form->isValid()) {
            return $this->invalidCreate($topic);
        }

        $this->get('forum.blamer.post')->blame($post);

        if ($this->get('forum.akismet')->isPostSpam($post)) {
            $form['message']->addError(new FormError('Sorry, but your post looks like spam. If you think it is an error, send me an email.'));
            $this->get('logger')->warn('ForumBundle:post spam block: '.$post->getAuthorName());
            return $this->invalidCreate($topic);
        }

        $this->get('forum.creator.post')->create($post);

        $objectManager = $this->get('forum.object_manager');
        $objectManager->persist($post);
        $objectManager->flush();

        $this->get('lichess_forum.timeline.pusher')->pushPost($topic->getFirstPost());
        $objectManager->flush();

        $url = $this->get('forum.router.url_generator')->urlForPost($post);
        $response = new RedirectResponse($url);
        $this->get('forum.authorname_persistence')->persistPost($post, $response);

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

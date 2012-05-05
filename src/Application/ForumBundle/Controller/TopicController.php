<?php

namespace Application\ForumBundle\Controller;

use Herzult\Bundle\ForumBundle\Controller\TopicController as BaseTopicController;
use Herzult\Bundle\ForumBundle\Form\TopicForm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Herzult\Bundle\ForumBundle\Model\Topic;
use Herzult\Bundle\ForumBundle\Model\Category;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Form\FormError;

class TopicController extends BaseTopicController
{
    public function newAction(Category $category = null)
    {
        $checkmate = $this->get('lila')->captchaCreate();
        $post = $this->get('herzult_forum.repository.post')->createNewPost();
        $post->checkmateId = $checkmate['id'];
        $this->get('lichess_forum.authorname_persistence')->loadPost($post);
        $topic = $this->get('herzult_forum.repository.topic')->createNewTopic();
        $topic->setFirstPost($post);
        if ($category) {
            $topic->setCategory($category);
        }
        $form = $this->get('form.factory')->createNamed($this->get('lichess_forum.form_type.new_topic'), 'forum_new_topic_form', $topic);

        return $this->get('templating')->renderResponse('HerzultForumBundle:Topic:new.html.'.$this->getRenderer(), array(
            'form'      => $form->createView(),
            'category'  => $category,
            'checkmate' => $checkmate
        ));
    }

    public function createAction(Category $category = null)
    {
        $postData = $this->get('request')->request->all();
        $checkmateId = isset($postData['forum_new_topic_form']['firstPost']['checkmateId']) ? $postData['forum_new_topic_form']['firstPost']['checkmateId'] : null;
        if (empty($checkmateId)) throw new HttpException(400);
        $solutions = $this->get('lila')->captchaSolve($checkmateId);
        if (empty($solutions)) throw new HttpException(400);
        $post = $this->get('herzult_forum.repository.post')->createNewPost();
        $post->checkmateSolutions = $solutions;

        $topic = $this->get('herzult_forum.repository.topic')->createNewTopic();
        $topic->setCategory($category);
        $topic->setFirstPost($post);
        $form = $this->get('form.factory')->createNamed($this->get('lichess_forum.form_type.new_topic'), 'forum_new_topic_form', $topic);
        $form->bindRequest($this->get('request'));

        if(!$form->isValid()) {
            return $this->invalidCreate($category, $form);
        }

        $this->get('herzult_forum.blamer.topic')->blame($topic);
        $this->get('herzult_forum.blamer.post')->blame($topic->getFirstPost());

        if ($this->get('forum.akismet')->isTopicSpam($topic)) {
            $form['firstPost']->addError(new FormError('Sorry, but your topic looks like spam. If you think it is an error, send me an email.'));
            $this->get('logger')->warn('HerzultForumBundle:topic spam block: '.$topic->getFirstPost()->getAuthorName().' - '.$topic->getSubject());
            return $this->invalidCreate($category, $form);
        }

        $this->get('herzult_forum.creator.topic')->create($topic);
        $this->get('herzult_forum.creator.post')->create($topic->getFirstPost());

        $objectManager = $this->get('herzult_forum.object_manager');
        $objectManager->persist($topic);
        $objectManager->persist($topic->getFirstPost());
        $objectManager->flush();
        $this->get('lichess_forum.newposts_cache')->invalidate();

        $this->get('session')->setFlash('forum_topic_create/success', true);
        $url = $this->get('herzult_forum.router.url_generator')->urlForTopic($topic);
        $response = new RedirectResponse($url);
        $this->get('lichess_forum.authorname_persistence')->persistTopic($topic, $response);

        return $response;
    }

    protected function invalidCreate(Category $category, $form)
    {
        $checkmate = $this->get('lila')->captchaCreate();

        return $this->render('HerzultForumBundle:Topic:new.html.twig', array(
            'form'      => $form->createView(),
            'category'  => $category,
            'checkmate' => $checkmate
        ));
    }

    public function showAction($categorySlug, $slug, $form = null)
    {
        $topic = $this->findTopic($categorySlug, $slug);
        $this->get('herzult_forum.repository.topic')->incrementTopicNumViews($topic);

        if ('html' === $this->get('request')->getRequestFormat()) {
            $page = $this->get('request')->query->get('page', 1);
            $posts = $this->get('herzult_forum.repository.post')->findAllByTopic($topic, true);
            $posts->setCurrentPage($page);
            $posts->setMaxPerPage($this->container->getParameter('herzult_forum.paginator.posts_per_page'));
        } else {
            $posts = $this->get('herzult_forum.repository.post')->findRecentByTopic($topic, 30);
        }

        $template = sprintf('HerzultForumBundle:Topic:show.%s.%s', $this->get('request')->getRequestFormat(), $this->getRenderer());
        return $this->get('templating')->renderResponse($template, array(
            'topic' => $topic,
            'posts' => $posts,
            'form' => $form
        ));
    }
}

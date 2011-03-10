<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\TopicController as BaseTopicController;
use Bundle\ForumBundle\Form\TopicForm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Category;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\Form\Error;

class TopicController extends BaseTopicController
{
    public function newAction(Category $category = null)
    {
        $post = $this->get('forum.repository.post')->createNewPost();
        $this->get('forum.authorname_persistence')->loadPost($post);
        $topic = $this->get('forum.repository.topic')->createNewTopic();
        $topic->setFirstPost($post);
        if ($category) {
            $topic->setCategory($category);
        }
        $form = $this->get('forum.form.new_topic');
        $form->setData($topic);

        return $this->get('templating')->renderResponse('ForumBundle:Topic:new.html.'.$this->getRenderer(), array(
            'form'      => $form,
            'category'  => $category
        ));
    }

    public function createAction(Category $category = null)
    {
        $topic = $this->get('forum.repository.topic')->createNewTopic();
        $form = $this->get('forum.form.new_topic');
        $form->bind($this->get('request'), $topic);

        if(!$form->isValid()) {
            return $this->invalidCreate($category, $form);
        }

        $this->get('forum.blamer.topic')->blame($topic);
        $this->get('forum.blamer.post')->blame($topic->getFirstPost());

        if ($this->get('forum.akismet')->isTopicSpam($topic)) {
            $form['firstPost']->addError(new Error('Sorry, but your topic looks like spam. If you think it is an error, send me an email.'));
            $this->get('logger')->notice('Forum:topic spam block: '.$topic->getFirstPost()->getAuthor());
            return $this->invalidCreate($category, $form);
        }

        $this->get('forum.creator.topic')->create($topic);
        $this->get('forum.creator.post')->create($topic->getFirstPost());

        $objectManager = $this->get('forum.object_manager');
        $objectManager->persist($topic);
        $objectManager->persist($topic->getFirstPost());
        $objectManager->flush();

        $this->get('session')->setFlash('forum_topic_create/success', true);
        $url = $this->get('forum.router.url_generator')->urlForTopic($topic);
        $response = new RedirectResponse($url);
        $this->get('forum.authorname_persistence')->persistTopic($topic, $response);

        return $response;
    }

    protected function invalidCreate(Category $category, $form)
    {
        return $this->render('ForumBundle:Topic:new.html.twig', array(
            'form'      => $form,
            'category'  => $category
        ));
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

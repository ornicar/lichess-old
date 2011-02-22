<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\TopicController as BaseTopicController;
use Bundle\ForumBundle\Form\TopicForm;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Category;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TopicController extends BaseTopicController
{
    public function createAction(Category $category = null)
    {
        $form = $this->get('forum.form.new_topic');
        $topic = $this->get('forum.repository.topic')->createNewTopic();
        $form->bind($this->get('request'), $topic);

        if(!$form->isValid()) {
            return $this->render('ForumBundle:Topic:new.html.twig', array(
                'form'      => $form,
                'category'  => $category
            ));
        }

        $topic = $form->getData();
        $this->get('forum.creator.topic')->create($topic);
        $this->get('forum.blamer.topic')->blame($topic);

        $this->get('forum.creator.post')->create($topic->getFirstPost());
        $this->get('forum.blamer.post')->blame($topic->getFirstPost());

        $objectManager = $this->get('forum.object_manager');
        $objectManager->persist($topic);
        $objectManager->persist($topic->getFirstPost());
        $objectManager->flush();

        $this->get('session')->setFlash('forum_topic_create/success', true);
        $url = $this->get('forum.router.url_generator')->urlForTopic($topic);

        $response = new RedirectResponse($url);
        if(!$this->get('security.context')->vote('IS_AUTHENTICATED_FULLY')) {
            $response->headers->setCookie('lichess_forum_authorName', urlencode($topic->getLastPost()->getAuthorName()), null, new \DateTime('+ 6 month'), $this->generateUrl('forum_index'));
        }

        return $response;
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

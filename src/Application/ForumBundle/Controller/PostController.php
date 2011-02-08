<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\PostController as BasePostController;
use Bundle\ForumBundle\Model\Topic;
use Bundle\ForumBundle\Model\Post;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends BasePostController
{
    public function deleteAction($id)
    {
        if(!$this->get('security.context')->vote('ROLE_SUPERADMIN')) {
            throw new NotFoundHttpException();
        }

        return parent::deleteAction($id);
    }

    public function createAction(Topic $topic)
    {
        $post = $this->get('forum.repository.post')->createNewPost();
        $post->setTopic($topic);
        $form = $this->get('forum.form.post');
        $form->bind($this->get('request'), $post);

        if(!$form->isValid()) {
            $lastPage = $this->get('forum.router.url_generator')->getTopicNumPages($topic);
            return $this->forward('ForumBundle:Topic:show', array(
                'categorySlug' => $topic->getCategory()->getSlug(),
                'slug' => $topic->getSlug(),
                'id' => $topic->getId()
            ), array('page' => $lastPage));
        }

        $post = $form->getData();
        $post->setTopic($topic);

        $this->get('forum.creator.post')->create($post);
        $this->get('forum.blamer.post')->blame($post);

        $objectManager->persist($post);
        $objectManager->flush();

        $url = $this->get('forum.router.url_generator')->urlForPost($post);
        $response = $this->redirect($url);

        if(!$this->get('security.context')->vote('IS_AUTHENTICATED_FULLY')) {
            $response->headers->setCookie('lichess_forum_authorName', urlencode($post->getAuthorName()), null, new \DateTime('+ 6 month'), $this->generateUrl('forum_index'));
        }

        return $response;
    }

    protected function createForm($name, Topic $topic)
    {
        $form = parent::createForm($name, $topic);

        if($this->get('security.context')->vote('IS_AUTHENTICATED_FULLY')) {
            unset($form['authorName']);
        } elseif($authorName = $this->get('request')->cookies->get('lichess_forum_authorName')) {
            $form['authorName']->setData(urldecode($authorName));
        }

        return $form;
    }

}

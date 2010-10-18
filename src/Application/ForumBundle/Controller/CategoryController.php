<?php

namespace Application\ForumBundle\Controller;

use Bundle\ForumBundle\Controller\CategoryController as BaseCategoryController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CategoryController extends BaseCategoryController
{
    public function showAction($slug)
    {
        $category = $this['forum.category_repository']->findOneBySlug($slug);

        if (!$category) {
            throw new NotFoundHttpException(sprintf('The category %s does not exist.', $slug));
        }

        $page = $this['request']->query->get('page', 1);
        $topics = $this['forum.topic_repository']->findAllByCategory($category, true);
        $topics->setCurrentPageNumber($page);
        $topics->setItemCountPerPage($this->container->getParameter('forum.topic_list.max_per_page'));
        $topics->setPageRange(5);

        return $this->render('ForumBundle:Category:show.'.$this->getRenderer(), array(
            'category'  => $category,
            'topics'    => $topics
        ));
    }
}

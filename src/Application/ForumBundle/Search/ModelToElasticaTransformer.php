<?php

namespace Application\ForumBundle\Search;

use Elastica_Document;
use FOQ\ElasticaBundle\Transformer\ModelToElasticaAutoTransformer;
use Bundle\ForumBundle\Document\PostRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ModelToElasticaTransformer extends ModelToElasticaAutoTransformer
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function transform($object, array $fields)
    {
        $messages = $authors = array();
        $posts = $this->container->get('forum.repository.post')->findAllByTopic($object, false);
        foreach ($posts as $post) {
            $messages[] = $post->getMessage();
            $authors[] = $post->getAuthorName();
        }
        $array = array(
            'subject' => $object->getSubject(),
            'messages' => implode(' ', $messages),
            'authors' => implode(' ', $authors)
        );

        return new Elastica_Document((string) $object->getId(), $array);
    }
}

<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(
 *   collection="wiki",
 *   repositoryClass="Bundle\LichessBundle\Document\WikiPageRepository"
 * )
 */
class WikiPage
{
    /**
     * @var string
     * @MongoDB\Id(strategy="none")
     */
    protected $slug;

    /**
     * @var string
     * @MongoDB\String
     * @MongoDB\Index
     */
    protected $name;

    /**
     * @var string
     * @MongoDB\String
     */
    protected $title;

    /**
     * @var string
     * @MongoDB\Field(type="string")
     */
    protected $body;

    public function __construct($name, $body)
    {
        $this->name = $name;
        $this->slug = preg_replace('/^\d+_(.+)$/', '$1', $name);
        $this->title = str_replace('-', ' ', $this->slug);
        $this->body = $body;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function __toString()
    {
        return $this->slug;
    }
}

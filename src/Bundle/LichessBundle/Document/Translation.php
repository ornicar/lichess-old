<?php

namespace Bundle\LichessBundle\Document;
use Symfony\Component\Yaml\Yaml;

/**
 * @mongodb:Document(
 *   collection="translation"
 * )
 */
class Translation
{
    /**
     * Unique ID of the translation
     *
     * @var string
     * @mongodb:Id(custom="true")
     */
    protected $id;

    /**
     * 2 chars language code
     *
     * @mongodb:Field(type="string")
     * @var string
     */
    protected $code = null;

    /**
     * The locale name, translated to the locale itself
     *
     * @mongodb:Field(type="string")
     * @var string
     */
    protected $name = null;

    /**
     * @validation:AssertNotNull
     */
    protected $messages = array();

    /**
     * translated messages
     * @mongodb:Field(type="string")
     * @validation:AssertNotNull
     * @var string
     */
    protected $yaml = null;

    /**
     * author name
     *
     * @mongodb:Field(type="string")
     * @var string
     */
    protected $author = null;

    /**
     * comment
     *
     * @mongodb:Field(type="string")
     * @var string
     */
    protected $comment = null;

    /**
     * creation date
     *
     * @mongodb:Field(type="date")
     * @var \DateTime
     */
    protected $createdAt = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Get yamlMessages
     * @return string
     */
    public function getYaml()
    {
        return $this->yaml;
    }

    /**
     * Set yamlMessages
     * @param  string
     * @return null
     */
    public function setYaml($yaml)
    {
        $this->yaml = $yaml;
        try {
            $this->messages = Yaml::load($yaml);
        }
        catch(\InvalidArgumentException $e) {
            $this->messages = null;
        }
    }

    /**
     * @validation:AssertFalse()
     */
    public function getYamlError()
    {
        try {
            Yaml::load($this->yaml);
        }
        catch(\InvalidArgumentException $e) {
            return str_replace('Unable to parse string: Unable to parse', 'Error', $e->getMessage());
        }
        return false;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param  \DateTime
     * @return null
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param  string
     * @return null
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }
    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param  string
     * @return null
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }
    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param  array
     * @return null
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        $lines = array();
        foreach($this->getMessages() as $from => $to) {
            $lines[] = sprintf('"%s": "%s"', $from, $to);
        }

        $this->yaml = implode("\n", $lines);
    }
    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string
     * @return null
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param  string
     * @return null
     */
    public function setCode($code)
    {
        $this->code = $code;
    }
}

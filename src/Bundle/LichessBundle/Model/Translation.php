<?php

namespace Bundle\LichessBundle\Model;
use Symfony\Component\Yaml\Yaml;

abstract class Translation
{
    protected $id;

    protected $code = null;

    protected $messages = array();

    protected $yaml = null;

    protected $author = null;

    protected $comment = null;

    protected $createdAt = null;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    public function getNumericId()
    {
        return is_numeric($this->id) ? $this->id : null;
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
        return $this->messages ? $this->messages : Yaml::load($this->yaml);
    }

    public function getMessagesValues()
    {
        return array_values($this->getMessages());
    }

    public function setMessagesValues($values)
    {
        return $this->setMessages(array_combine($this->getMessagesKeys(), $values));
    }

    public function getMessagesKeys()
    {
        return array_keys($this->getMessages());
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
            if(!empty($to)) {
                $lines[] = sprintf('"%s": "%s"', $from, $to);
            }
        }

        $this->yaml = implode("\n", $lines);
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

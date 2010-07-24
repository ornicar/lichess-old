<?php

namespace Bundle\LichessBundle\Entities;
use Symfony\Components\Yaml\Yaml;

class Translation
{
    /**
     * The locale code (2 chars)
     *
     * @var string
     */
    protected $code = null;

    /**
     * The locale name, translated to the locale itself
     *
     * @var string
     */
    protected $name = null;

    /**
     * The translation messages
     *
     * @var array
     */
    protected $messages = array();

    public $comment;

    public $author;
    
    /**
     * Get code
     * @return string
     */
    public function getCode()
    {
      return $this->code;
    }
    
    /**
     * Set code
     * @param  string
     * @return null
     */
    public function setCode($code)
    {
      $this->code = $code;
    }
    
    /**
     * Get name
     * @return string
     */
    public function getName()
    {
      return $this->name;
    }
    
    /**
     * Set name
     * @param  string
     * @return null
     */
    public function setName($name)
    {
      $this->name = $name;
    }
    
    /**
     * Get messages
     * @return array
     */
    public function getMessages()
    {
      return $this->messages;
    }
    
    /**
     * Set messages
     * @param  array
     * @return null
     */
    public function setMessages($messages)
    {
      $this->messages = $messages;
    }

    public function setEmptyMessages($messages)
    {
        foreach($messages as $from => $to) {
            $messages[$from] = '';
        }

        $this->setMessages($messages);
    }

    /**
     * Get yamlMessages
     * @return string
     */
    public function getYamlMessages()
    {
        $lines = array();
        foreach($this->getMessages() as $from => $to) {
            $lines[] = sprintf('"%s": "%s"', $from, $to);
        }

        return implode("\n", $lines);
    }
    
    /**
     * Set yamlMessages
     * @param  string
     * @return null
     */
    public function setYamlMessages($yamlMessages)
    {
        $this->messages = Yaml::load($yamlMessages);
    }
    
}

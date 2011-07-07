<?php

namespace Lichess\OpeningBundle\Config;

use Symfony\Component\Validator\Constraints as Assert;

class AiGameConfig extends GameConfig
{
    /**
     * AI level
     *
     * @Assert\NotBlank()
     * @Assert\Min(1)
     * @Assert\Max(8)
     * @var int
     */
    protected $level = 1;

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param  int
     * @return null
     */
    public function setLevel($level)
    {
        $this->level = (int) $level;
    }

    public function toArray()
    {
        return array_merge(parent::toArray(), array('level' => $this->level));
    }

    public function fromArray(array $data)
    {
        parent::fromArray($data);

        if(isset($data['level'])) $this->level = $data['level'];
    }
}

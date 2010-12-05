<?php

namespace Bundle\LichessBundle\Entity;

use Bundle\LichessBundle\Model;

/**
 * @orm:Entity(repositoryClass="Bundle\LichessBundle\Entity\TranslationRepository")
 */
class Translation extends Model\Translation
{
    /**
     * Unique ID of the translation
     *
     * @var string
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue
     */
    protected $id;

    /**
     * 2 chars language code
     *
     * @orm:Column(type="string")
     * @var string
     */
    protected $code = null;

    /**
     * @validation:AssertNotNull
     */
    protected $messages = array();

    /**
     * translated messages
     * @orm:Column(type="string")
     * @validation:AssertNotNull
     * @var string
     */
    protected $yaml = null;

    /**
     * author name
     *
     * @orm:Column(type="string")
     * @var string
     */
    protected $author = null;

    /**
     * comment
     *
     * @orm:Column(type="string")
     * @var string
     */
    protected $comment = null;

    /**
     * creation date
     *
     * @orm:Column(type="date")
     * @var \DateTime
     */
    protected $createdAt = null;
}

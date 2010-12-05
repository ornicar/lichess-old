<?php

namespace Bundle\LichessBundle\Document;

use Bundle\LichessBundle\Model;

/**
 * @mongodb:Document(
 *   collection="translation",
 *   repositoryClass="Bundle\LichessBundle\Document\TranslationRepository"
 * )
 */
class Translation extends Model\Translation
{
    /**
     * Unique ID of the translation
     *
     * @var string
     * @mongodb:Id(strategy="increment")
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
}

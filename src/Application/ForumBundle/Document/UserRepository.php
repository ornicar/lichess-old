<?php

namespace Application\ForumBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;

class UserRepository extends DocumentRepository
{
    public function getObjectClass()
    {
        return $this->getDocumentName();
    }
}

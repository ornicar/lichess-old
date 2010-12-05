<?php

namespace Bundle\LichessBundle\Document;

use Doctrine\ODM\MongoDB\DocumentRepository;
use Bundle\LichessBundle\Model\RepositoryInterface;

abstract class ObjectRepository extends DocumentRepository implements RepositoryInterface
{

    /**
     * @see RepositoryInterface::getObjectManager
     */
    public function getObjectManager()
    {
        return $this->getDocumentManager();
    }

    /**
     * @see RepositoryInterface::getObjectClass
     */
    public function getObjectClass()
    {
        return $this->getDocumentName();
    }

    /**
     * @see RepositoryInterface::getObjectIdentifier
     */
    public function getObjectIdentifier()
    {
        return $this->getClassMetadata()->identifier;
    }

    /**
     * @see RepositoryInterface::cleanUp
     */
    public function cleanUp()
    {
        $this->createQueryBuilder()->remove()->getQuery()->execute();
    }
}

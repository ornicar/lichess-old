<?php

namespace Bundle\PHPUnitBundle\Functional\Service;

use Bundle\PHPUnitBundle\Functional\Service\Service;
use Bundle\PHPUnitBundle\Functional\WebTestCase;

class BackupSqliteService extends Service
{
    protected $databasePath;
    
    public function init()
    {
        $this->databasePath = $this->options['database_path'];
    }
    
    public function setUp()
    {
        copy($this->getDatabasePath(), $this->getDatabaseBackupPath());
    }

    public function tearDown()
    {
        copy($this->getDatabaseBackupPath(), $this->getDatabasePath());
    }
    
    protected function getDatabasePath()
    {
        return $this->databasePath;
    }

    protected function getDatabaseBackupPath()
    {
        return $this->getDatabasePath().'.bak';
    }
    
}
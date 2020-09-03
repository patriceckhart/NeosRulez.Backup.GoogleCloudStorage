<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Service;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @Flow\Scope("singleton")
 */
class BackupService {

    /**
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Factory\DatabaseFactory
     */
    protected $databaseFactory;

    /**
     *
     * @return void
     */
    public function createBackup() {

        return $this->databaseFactory->createDatabaseBackup();

    }

}

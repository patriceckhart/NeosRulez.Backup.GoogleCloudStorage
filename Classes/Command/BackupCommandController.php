<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Command;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

/**
 * @Flow\Scope("singleton")
 */
class BackupCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Service\BackupService
     */
    protected $backupService;


    /**
     * Create backup on Google Cloud Storage
     *
     * @return void
     */
    public function createCommand() {

        $backup = $this->backupService->createBackup();
        $this->outputLine('created: ' . $backup['file'] . ' on ' . $backup['bucket']);
    }

    /**
     * Restore backup from Google Cloud Storage (can't be undone!)
     *
     * @param string $backup
     * @return void
     */
    public function restoreCommand($backup) {
        $this->backupService->restore($backup);
        $this->outputLine('restored: ' . $backup);
    }

    /**
     * Delete backup on Google Cloud Storage (can't be undone!)
     *
     * @param string $backup
     * @return void
     */
    public function deleteCommand($backup) {
        $bucket = $this->backupService->delete($backup);
        $this->outputLine('deleted: ' . $backup . ' from ' . $bucket);
    }

}

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
     * @param string $name
     * @return void
     */
    public function createCommand($name = NULL) {
        if($name) {
            $backup = $this->backupService->createBackup($name);
        } else {
            $backup = $this->backupService->createBackup();
        }
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
     * Restore only data backup from Google Cloud Storage (can't be undone!)
     *
     * @param string $backup
     * @return void
     */
    public function restoredataCommand($backup) {
        $this->backupService->restorePersistentData($backup);
        $this->outputLine('data restored: ' . $backup);
    }

    /**
     * Restore only database from Google Cloud Storage (can't be undone!)
     *
     * @param string $backup
     * @return void
     */
    public function restoredatabaseCommand($backup) {
        $this->backupService->restoreDatabase($backup);
        $this->outputLine('database restored: ' . $backup);
    }

    /**
     * Download backup from Google Cloud Storage
     *
     * @param string $backup
     * @return void
     */
    public function downloadCommand($backup) {
        $this->backupService->download($backup);
        $this->outputLine('Backup has been downloaded: https://yourdomain.com/' . $backup);
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

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
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings) {
        $this->settings = $settings;
    }


    /**
     * Create backup on Google Cloud Storage
     *
     * @param string $name
     * @return void
     */
    public function createCommand(string $name = null):void
    {
        $backup = $name !== null ? $this->backupService->createBackup($name) : $this->backupService->createBackup();
        $this->outputLine('backup ' . $backup . ' created on ' . $this->settings['storage_bucket_name']);
    }

    /**
     * Delete backup on Google Cloud Storage (can't be undone!)
     *
     * @param string $name
     * @return void
     */
    public function deleteCommand(string $name):void
    {
        $result = $this->backupService->delete($name);
        $this->outputLine('deleted: ' . $result . ' from ' . $this->settings['storage_bucket_name']);
    }

    /**
     * Restore backup from Google Cloud Storage (can't be undone!)
     *
     * @param string $name
     * @return void
     */
    public function restoreCommand(string $name) {
        $result = $this->backupService->restore($name);
        $this->outputLine($result);
    }

    /**
     * Restore persistent data backup from Google Cloud Storage (can't be undone!)
     *
     * @param string $name
     * @return void
     */
    public function restoreDataCommand(string $name):void
    {
        $result = $this->backupService->restorePersistentData($name);
        $this->outputLine($result);
    }

    /**
     * Restore persistent data backup from Google Cloud Storage (can't be undone!)
     *
     * @param string $name
     * @return void
     */
    public function restoreDatabaseCommand(string $name):void
    {
        $result = $this->backupService->restoreDatabase($name);
        $this->outputLine($result);
    }

}

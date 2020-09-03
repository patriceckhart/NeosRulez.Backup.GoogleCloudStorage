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
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings) {
        $this->settings = $settings;
    }

    public function storage() {
        $storage = new StorageClient([
            'keyFilePath' => $this->settings['key_file_path']
        ]);
        return $storage;
    }

    /**
     * Create backup on Google Cloud Storage
     *
     * @return void
     */
    public function createCommand() {
print_r($this->backupService->createBackup());
//        $this->outputLine($this->backupService->createBackup());
//        $this->outputLine('created: '.$file.' on '.$this->settings['storage_bucket_name']);
    }

    /**
     * Restore backup from Google Cloud Storage (can't be undone!)
     *
     * @param string $backup
     * @return void
     */
    public function restoreCommand($backup) {

//        $this->outputLine('backup: '.$backup.' restored from '.$this->settings['storage_bucket_name']);
    }

}

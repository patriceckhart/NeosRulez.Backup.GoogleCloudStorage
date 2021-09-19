<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Service;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use wapmorgan\UnifiedArchive\UnifiedArchive;
use Ifsnop\Mysqldump as IMysqldump;

/**
 *
 * @Flow\Scope("singleton")
 */
class BackupService {

    /**
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Service\GoogleCloudStorageService
     */
    protected $googleCloudStorageService;

    /**
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Factory\PersistentDataFactory
     */
    protected $persistentDataFactory;

    /**
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Factory\DatabaseFactory
     */
    protected $databaseFactory;

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
     * @param string $name
     * @return string
     */
    public function createBackup($name = null):string
    {
        $tempDir = sys_get_temp_dir();
        $fileIdentifier = $name !== null ? $name : $this->settings['backup_identfier'];
        $backupFilename = $fileIdentifier . '_' . date('Y-m-d_H-i-s').'.tar.gz';
        $backupSource = $tempDir . '/' . $backupFilename;

        $persistentData = $this->persistentDataFactory->create();
        $database = $this->databaseFactory->create();

        UnifiedArchive::archiveFiles(['PersistentData.zip' => $persistentData, 'Database.sql' => $database], $backupSource);
        $this->googleCloudStorageService->upload($backupFilename, $backupSource);
        unlink($backupSource);
        unlink($database);
        unlink($persistentData);
        return $backupFilename;
    }

    /**
     * @return string
     */
    public function database():string
    {
        return $this->databaseFactory->create();
    }

    /**
     * @param string $objectName
     * @return string
     */
    function restore(string $objectName):string
    {
        $this->persistentDataFactory->restore($objectName);
        $this->databaseFactory->restore($objectName);
        $this->unlinkTemporaryFiles($objectName);
        return 'backup ' . $objectName . ' restored.';
    }

    /**
     * @param string $objectName
     * @return string
     */
    function restorePersistentData(string $objectName):string
    {
        $this->persistentDataFactory->restore($objectName);
        $this->unlinkTemporaryFiles($objectName);
        return 'persistent data backup ' . $objectName . ' restored.';
    }

    /**
     * @param string $objectName
     * @return string
     */
    function restoreDatabase(string $objectName):string
    {
        $this->databaseFactory->restore($objectName);
        $this->unlinkTemporaryFiles($objectName);
        return 'database backup ' . $objectName . ' restored.';
    }

    /**
     * @param string $objectName
     * @return string
     */
    function delete(string $objectName):string
    {
        $this->googleCloudStorageService->delete($objectName);
        return 'backup ' . $objectName . ' deleted.';
    }

    /**
     * @param string $objectName
     * @return void
     */
    function unlinkTemporaryFiles(string $objectName):void
    {
        unlink(sys_get_temp_dir() . '/' . 'PersistentData.zip');
        unlink(sys_get_temp_dir() . '/' . $objectName);
        unlink(sys_get_temp_dir() . '/Database.sql');
    }

}

<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Service;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

use wapmorgan\UnifiedArchive\UnifiedArchive;

/**
 * @Flow\Scope("singleton")
 */
class BackupService extends AbstractService
{

    /**
     * @param string $name
     * @return string
     */
    public function createBackup(string $name = ''): string
    {
        $tempDir = sys_get_temp_dir();
        $fileIdentifier = $name !== '' ? $name : $this->settings['backup_identfier'];
        $backupFilename = $fileIdentifier . '_' . date('Y-m-d_H-i-s').'.zip';
        $backupSource = $tempDir . '/' . $backupFilename;

        $persistentData = $this->PersistentDataService->create();
        $database = $this->databaseService->create();

        UnifiedArchive::archiveFiles(['Data.zip' => $persistentData, 'Database.sql' => $database], $backupSource);
        $this->googleCloudStorageService->upload($backupFilename, $backupSource);
        unlink($backupSource);
        unlink($database);
        unlink($persistentData);
        return $backupFilename;
    }

    /**
     * @return string
     */
    private function database(): string
    {
        return $this->databaseService->create();
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function restore(string $objectName): string
    {
        $this->PersistentDataService->restore($objectName);
        $this->databaseService->restore($objectName);
        $this->unlinkTemporaryFiles($objectName);
        return 'backup ' . $objectName . ' restored.';
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function restorePersistentData(string $objectName): string
    {
        $this->PersistentDataService->restore($objectName);
        $this->unlinkTemporaryFiles($objectName);
        return 'persistent data backup ' . $objectName . ' restored.';
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function restoreDatabase(string $objectName): string
    {
        $this->databaseService->restore($objectName);
        $this->unlinkTemporaryFiles($objectName);
        return 'database backup ' . $objectName . ' restored.';
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function delete(string $objectName): string
    {
        $this->googleCloudStorageService->delete($objectName);
        return $objectName;
    }

    /**
     * @param string $objectName
     * @return void
     */
    private function unlinkTemporaryFiles(string $objectName): void
    {
        unlink($this->getTemporaryPath() . 'Data.zip');
        unlink($this->getTemporaryPath() . $objectName);
        unlink($this->getTemporaryPath() . 'Database.sql');
    }

    /**
     * @return string
     */
    public function generateLatestBackupUrl(): string
    {
        return $this->googleCloudStorageService->generateLatestObjectUrl($this->settings['backup_identfier']);
    }

}

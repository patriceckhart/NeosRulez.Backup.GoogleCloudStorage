<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Service;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

use wapmorgan\UnifiedArchive\UnifiedArchive;

/**
 *
 * @Flow\Scope("singleton")
 */
class PersistentDataService extends AbstractService
{

    /**
     * @return string
     */
    public function create(): string
    {
        $backupSource = $this->getTemporaryPath() . 'Data.zip';
        $persistentData = constant('FLOW_PATH_DATA');
        UnifiedArchive::archiveFiles(['Data/' => $persistentData], $backupSource);
        return $backupSource;
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function restore(string $objectName): string
    {
        $backup = $this->googleCloudStorageService->restore($objectName);
        $archive = UnifiedArchive::open($this->getTemporaryPath() . $objectName);
        if (!empty($backup)) {
            $outputDir = $this->getTemporaryPath();
            if (disk_free_space($outputDir) > $archive->getOriginalSize()) {
                $archive->extractFiles($outputDir);
                $data = UnifiedArchive::open($this->getTemporaryPath() . 'Data.zip');
                if (disk_free_space(constant('FLOW_PATH_DATA')) > $data->getOriginalSize()) {
                    $data->extractFiles(constant('FLOW_PATH_DATA'));
                }
            } else {
                return 'Not enough disk space! Disk: ' . disk_free_space($outputDir) . ', Backup: ' . $archive->getOriginalSize();
            }
        }
        return '';
    }

}

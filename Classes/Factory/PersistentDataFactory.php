<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Factory;

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
class PersistentDataFactory {

    /**
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Service\GoogleCloudStorageService
     */
    protected $googleCloudStorageService;

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
     * @return string
     */
    public function create():string
    {
        $tempDir = sys_get_temp_dir();
        $backupFilename = 'PersistentData.zip';
        $backupSource = $tempDir . '/' . $backupFilename;
        $persistentData = constant('FLOW_PATH_ROOT') . 'Data/';
        UnifiedArchive::archiveFiles(['Data' => $persistentData], $backupSource);
        return $backupSource;
    }

    /**
     * @param string $objectName
     * @return string
     */
    function restore(string $objectName):string
    {
        $backup = $this->googleCloudStorageService->restore($objectName);
        $archive = UnifiedArchive::open(sys_get_temp_dir() . '/' . $objectName);
        $result = '';
        if ($backup !== null) {
            $outputDir = sys_get_temp_dir();
            if (disk_free_space($outputDir) > $archive->getOriginalSize()) {
                $archive->extractFiles($outputDir);
                $data = UnifiedArchive::open(sys_get_temp_dir() . '/' . 'PersistentData.zip');
                if (disk_free_space(constant('FLOW_PATH_ROOT')) > $data->getOriginalSize()) {
                    $data->extractFiles(constant('FLOW_PATH_ROOT'));
                }
            } else {
                $result = 'Not enough disk space! Disk: ' . disk_free_space($outputDir) . ', Backup: ' . $archive->getOriginalSize();
            }
        }
        return $result;
    }

}

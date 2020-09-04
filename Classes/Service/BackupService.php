<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Service;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Google\Cloud\Storage\StorageClient;

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
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Factory\PersistentDataFactory
     */
    protected $persistentDataFactory;

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
     * @return array
     */
    public function createBackup() {

        $database = $this->databaseFactory->createDatabaseBackup();

        $backup_filename = $this->settings['backup_identfier'] . '_' . date('Y-m-d_H-i-s').'.tar.gz';
        shell_exec('tar -cf ' . $backup_filename . ' ' . $database . ' ' . constant('FLOW_PATH_ROOT') . 'Data/');

        $this->upload($this->settings['storage_bucket_name'], $backup_filename, constant('FLOW_PATH_ROOT') . $backup_filename);

        shell_exec('rm -rf ' . constant('FLOW_PATH_ROOT') . $backup_filename);

        $result = ['file' => $backup_filename, 'bucket' => $this->settings['storage_bucket_name']];

        return $result;

    }

    /**
     * @param string $bucketName
     * @param string $objectName
     * @param string $source
     */
    function upload($bucketName, $objectName, $source) {
        $storage = $this->storage();
        $file = fopen($source, 'r');
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($file, [
            'name' => $objectName
        ]);
    }

    /**
     * @param string $objectName
     */
    function restore($objectName) {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $object = $bucket->object($objectName);
        $object->downloadToFile(constant('FLOW_PATH_ROOT') . $objectName);
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && tar -xvf ' . $objectName);
        $this->databaseFactory->restoreDatabaseBackup();
        $this->persistentDataFactory->restorePersistentDataBackup($objectName);
        $this->deleteTmp($objectName);
    }

    /**
     * @param string $objectName
     */
    function restorePersistentData($objectName) {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $object = $bucket->object($objectName);
        $object->downloadToFile(constant('FLOW_PATH_ROOT') . $objectName);
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && tar -xvf ' . $objectName);
        $this->persistentDataFactory->restorePersistentDataBackup($objectName);
        $this->deleteTmp($objectName);
    }

    /**
     * @param string $objectName
     */
    function restoreDatabase($objectName) {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $object = $bucket->object($objectName);
        $object->downloadToFile(constant('FLOW_PATH_ROOT') . $objectName);
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && tar -xvf ' . $objectName);
        $this->databaseFactory->restoreDatabaseBackup();
        $this->deleteTmp($objectName);
    }

    /**
     * @param string $objectName
     */
    function download($objectName) {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $object = $bucket->object($objectName);
        $object->downloadToFile(constant('FLOW_PATH_ROOT') . $objectName);
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && mv ' . $objectName . ' ' . constant('FLOW_PATH_ROOT') . 'Web/' . $objectName);
    }

    /**
     * @param string $objectName
     * @return string
     */
    function delete($objectName) {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $object = $bucket->object($objectName);
        $object->delete();
        return $this->settings['storage_bucket_name'];
    }

    /**
     * @param string $objectName
     * @return void
     */
    function deleteTmp($objectName) {
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && rm -rf ' . $objectName);
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && rm -rf data');
    }



}

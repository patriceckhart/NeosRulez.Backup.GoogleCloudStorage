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
class GoogleCloudStorageService {

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
     * @return StorageClient
     */
    public function storage() {
        $storage = new StorageClient([
            'keyFilePath' => $this->settings['key_file_path']
        ]);
        return $storage;
    }

    /**
     * @param string $objectName
     * @param string $source
     * @return bool
     */
    function upload(string $objectName, string $source):bool
    {
        $storage = $this->storage();
        $file = fopen($source, 'r');
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $bucket->upload($file, [
            'name' => $objectName
        ]);
        return true;
    }

    /**
     * @param string $objectName
     * @return bool
     */
    function delete(string $objectName):bool
    {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $object = $bucket->object($objectName);
        $object->delete();
        return true;
    }

    /**
     * @param string $objectName
     * @return string
     */
    function restore(string $objectName):bool
    {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $object = $bucket->object($objectName);
        $object->downloadToFile(sys_get_temp_dir() . '/' . $objectName);
        return true;
    }

    /**
     * @param string $prefix
     * @return string
     */
    public function generateLatestObjectUrl(string $prefix): string
    {
        $storage = $this->storage();
        $bucket = $storage->bucket($this->settings['storage_bucket_name']);
        $lastObject = null;
        /* @var StorageObject $object */
        foreach ($bucket->objects(['prefix' => $prefix]) as $object) {
            $lastObject = $object;
        }
        if ($lastObject !== null) {
            return $lastObject->signedUrl(new \DateTime('+ 600 seconds'));
        }
        return '';
    }

}

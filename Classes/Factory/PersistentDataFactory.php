<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Factory;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @Flow\Scope("singleton")
 */
class PersistentDataFactory {

//    /**
//     * @return string
//     */
//    public function createPersistentDataBackup() {
//
//        $credentials = $this->databaseFactory->getDatabaseCredentials();
//
//        $databackup_filename = constant('FLOW_PATH_ROOT') . $credentials['dbname'].'_'.date('Y-m-d_H-i-s').'_data.tar.gz';
//        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && tar -zcvf '.$databackup_filename.' Data');
//
//        return $databackup_filename;
//
//    }

    /**
     * @param string $objectName
     * @return void
     */
    public function restorePersistentDataBackup($objectName) {
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && rm -rf Data');
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . 'data/neos && mv Data ' . constant('FLOW_PATH_ROOT') . 'Data');
    }


}

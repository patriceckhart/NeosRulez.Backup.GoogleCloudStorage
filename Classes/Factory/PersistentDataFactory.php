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

    /**
     * @param string $objectName
     * @return void
     */
    public function restorePersistentDataBackup($objectName) {
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . ' && rm -rf Data');
        shell_exec('cd ' . constant('FLOW_PATH_ROOT') . 'data/neos && mv Data ' . constant('FLOW_PATH_ROOT') . 'Data');
    }

}

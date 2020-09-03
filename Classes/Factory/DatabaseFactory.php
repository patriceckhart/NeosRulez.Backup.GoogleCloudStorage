<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Factory;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Ifsnop\Mysqldump as IMysqldump;

/**
 *
 * @Flow\Scope("singleton")
 */
class DatabaseFactory {

    /**
     * @return string
     */
    public function createDatabaseBackup() {
        $credentials = $this->getDatabaseCredentials();
        try {
            $dump = new IMysqldump\Mysqldump('mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['dbname'] . '', $credentials['user'], $credentials['password']);
            $dump_file = constant('FLOW_PATH_ROOT') . $credentials['dbname'] . '_' . date('Y-m-d_H-i-s') . '.sql';
            $dump->start($dump_file);
            return $dump_file;
        } catch (\Exception $e) {
            print('mysqldump-php error: ' . $e->getMessage());
        }
    }

    /**
     *
     * @return void
     */
    public function getDatabaseCredentials() {
        $flow_rootpath = constant('FLOW_PATH_ROOT');
        $configuration = shell_exec('cd ' . $flow_rootpath . ' && ./flow configuration:show');
        $configuration_yaml = yaml_parse($configuration);
        $yaml_parse = $configuration_yaml['Neos']['Flow']['persistence']['backendOptions'];
        return $yaml_parse;
    }

}

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
class DatabaseFactory {

    /**
     *
     * @return void
     */
    public function createDatabaseBackup() {

        $credentials = $this->getDatabaseCredentials();


//        return $credentials;
//        return exec('mysqldump --host '. $credentials['host'] .' --user '. $credentials['user'] .' --password '. $credentials['password'] .' '. $credentials['dbname'] .' --result-file='.constant('FLOW_PATH_ROOT').'test.sql') === 0;
//        $dump_file = constant('FLOW_PATH_ROOT') . $credentials['dbname'] . '_' . date('Y-m-d_H-i-s') . '.sql.gz';
//        passthru("mysqldump --user=" . $credentials['user'] ." --password=" . $credentials['password'] ." --host=" . $credentials['host'] ." | gzip -c  > $dump_file");
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
//        $db_host = $yaml_parse['host'];
//        $db_name = $yaml_parse['dbname'];
//        $db_user = $yaml_parse['user'];
//        $db_password = $yaml_parse['password'];
        return $yaml_parse;
    }


}

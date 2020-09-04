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
        } catch (\Exception $e) {
            print('mysqldump-php error: ' . $e->getMessage());
            $dump_file = false;
        }
        return $dump_file;
    }

    /**
     * @return void
     */
    public function restoreDatabaseBackup() {

        $sqlfile = [];
        $dir = constant('FLOW_PATH_ROOT') . 'data/neos';
        if (is_dir($dir)){
            if ($dh = opendir($dir)){
                while (($file = readdir($dh)) !== false){
                    $sqlfile[] = $file;
                }
                closedir($dh);
            }
        }

        $credentials = $this->getDatabaseCredentials();
        $pdo = new \PDO('mysql:host=' . $credentials['host'] . ';dbname=' . $credentials['dbname'] .'', $credentials['user'],  $credentials['password']);
        $this->importSqlFile($pdo, constant('FLOW_PATH_ROOT') . 'data/neos/' . $sqlfile[1]);

    }

    /**
     *
     * @return array
     */
    public function getDatabaseCredentials() {
        $flow_rootpath = constant('FLOW_PATH_ROOT');
        $configuration = shell_exec('cd ' . $flow_rootpath . ' && ./flow configuration:show');
        $configuration_yaml = yaml_parse($configuration);
        $yaml_parse = $configuration_yaml['Neos']['Flow']['persistence']['backendOptions'];
        return $yaml_parse;
    }

    /**
     * Import SQL File
     *
     * @param $pdo
     * @param $sqlFile
     * @param null $tablePrefix
     * @param null $InFilePath
     * @return bool
     */
    function importSqlFile($pdo, $sqlFile, $tablePrefix = null, $InFilePath = null) {
        try {
            $pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
            $errorDetect = false;
            $tmpLine = '';
            $lines = file($sqlFile);
            foreach ($lines as $line) {
                if (substr($line, 0, 2) == '--' || trim($line) == '') {
                    continue;
                }
                $line = str_replace(['<<prefix>>', '<<InFilePath>>'], [$tablePrefix, $InFilePath], $line);
                $tmpLine .= $line;
                if (substr(trim($line), -1, 1) == ';') {
                    try {
                        $pdo->exec($tmpLine);
                    } catch (\PDOException $e) {
                        print('Error performing Query: ' . $tmpLine . ' ' . $e->getMessage());
                        $errorDetect = true;
                    }

                    $tmpLine = '';
                }
            }

            if ($errorDetect) {
                return false;
            }

        } catch (\Exception $e) {
            print($e->getMessage());
            return false;
        }

        return true;
    }

}

<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Factory;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Configuration\ConfigurationManager;
use wapmorgan\UnifiedArchive\UnifiedArchive;
use Ifsnop\Mysqldump as IMysqldump;

/**
 *
 * @Flow\Scope("singleton")
 */
class DatabaseFactory {

    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @Flow\Inject
     * @var \NeosRulez\Backup\GoogleCloudStorage\Service\GoogleCloudStorageService
     */
    protected $googleCloudStorageService;


    /**
     * @return string
     */
    public function create():string
    {
        $backendOptions = $this->getConfiguration();
        $dumpFile = '';
        if(!empty($backendOptions)) {
            try {
                $dump = new IMysqldump\Mysqldump('mysql:host=' . $backendOptions['host'] . ';dbname=' . $backendOptions['dbname'] . '', $backendOptions['user'], $backendOptions['password']);
                $dumpFile = sys_get_temp_dir() . '/' . $backendOptions['dbname'] . '_' . date('Y-m-d_H-i-s') . '.sql';
                $dump->start($dumpFile);
            } catch (\Exception $e) {
                print('mysqldump-php error: ' . $e->getMessage());
            }
        }
        return $dumpFile;
    }

    /**
     * @param string $objectName
     * @return string
     */
    public function restore(string $objectName):string
    {
        $backup = $this->googleCloudStorageService->restore($objectName);
        $archive = UnifiedArchive::open(sys_get_temp_dir() . '/' . $objectName);
        $backendOptions = $this->getConfiguration();
        $result = '';
        if ($backup !== null && !empty($backendOptions)) {
            $outputDir = sys_get_temp_dir();
            if (disk_free_space($outputDir) > $archive->getOriginalSize()) {
                $archive->extractFiles($outputDir);
                foreach ($archive->getFileNames() as $file) {
                    if($file == 'Database.sql') {
                        $pdo = new \PDO('mysql:host=' . $backendOptions['host'], $backendOptions['user'], $backendOptions['password']);
                        $this->dropDatabase($pdo, $backendOptions['dbname']);
                        $this->createDatabase($pdo, $backendOptions['dbname']);
                        $pdo = new \PDO('mysql:host=' . $backendOptions['host'] . ';dbname=' . $backendOptions['dbname'], $backendOptions['user'], $backendOptions['password']);
                        $this->importSqlFile($pdo, sys_get_temp_dir() . '/' . $file);

                        break;
                    }
                }
            } else {
                $result = 'Not enough disk space! Disk: ' . disk_free_space($outputDir) . ', Backup: ' . $archive->getOriginalSize();
            }
        }
        return $result;
    }

    /**
     *
     * @return array
     */
    public function getConfiguration():array
    {
        $availableConfigurationTypes = $this->configurationManager->getAvailableConfigurationTypes();
        $backendOptions = [];
        if (in_array('Settings', $availableConfigurationTypes)) {
            $configuration = $this->configurationManager->getConfiguration('Settings');
            if (array_key_exists('Neos', $configuration)) {
                if (array_key_exists('Flow', $configuration['Neos'])) {
                    if (array_key_exists('persistence', $configuration['Neos']['Flow'])) {
                        if (array_key_exists('backendOptions', $configuration['Neos']['Flow']['persistence'])) {
                            $backendOptions = $configuration['Neos']['Flow']['persistence']['backendOptions'];
                        }
                    }
                }
            }
        }
        return $backendOptions;
    }

    /**
     * @param $pdo
     * @param string $database
     * @return void
     */
    public function dropDatabase($pdo, string $database):void
    {
        $drop = 'DROP DATABASE ' . $database;
        $pdo->query($drop);
    }

    /**
     * @param $pdo
     * @param string $database
     * @return void
     */
    public function createDatabase($pdo, string $database):void
    {
        $create = 'CREATE DATABASE ' . $database;
        $pdo->query($create);
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
    function importSqlFile($pdo, $sqlFile, $tablePrefix = null, $InFilePath = null):bool {
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

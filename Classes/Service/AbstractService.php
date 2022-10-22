<?php
namespace NeosRulez\Backup\GoogleCloudStorage\Service;

/*
 * This file is part of the NeosRulez.Backup.GoogleCloudStorage package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Configuration\ConfigurationManager;

use NeosRulez\Backup\GoogleCloudStorage\Service\DatabaseService;
use NeosRulez\Backup\GoogleCloudStorage\Service\PersistentDataService;
use NeosRulez\Backup\GoogleCloudStorage\Service\GoogleCloudStorageService;

/**
 *
 * @Flow\Scope("singleton")
 */
abstract class AbstractService
{

    /**
     * @Flow\Inject
     * @var GoogleCloudStorageService
     */
    protected $googleCloudStorageService;

    /**
     * @Flow\Inject
     * @var PersistentDataService
     */
    protected $PersistentDataService;

    /**
     * @Flow\Inject
     * @var DatabaseService
     */
    protected $databaseService;

    /**
     * @Flow\Inject
     * @var ConfigurationManager
     */
    protected $configurationManager;

    /**
     * @var array
     */
    protected $settings;

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return string
     */
    public function getTemporaryPath(): string
    {
        return sys_get_temp_dir() . '/';
    }

}

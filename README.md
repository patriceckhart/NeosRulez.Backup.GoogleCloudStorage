# Neos CMS and Neos Flow Google Cloud Storage Backup

A package for Neos CMS and Neos Flow to create and restore backups on and from Google Cloud Storage.

## Installation

The NeosRulez.Backup.GoogleCloudStorage package is listed on packagist (https://packagist.org/packages/neosrulez/backup-googlecloudstorage) - therefore you don't have to include the package in your "repositories" entry any more.

Just run:

```
composer require neosrulez/backup-googlecloudstorage
```

## Settings.yaml

Define an individual identifier for your backups, a Google Cloud Storage Bucket and specify the storage location of the credential .json:

```
NeosRulez:
  Backup:
    GoogleCloudStorage:
      backup_identfier: 'backup' # your own backup identifier (extend filename on storage)
      storage_bucket_name: 'my_neos_backups'
      key_file_path: '/var/www/html/Packages/Sites/Acme.Site/Resources/Private/.credentials/credential.json'
```

## CLI

| CLI command | Action |
|---------|-------------|
|./flow backup:create|Create backup on Google Cloud Storage|
|./flow backup:create --name `custom`|Create custom named backup in Google Cloud Storage|
|./flow backup:restore `name_of_the_backup.tar.gz`|Restore backup from Google Cloud Storage (can't be undone!)|
|./flow backup:restoredata|Restore persistent data backup from Google Cloud Storage (can't be undone!)|
|./flow backup:restoredatabase|Restore persistent data backup from Google Cloud Storage (can't be undone!)
|./flow backup:delete|Delete backup on Google Cloud Storage (can't be undone!)|

## Author

* E-Mail: mail@patriceckhart.com
* URL: http://www.patriceckhart.com

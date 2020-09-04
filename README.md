# Neos CMS and Neos Flow Google Cloud Storage Backup

A package for Neos CMS and Neos Flow to create in Google Cloud Storage and restore.

## Installation

The NeosRulez.Backup.GoogleCloudStorage package is listed on packagist (https://packagist.org/packages/neosrulez/backup-googlecloudstorage) - therefore you don't have to include the package in your "repositories" entry any more.

Just add the following line to your require section:

```
"neosrulez/backup-googlecloudstorage": "*"
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

## Author

* E-Mail: mail@patriceckhart.com
* URL: http://www.patriceckhart.com

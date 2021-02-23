Sync Translation Files
======================

A command for synchronize all Symfony translation files according to one locale file (etc. `messages.en.yaml`).

Supports YAML format only.

Install
-------

Step 1: Download the package

`$ composer require hricer/sync-translations --dev`

Step 2: Regist the Bundle

```php
// bundles.php
return [
    // ...
    Hricer\SyncTranslations\SyncTranslationsBundle::class => ['dev' => true],
];
```

### Without Symfony Bundle system

Create a console application with translation:sync as its only command:

```php
// bin/translation.php
use Symfony\Component\Console\Application;
use Hricer\SyncTranslations\Command\SyncTranslationCommand;

(new Application('translation/sync'))
    ->add(new SyncTranslationCommand())
    ->getApplication()
    ->setDefaultCommand('translation:sync', true)
    ->run();
```

Example
-------

Usage
-----

In terminal:
```
$ php bin/console translation:sync en --domain=messages
```

It will synchronize all `translations/*.messages.yaml` files by `translations/en.messages.yaml`.

| Options        | Default        | Description  |
| ------------- |:--------------:| -----:|
| `--domain`    | `*` (all)      | The translation domain name to synchronize. |
| `--directory` | `translations` | Directory with translation files. |
| `--format`    | `yaml`         | Only YAML supported. |

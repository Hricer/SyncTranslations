Sync Translation Files
======================

A command for synchronize all Symfony translation files according to one locale file.

Supports YAML format only.

Usage
-----

Imagine you make changes in your main locale, e.g. `message.en.yaml`:

```diff
common:
    yes: Yes
+   no: No
    actions:
        close: Close
-       send: Send
    toast:
        save: 'Successfully saved.'
```

Now you need synchronize all `translations/messages.*.yaml` files by `translations/messages.en.yaml` (add new or remove old lines). Type in terminal:
```
$ php bin/console translation:sync en --domain=messages
```

| Options       | Default        | Description  |
| ------------- |----------------| ------------ |
| `--domain`    | `*` (all)      | The translation domain name to synchronize. |
| `--directory` | `translations` | Directory with translation files. |
| `--format`    | `yaml`         | Only YAML supported. |


Command will update all `message.*.yaml` (exclude en). For exmaple `message.cs.yaml` :


```diff
common:
    yes: Ano
+   no: No
    actions:
        close: Zavřít
-       send: Odeslat
    toast:
        save: 'Úspešně uloženo.'
```


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

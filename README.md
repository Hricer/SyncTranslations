Sync Translation Files
======================

The command for synchronize all translation files according to one locale file. This will ensure that all translation files contain the same keys.

This allows the developer to manage only one language. Before deploy the application into production,
the developer executes synchronization for other languages.

Usage
-----

Imagine you made changes to the main locale file, e.g. `messages.en.yaml`:

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

Now you need synchronize all `messages.*.yaml` files by `messages.en.yaml` (add new or remove old lines). Type into the terminal:
```
$ php bin/console translation:sync en --domain=messages
```

| Options       | Default        | Description                                  |
|---------------|----------------|----------------------------------------------|
| `--domain`    | `*` (all)      | The translation domain name to synchronize.  |
| `--directory` | `translations` | Directory with translation files.            |
| `--format`    | `yaml`         | Only YAML supported.                         |
| `--deepl`     | `null`         | Optional DeepL API key for auto translation. |


The command will update all `messages.*.yaml` (exclude en). 

For exmaple `message.cs.yaml` :

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

You can use `--deepl` parameter for auto translate all new lines.


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

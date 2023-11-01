<?php

namespace Hricer\SyncTranslations\DependencyInjection;

use Hricer\SyncTranslations\Command\SyncTranslationCommand;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class SyncTranslationsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $container->register(SyncTranslationCommand::class)
            ->addTag('console.command');
    }
}

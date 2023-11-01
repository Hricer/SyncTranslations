<?php declare(strict_types=1);

namespace Hricer\SyncTranslations\Command;

use Hricer\SyncTranslations\Comparator;
use Hricer\SyncTranslations\File;
use Hricer\SyncTranslations\Finder;
use Hricer\SyncTranslations\Synchronizer;
use Hricer\SyncTranslations\TranslatorManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('translation:sync', description: 'Synchronize all translation files by one.')]
class SyncTranslationCommand extends Command
{
    protected function configure(): void
    {
        $this->addArgument('locale', InputArgument::REQUIRED, 'Synchronize by this locale.');
        $this->addOption('directory', 'd', InputOption::VALUE_OPTIONAL, 'Translation files directory.', 'translations');
        $this->addOption('domain', null, InputOption::VALUE_OPTIONAL, 'Translation domain to process. Asterisk means all domains.', '*');
        $this->addOption('format', null, InputOption::VALUE_OPTIONAL, 'File format.', 'yaml');
        $this->addOption('deepl', null, InputOption::VALUE_OPTIONAL, 'DeepL api key.', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $_locale = $input->getArgument('locale');
        $_directory = $input->getOption('directory');
        $_domain = $input->getOption('domain');
        $_format = $input->getOption('format');
        $_deepl = $input->getOption('deepl');

        $translator = $_deepl ? new TranslatorManager($_deepl) : null;

        $io = new SymfonyStyle($input, $output);
        $io->title('Synchronize following files');
        $io->definitionList(
            ['Locale' => $_locale],
            ['Directory' => $_directory],
            ['Domain' => $_domain],
            ['DeepL auto translate' => $translator ? sprintf('yes (languages: %s)', implode(', ', $translator->getSupportedLanguages())) : 'no' ],
        );

        $io->section('Files:');

        $finder = new Finder($_directory, $_domain, $_format);
        $files = $finder->findFiles($_locale);

        if (empty($files)) {
            $io->warning('Nothing to change.');

            return Command::SUCCESS;
        }

        foreach ($files as $master) {
            $io->write(sprintf('  <info>%s</info>: ', basename($master->path)));

            foreach ($master->slaves as $slave) {
                $io->write(basename($slave->path).' SyncTranslationCommand.php');
            }

            $io->newLine();
        }

        $io->newLine(2);

        if (!$io->confirm('Continue?', true)) {
            $io->caution('Canceled.');

            return Command::SUCCESS;
        }

        $io->section('Syncing...');

        $sync = new Synchronizer($finder, $_locale, $translator);
        $sync->setUpdatedCallback(function (Comparator $comparator, File $file) use ($io) {
            $added = count($comparator->getAdded());
            $io->text(sprintf(' %s <info>[Done]</info> [%s]', basename($file->path), $added > 0 ? $added .' added' : 'No change'));
        });

        $sync->run();

        $io->success('Success.');

        return Command::SUCCESS;
    }
}

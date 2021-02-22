<?php declare(strict_types=1);

namespace Hricer\SyncTranslations\Command;

use Hricer\SyncTranslations\Finder;
use Hricer\SyncTranslations\SyncTranslations;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class TranslationCommand extends Command
{
    protected static $defaultName = 'translation:sync';

    protected function configure()
    {
        $this->setDescription('Synchronize all translation files by one.');

        $this->addArgument('locale', InputArgument::REQUIRED, 'Synchronize by this locale.');
        $this->addOption('directory', 'd', InputOption::VALUE_OPTIONAL, 'Translation files directory.', 'translations');
        $this->addOption('domain', null, InputOption::VALUE_OPTIONAL, 'Translation domain to process. Asterisk means all domains.', '*');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $_directory = $input->getOption('directory');
        $_domain = $input->getOption('domain');
        $_locale = $input->getArgument('locale');

        $output->writeln('<info>Generate translation files in directory.</info>');
        $output->writeln('<comment>Directory:</comment> '.$_directory);
        $output->write('<comment>Files to change:</comment> ');

        $finder = new Finder($_directory, $_domain, 'yaml');
        $files = $finder->findFiles($_locale);

        if (empty($files)) {
            $output->writeln('Nothing to change.');

            return 1;
        }

        foreach ($files as $fileBase) {
            foreach ($fileBase as $file) {
                $output->write(basename($file).' ');
            }
        }

        $output->writeln('');
        $output->writeln('');

        $question = new ConfirmationQuestion('Continue with change files (y/n)?', true);
        $helper = $this->getHelper('question');

        if (!$helper->ask($input, $output, $question)) {
            $output->writeln('<info>Canceled.</info>');

            return 1;
        }

        $output->writeln('<info>Syncing...</info>');

        $sync = new SyncTranslations($finder, 'en');
        $sync->setUpdatedCallback(function ($master, $slave) use ($output) {
            $output->writeLn('<comment>'.basename($slave)."</comment> <info>updated by</info> ".basename($master));
        });
        $sync->run();

        $output->writeln('<info>Done.</info>');

        return 0;
    }
}

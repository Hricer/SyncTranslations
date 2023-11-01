<?php declare(strict_types=1);

namespace Hricer\SyncTranslations;

use Exception;

class Finder
{
    const FORMATS = '{yml,yaml}';
    const MASK = '%s/%s.%s.%s';

    private string $directory;
    private string $domain;
    private string $format;
    private array $files;

    public function __construct(string $directory, string $domain, string $format = 'yaml')
    {
        $this->files = [];
        $this->directory = $directory;
        $this->domain = $domain;

        if ($format != 'yaml') {
            Finder::throwFormatException();
        }

        $this->format = $format;
    }

    static function throwFormatException(): never
    {
        throw new Exception('Only Yaml format supported.');
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return File[]
     */
    public function findFiles(string $locale): array
    {
        if (!isset($this->files[$locale])) {
            $glob = sprintf(self::MASK, $this->directory, $this->domain, '*', self::FORMATS);
            $files = [];

            foreach (glob($glob, GLOB_BRACE) as $file) {
                preg_match('/^([a-zA-Z0-9_\-+]+)\.([a-z]{2})\.([a-z]+)$/', basename($file), $matches);

                if (empty($matches)) {
                    continue;
                }

                $files[] = new File($file, $matches[1], $matches[2], strtolower($matches[3]), $matches[2] === $locale);
            }

            foreach ($this->findMasters($files) as $master) {
                foreach ($this->findSlavesForDomain($files, $master->domain) as $slave) {
                    $master->addSlave($slave);
                }

                $this->files[$locale][] = $master;
            }
        }

        return $this->files[$locale];
    }

    /**
     * @param File[] $files
     * @return File[]
     */
    private function findMasters(array $files): array
    {
        return array_filter($files, fn (File $file) => $file->master);
    }

    /**
     * @param File[] $files
     * @return File[]
     */
    private function findSlavesForDomain(array $files, string $domain): array
    {
        return array_filter($files, fn (File $file) => !$file->master && $file->domain === $domain);
    }
}

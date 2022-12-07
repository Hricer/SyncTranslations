<?php declare(strict_types=1);

namespace Hricer\SyncTranslations;

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
            throw new \Exception('Only Yaml format supported.');
        }

        $this->format = $format;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function findFiles(string $locale): array
    {
        if (!isset($this->files[$locale])) {
            $glob = sprintf(self::MASK, $this->directory, $this->domain, '*', self::FORMATS);

            $masters = $slaves = [];

            foreach (glob($glob, GLOB_BRACE) as $file) {
                preg_match('/^([a-zA-Z0-9_\-+]+)\.([a-z]{2})\.([a-z]+)$/', basename($file), $matches);

                if (empty($matches)) {
                    continue;
                }

                if ($matches[2] == $locale) {
                    $masters[$matches[1]] = $file;
                } else {
                    $slaves[$matches[1]][] = $file;
                }
            }

            foreach ($masters as $domain => $master) {
                $this->files[$locale][$master] = $slaves[$domain] ?? [];
            }
        }

        return $this->files[$locale];
    }
}

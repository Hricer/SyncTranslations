<?php declare(strict_types=1);

namespace Hricer\SyncTranslations;

use Symfony\Component\Yaml\Yaml;

class Synchronizer
{
    private Finder $finder;
    private string $locale;

    /**
     * @var callable
     */
    private $createdEvent;

    public function __construct(Finder $finder, string $locale = 'en')
    {
        $this->finder = $finder;
        $this->locale = $locale;
    }

    public function setUpdatedCallback(callable $updated)
    {
        $this->createdEvent = $updated;
    }

    public function run()
    {
        foreach ($this->finder->findFiles($this->locale) as $master => $slaves) {
            $masterTranslation = $this->parseFile($master);

            foreach ($slaves as $slave) {
                $synchronized = $this->compareNodes($masterTranslation, $this->parseFile($slave));
                file_put_contents($slave, $this->dump($synchronized));

                if ($this->createdEvent) {
                    ($this->createdEvent)($master, $slave);
                }
            }
        }
    }

    private function parseFile(string $file)
    {
        switch ($this->finder->getFormat()) {
            case 'yaml':
                return Yaml::parseFile($file);
        }
    }

    private function dump(array $content)
    {
        switch ($this->finder->getFormat()) {
            case 'yaml':
                return Yaml::dump($content, 4);
        }
    }

    private function compareNodes(array $master, array $slave): array
    {
        $new = [];

        foreach ($master as $masterKey => $masterValue) {
            if (isset($slave[$masterKey])) {
                if (is_array($masterValue)) {
                    if (is_array($slave[$masterKey])) {
                        $new[$masterKey] = $this->compareNodes($masterValue, $slave[$masterKey]);
                    } else {
                        $new[$masterKey] = $this->compareNodes($masterValue, []);
                    }
                } else {
                    if (is_array($slave[$masterKey])) {
                        $new[$masterKey] = $masterValue;
                    } else {
                        $new[$masterKey] = $slave[$masterKey];
                    }
                }
            } else {
                $new[$masterKey] = $masterValue;
            }
        }

        return $new;
    }
}

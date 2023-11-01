<?php declare(strict_types=1);

namespace Hricer\SyncTranslations;

use Closure;
use Symfony\Component\Yaml\Yaml;

class Synchronizer
{
    private ?Closure $eventDone = null;

    public function __construct(
        private Finder $finder,
        private string $locale = 'en',
        private ?TranslatorManager $translatorManager = null,
    ) {
    }

    public function setUpdatedCallback(Closure $updated): void
    {
        $this->eventDone = $updated;
    }

    public function run(): void
    {
        foreach ($this->finder->findFiles($this->locale) as $master) {
            $masterTranslation = $this->parseFile($master->path);

            foreach ($master->slaves as $slave) {
                $comparator = new Comparator();
                $synchronized = $comparator->compare($masterTranslation, $this->parseFile($slave->path));

                if ($this->translatorManager) {
                    $this->translatorManager->translateValues($comparator->getAdded(), $this->locale, $slave->locale);
                }

                array_walk_recursive($synchronized, function (&$item) {
                    if ($item instanceof Value) {
                        $item = $item->getTranslation() ?? $item->getValue();
                    }
                });

                file_put_contents($slave->path, $this->dump($synchronized));

                if ($this->eventDone) {
                    ($this->eventDone)($comparator, $slave);
                }
            }
        }
    }

    private function parseFile(string $file): array
    {
        return match ($this->finder->getFormat()) {
            'yaml' => Yaml::parseFile($file),
            default => Finder::throwFormatException(),
        };
    }

    private function dump(array $content): string
    {
        return match ($this->finder->getFormat()) {
            'yaml' => Yaml::dump($content, 4, 4, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK),
            default => Finder::throwFormatException(),
        };
    }
}

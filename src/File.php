<?php

namespace Hricer\SyncTranslations;

class File
{
    /**
     * @var File[]
     */
    public array $slaves;

    public function __construct(
        public string $path,
        public string $domain,
        public string $locale,
        public string $format,
        public bool $master = false,
    ) {
    }

    public function addSlave(File $slave): void
    {
        if (!$this->master) {
            throw new \Exception('A slave cannot own a slave.');
        }

        $this->slaves[] = $slave;
    }
}

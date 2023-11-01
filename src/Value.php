<?php

namespace Hricer\SyncTranslations;

class Value
{
    private ?string $translation = null;

    public function __construct(public string $value)
    {
    }

    public function setTranslation(string $translation): void
    {
        $this->translation = $translation;
    }

    public function getTranslation(): ?string
    {
        return $this->translation;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}

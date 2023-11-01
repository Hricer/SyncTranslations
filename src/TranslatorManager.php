<?php declare(strict_types=1);

namespace Hricer\SyncTranslations;

use DeepL\DeepLException;
use DeepL\Language;
use DeepL\Translator;

class TranslatorManager
{
    public Translator $deepL;

    /**
     * @var string[]
     */
    private ?array $supportedLanguages = null;

    public function __construct(string $apiKey)
    {
        $this->deepL = new Translator($apiKey);
    }

    public function getSupportedLanguages(): array
    {
        if (!$this->supportedLanguages) {
            $this->supportedLanguages = array_map(fn (Language $language) => $language->code, $this->deepL->getTargetLanguages());
        }

        return $this->supportedLanguages;
    }

    /**
     * @param Value[] $values
     * @throws DeepLException
     */
    public function translateValues(array $values, string $sourceLanguage, string $targetLanguage): void
    {
        if (count($values) <= 0) {
            return;
        }

        if (!in_array($targetLanguage, $this->getSupportedLanguages(), true)) {
            return;
        }

        $texts = array_map(fn (Value $value) => $value->value, $values);

        $translations = $this->deepL->translateText($texts, $sourceLanguage, $targetLanguage);

        foreach ($translations as $i => $translation) {
            $values[$i]->setTranslation($translation->text);
        }
    }
}

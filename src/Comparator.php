<?php declare(strict_types=1);

namespace Hricer\SyncTranslations;

class Comparator
{
    /**
     * @var Value[]
     */
    private array $added = [];

    public function compare(array $master, array $slave): array
    {
        $new = [];

        foreach ($master as $masterKey => $masterValue) {
            if (isset($slave[$masterKey])) {
                if (is_array($masterValue)) {
                    if (is_array($slave[$masterKey])) {
                        $new[$masterKey] = $this->compare($masterValue, $slave[$masterKey]);
                    } else {
                        $new[$masterKey] = $this->compare($masterValue, []);
                    }
                } else {
                    if (is_array($slave[$masterKey])) {
                        $this->added[] = $new[$masterKey] = new Value($masterValue);
                    } else {
                        $new[$masterKey] = $slave[$masterKey];
                    }
                }
            } else {
                if (is_array($masterValue)) {
                    $new[$masterKey] = $this->compare($masterValue, []);
                } else {
                    $this->added[] = $new[$masterKey] = new Value($masterValue);
                }
            }
        }

        return $new;
    }

    public function getAdded(): array
    {
        return $this->added;
    }
}

<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace GibsonOS\Core\Service;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Enum\NewRelicPrefix;
use GibsonOS\Core\Utility\JsonUtility;

class NewRelicService
{
    public function __construct(
        #[GetEnv('APP_NAME')] ?string $appName,
        #[GetEnv('NEW_RELIC_LICENSE')] ?string $license,
    ) {
        if ($this->isLoaded() && $appName !== null && $license !== null) {
            $this->setAppName($appName, $license);
        }
    }

    public function setAppName(string $appName, string $license): void
    {
        newrelic_set_appname($appName, $license);
    }

    public function setTransactionName(string $transactionName): void
    {
        newrelic_name_transaction($transactionName);
    }

    public function setCustomParameter(string $key, bool|float|int|string $value): void
    {
        newrelic_add_custom_parameter($key, $value);
    }

    /**
     * @param array<string, mixed> $values
     */
    public function setCustomParameters(array $values, NewRelicPrefix $prefix = NewRelicPrefix::NONE): void
    {
        foreach ($values as $key => $value) {
            if (!is_bool($value) && !is_float($value) && !is_int($value) && !is_string($value)) {
                $value = JsonUtility::encode($value);
            }

            newrelic_add_custom_parameter($prefix->value . $key, $value);
        }
    }

    public function isLoaded(): bool
    {
        return extension_loaded('newrelic');
    }
}

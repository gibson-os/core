<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace GibsonOS\Core\Tracer;

use GibsonOS\Core\Attribute\GetEnv;
use GibsonOS\Core\Enum\TracePrefix;
use GibsonOS\Core\Utility\JsonUtility;

class NewRelicTracer extends AbstractTracer
{
    public function __construct(
        #[GetEnv('APP_NAME')] ?string $appName,
        #[GetEnv('NEW_RELIC_LICENSE')] ?string $license,
    ) {
        if ($this->isLoaded() && $appName !== null && $license !== null) {
            newrelic_set_appname($appName, $license);
        }
    }

    public function isLoaded(): bool
    {
        return extension_loaded('newrelic');
    }

    public function setTransactionName(string $transactionName): NewRelicTracer
    {
        newrelic_name_transaction($transactionName);

        return $this;
    }

    public function setCustomParameter(string $key, mixed $value, TracePrefix $prefix = TracePrefix::APP): NewRelicTracer
    {
        if (!is_bool($value) && !is_float($value) && !is_int($value) && !is_string($value)) {
            $value = JsonUtility::encode($value);
        }

        newrelic_add_custom_parameter($prefix->value . $key, $value);

        return $this;
    }
}

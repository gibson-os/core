<?php
declare(strict_types=1);

namespace GibsonOS\Core\Exception;

use GibsonOS\Core\Dto\Violation;
use Throwable;

class ViolationException extends AbstractException
{
    /**
     * @param Violation[] $violations
     */
    public function __construct(array $violations, int $code = 0, ?Throwable $previous = null)
    {
        $message = implode(
            PHP_EOL,
            array_map(
                fn (Violation $violation): string => sprintf(
                    '%s%s',
                    (
                        $violation->getObjectName() === null
                            ? ''
                            : $violation->getObjectName() . (
                                $violation->getPropertyName() === null
                                    ? ': '
                                    : '::'
                            )
                    ) .
                    ($violation->getPropertyName() === null ? '' : $violation->getPropertyName() . ': '),
                    $violation->getMessage(),
                ),
                $violations,
            ),
        );

        parent::__construct($message, $code, $previous);
    }
}

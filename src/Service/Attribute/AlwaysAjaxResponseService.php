<?php
declare(strict_types=1);

namespace GibsonOS\Core\Service\Attribute;

use GibsonOS\Core\Attribute\AttributeInterface;
use GibsonOS\Core\Exception\ResponseError;
use GibsonOS\Core\Service\Response\AjaxResponse;
use GibsonOS\Core\Service\Response\ExceptionResponse;
use GibsonOS\Core\Service\Response\ResponseInterface;

class AlwaysAjaxResponseService extends AbstractActionAttributeService
{
    public function postExecute(AttributeInterface $attribute, ResponseInterface $response): void
    {
        if (
            !$response instanceof AjaxResponse
            && !$response instanceof ExceptionResponse
        ) {
            throw new ResponseError(sprintf(
                'Response must be an instance of %s or %s. Is instance of %s',
                AjaxResponse::class,
                ExceptionResponse::class,
                get_class($response)
            ));
        }
    }
}

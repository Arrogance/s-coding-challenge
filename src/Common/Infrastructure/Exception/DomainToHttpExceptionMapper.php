<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Exception;

use App\Common\Domain\Exception\DomainException;

class DomainToHttpExceptionMapper
{
    public static function map(\Throwable $e): ?BaseHttpException
    {
        if ($e instanceof DomainException) {
            return new class($e) extends BaseHttpException {
                public function __construct(DomainException $domainException)
                {
                    parent::__construct(
                        $domainException->getMessage(),
                        $domainException->statusCode(),
                        [],
                        0,
                        $domainException
                    );
                }
            };
        }

        return null;
    }
}

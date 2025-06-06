<?php

declare(strict_types=1);

namespace App\Common\Domain\Exception;

abstract class DomainException extends \DomainException
{
    abstract public function statusCode(): int;
}

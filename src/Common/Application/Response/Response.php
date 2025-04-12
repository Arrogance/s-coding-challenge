<?php

declare(strict_types=1);

namespace App\Common\Application\Response;

abstract class Response implements \JsonSerializable
{
    abstract public static function fromEntity(object $entity): self;
}

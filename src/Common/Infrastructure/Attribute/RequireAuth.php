<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class RequireAuth
{
    public function __construct()
    {
    }
}

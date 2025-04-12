<?php

declare(strict_types=1);

namespace App\Common\Application\Security;

interface TokenManagerInterface
{
    public function generateToken(string $id): string;

    /**
     * @return array<string, mixed>
     *
     * @throws \RuntimeException
     */
    public function verify(string $token): array;
}

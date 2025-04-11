<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\Request;

use Symfony\Component\HttpFoundation\Request;

final readonly class Pagination
{
    public const int MAXIMUM_LIMIT = 100;

    public int $offset;
    public int $limit;

    public function __construct(Request $request)
    {
        $this->offset = max(0, (int) $request->query->get('offset', 0));
        $this->limit = min(100, max(1, (int) $request->query->get('limit', self::MAXIMUM_LIMIT)));
    }
}

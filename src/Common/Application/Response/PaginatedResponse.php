<?php

declare(strict_types=1);

namespace App\Common\Application\Response;

final readonly class PaginatedResponse implements \JsonSerializable
{
    /**
     * @param array<\JsonSerializable> $items
     */
    public function __construct(
        public array $items,
        public int $offset,
        public int $limit
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'meta' => [
                'offset' => $this->offset,
                'limit' => $this->limit,
                'count' => \count($this->items),
            ],
            'data' => $this->items,
        ];
    }
}

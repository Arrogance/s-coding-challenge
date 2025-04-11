<?php

declare(strict_types=1);

namespace App\Common\Application\CommandBus\Exception;

final class NoHandlerFoundForCurrentCommandException extends \LogicException
{
    public function __construct(
        string $command,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = \sprintf(
            'No handler found for %s command.',
            $command
        );

        parent::__construct($message, $code, $previous);
    }
}

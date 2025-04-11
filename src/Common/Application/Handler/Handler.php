<?php

declare(strict_types=1);

namespace App\Common\Application\Handler;

use App\Common\Application\Command\Command;

abstract class Handler
{
    /**
     * @param Command $command
     */
    abstract public function handle(Command $command);

    /**
     * @return string
     */
    abstract public static function getHandledCommand(): string;
}

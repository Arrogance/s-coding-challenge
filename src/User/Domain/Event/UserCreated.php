<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\Common\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\Email;

class UserCreated extends UserEvent
{
    public function __construct(
        UserId $id,
        private readonly string $name,
        private readonly Email $email
    ) {
        parent::__construct($id);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): Email
    {
        return $this->email;
    }
}

<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\Common\Contract\Event\UserWorkEntryCreatedEvent;
use App\Common\Domain\Event\RecordDomainEvents;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Domain\ValueObject\WorkEntryId;
use App\User\Domain\Event\UserCreated;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;

class User
{
    use RecordDomainEvents;

    private \DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        private readonly UserId $id,
        private string $name,
        private Email $email,
        private Password $password,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        $this->updatedAt = new \DateTimeImmutable();
        $this->recordEvent(new UserCreated($this->id, $this->name, $this->email));
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
        $this->update();
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
        $this->update();
    }

    public function password(): Password
    {
        return $this->password;
    }

    public function changePassword(Password $password): void
    {
        $this->password = $password;
        $this->update();
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function workEntry(WorkEntryId $id, \DateTimeImmutable $start, \DateTimeImmutable $end): void
    {
        $this->recordEvent(
            new UserWorkEntryCreatedEvent(
                $id->value(),
                $this->id->value(),
                $start,
                $end
            )
        );
    }

    public function delete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
        $this->update();
        //        $this->recordEvent(new UserDeleted($this->id));
    }

    public function restore(): void
    {
        $this->deletedAt = null;
        $this->update();
        //        $this->recordEvent(new UserRestored($this->id));
    }

    protected function update(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }
}

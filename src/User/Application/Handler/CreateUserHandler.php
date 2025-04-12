<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Service\PasswordHasherService;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;

final class CreateUserHandler extends Handler
{
    public function __construct(
        private readonly PasswordHasherService $passwordHasher,
        private readonly UserRepositoryInterface $userRepository,
        private readonly EventBusInterface $eventBus,
    ) {
    }

    public function handle(Command|CreateUserCommand $command): User
    {
        $password = $this->passwordHasher->hash($command->password);

        $user = new User(
            UserId::generate(),
            $command->name,
            new Email($command->email),
            $password
        );

        $this->userRepository->save($user);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return $user;
    }

    public static function getHandledCommand(): string
    {
        return CreateUserCommand::class;
    }
}

<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\DeleteUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;

final class DeleteUserHandler extends Handler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EventBusInterface $eventBus
    ) {
    }

    public function handle(Command|DeleteUserCommand $command): User
    {
        $userId = new UserId($command->id);

        $user = $this->userRepository->findById($userId);

        $user->delete();
        $this->userRepository->save($user);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return $user;
    }

    public static function getHandledCommand(): string
    {
        return DeleteUserCommand::class;
    }
}

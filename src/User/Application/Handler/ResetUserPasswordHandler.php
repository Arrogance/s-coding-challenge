<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\ResetUserPasswordCommand;
use App\User\Application\Service\PasswordHasherService;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;

final class ResetUserPasswordHandler extends Handler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherService $hasher,
        private readonly EventBusInterface $eventBus,
    ) {
    }

    public function handle(Command|ResetUserPasswordCommand $command): User
    {
        $user = $this->userRepository->findById(new UserId($command->id));

        $hashedPassword = $this->hasher->hash($command->newPassword);
        $user->changePassword($hashedPassword);

        $this->userRepository->save($user);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return $user;
    }

    public static function getHandledCommand(): string
    {
        return ResetUserPasswordCommand::class;
    }
}

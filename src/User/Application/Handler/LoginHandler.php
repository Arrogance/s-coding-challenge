<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\Handler\Handler;
use App\Common\Application\Security\TokenManagerInterface;
use App\User\Application\Command\LoginCommand;
use App\User\Application\Service\PasswordHasherService;
use App\User\Domain\Exception\InvalidCredentialsException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;

class LoginHandler extends Handler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly PasswordHasherService $hasher,
        private readonly TokenManagerInterface $tokenManager
    ) {
    }

    public function handle(Command|LoginCommand $command): string
    {
        $user = $this->userRepository->findByEmail(new Email($command->email));

        if (!$user || !$this->hasher->verify($command->password, $user->password())) {
            throw new InvalidCredentialsException();
        }

        return $this->tokenManager->generateToken($user->id()->value());
    }

    public static function getHandledCommand(): string
    {
        return LoginCommand::class;
    }
}

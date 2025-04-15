<?php

declare(strict_types=1);

namespace App\Tests\Api;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\Event\UserDeletedEvent;
use Faker\Factory;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Component\HttpFoundation\Response;

#[Group('dev')]
class WorkEntryApiTest extends ApiTestCase
{
    private array $userData;

    protected function setUp(): void
    {
        parent::setUp();

        $faker = Factory::create();

        $this->userData = [
            'name' => $faker->name(),
            'email' => $faker->email(),
            'password' => $faker->password(12),
        ];
    }

    public function testCreateUser(): array
    {
        $response = $this->jsonRequest('POST', '/users', $this->userData);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());
        $data = $this->getResponseData($response);

        return [
            'userId' => $data['id'],
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ];
    }

    #[Depends('testCreateUser')]
    public function testLogin(array $user): array
    {
        $response = $this->jsonRequest('POST', '/login', [
            'email' => $user['email'],
            'password' => $user['password'],
        ]);
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->getResponseData($response);

        return [
            'token' => $data['token'],
            'userId' => $user['userId'],
        ];
    }

    #[Depends('testLogin')]
    public function testCreateWorkEntry(array $context): array
    {
        $this->setAuthorizationHeader($context['token']);

        $now = new \DateTimeImmutable();
        $payload = [
            'start_date' => $now->format(\DateTimeInterface::ATOM),
            'end_date' => $now->modify('+8 hours')->format(\DateTimeInterface::ATOM),
        ];

        $response = $this->jsonRequest('POST', '/work-entries', $payload);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $entry = $this->getResponseData($response);
        $this->assertEquals($context['userId'], $entry[0]['user_id']);

        return [
            'entryId' => $entry[0]['id'],
            'userId' => $context['userId'],
            'token' => $context['token'],
        ];
    }

    #[Depends('testCreateWorkEntry')]
    public function testListWorkEntries(array $context): void
    {
        $this->setAuthorizationHeader($context['token']);

        $response = $this->jsonRequest('GET', '/work-entries?offset=0&limit=10');
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->getResponseData($response);

        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('data', $data);
        $this->assertGreaterThan(0, $data['meta']['count'] ?? 0);
    }

    #[Depends('testCreateWorkEntry')]
    public function testDeleteUserAlsoDeletesWorkEntries(array $context): void
    {
        $this->setAuthorizationHeader($context['token']);

        // Mock sync event
        $eventBus = $this->getContainer()->get(EventBusInterface::class);
        $eventBus->dispatch(new UserDeletedEvent(
            $context['userId'],
            new \DateTimeImmutable()->format(\DATE_ATOM)
        ));

        $response = $this->jsonRequest('GET', '/work-entries');
        $data = $this->getResponseData($response);
        $this->assertCount(0, $data['data']);
    }
}

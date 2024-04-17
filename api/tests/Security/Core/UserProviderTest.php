<?php

namespace Reboot\Tests\Security\Core;

use PHPUnit\Framework\MockObject\MockObject;
use Reboot\Contracts\UserInterface;
use Reboot\Contracts\UserRepositoryInterface;
use Reboot\Security\Core\UserProvider;
use PHPUnit\Framework\TestCase;

class UserProviderTest extends TestCase
{
    private MockObject|UserRepositoryInterface $users;

    private MockObject|UserInterface $user;

    private UserProvider $sut;

    protected function setUp(): void
    {
        $this->user = $this->createMock(UserInterface::class);
        $this->users = $this->createMock(UserRepositoryInterface::class);
        $this->sut = new UserProvider($this->users);
    }

    public function testRefreshUser(): void
    {
        $sut = $this->sut;
        $users = $this->users;

        $users->expects($this->once())
            ->method('refresh')
            ->with($this->user);

        $sut->refreshUser($this->user);
    }

    public function testSupportClass(): void
    {
        $sut = $this->sut;
        $users = $this->users;

        $users->expects($this->once())
            ->method('getClass')
            ->willReturn('SomeClass');

        $this->assertTrue($sut->supportsClass('SomeClass'));
    }

    public function testLoadUserByIdentifier(): void
    {
        $users = $this->users;
        $newUser = $this->user;
        $sut = $this->sut;

        $users->expects($this->once())
            ->method('findByEmail')
            ->with($email = 'test@example.com')
            ->willReturn(null);

        $users->expects($this->once())
            ->method('create')
            ->willReturn($newUser);

        $users->expects($this->once())
            ->method('store')
            ->with($newUser);

        $newUser->expects($this->once())
            ->method('setName')
            ->with($name = 'Some Name');

        $sut->loadUserByIdentifier($email, ['name' => $name, 'email' => $email]);
    }
}

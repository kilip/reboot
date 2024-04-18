<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Security\Core;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Reboot\Contracts\UserInterface;
use Reboot\Contracts\UserRepositoryInterface;
use Reboot\Security\Core\UserProvider;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

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
        $newUser->expects($this->once())
            ->method('setEmail')
            ->with($email);
        $newUser->expects($this->once())
            ->method('setRoles')
            ->with($roles = ['ROLE_USER', 'ROLE_ADMIN']);

        $attributes = [
            'name' => $name,
            'email' => $email,
            'groups' => ['Admin', 'Group Admin'],
        ];
        $sut->loadUserByIdentifier($email, $attributes);
    }

    public function testThrowExceptionOnInvalidAttributtes(): void
    {
        $users = $this->users;
        $newUser = $this->user;
        $sut = $this->sut;

        $users->expects($this->once())
            ->method('findByEmail')
            ->with($email = 'email')
            ->willReturn($newUser);

        $attributes = [
            'email' => $email,
        ];
        $this->expectException(UnsupportedUserException::class);

        $sut->loadUserByIdentifier($email, $attributes);
    }
}

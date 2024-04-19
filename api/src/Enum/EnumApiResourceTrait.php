<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Enum;

use ApiPlatform\Metadata\Operation;

trait EnumApiResourceTrait
{
    public function getId(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @return array<int,mixed>
     */
    public static function getCases(): array
    {
        return self::cases();
    }

    /**
     * @param array<string,mixed> $uriVariables
     */
    public static function getCase(Operation $operation, array $uriVariables): ?static
    {
        $name = $uriVariables['id'] ?? null;

        return self::tryFrom($name);
    }
}

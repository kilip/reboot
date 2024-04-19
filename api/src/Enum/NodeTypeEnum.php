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

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;

#[ApiResource(
    shortName: 'NodeType',
    operations: [
        new GetCollection(provider: NodeTypeEnum::class.'::getTypes'),
        new Get(provider: NodeTypeEnum::class.'::getCase'),
    ]
)]
enum NodeTypeEnum: int
{
    use EnumApiResourceTrait;

    case Unknown = 0;

    case Server = 1;

    case PersonalComputer = 2;

    case Laptop = 3;
}

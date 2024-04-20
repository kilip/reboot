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

enum ScanModeEnum: string
{
    use EnumApiResourceTrait;

    case ScanNodes = 'scan-node';

    case CheckStatus = 'check-status';
}

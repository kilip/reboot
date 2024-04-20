<?php

namespace Reboot\Enum;

enum ScanModeEnum: string
{
    use EnumApiResourceTrait;

    case ScanNodes = 'scan-node';

    case CheckStatus = 'check-status';
}

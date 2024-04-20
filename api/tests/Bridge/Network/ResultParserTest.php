<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Tests\Bridge\Network;

use PHPUnit\Framework\TestCase;
use Reboot\Bridge\Network\ResultParser;

class ResultParserTest extends TestCase
{
    public function testParse(): void
    {
        $parser = new ResultParser();
        $hosts = $parser->parse(__DIR__.'/fixtures/scan-result.xml');

        $this->assertCount(4, $hosts);
        $this->assertCount(4, $parser->getOnlineIps());
    }

    public function testBug01(): void
    {
        $parser = new ResultParser();
        $hosts = $parser->parse(__DIR__.'/fixtures/bug-01-update-node.xml');

        $this->assertCount(2, $hosts);
    }

    public function testBug02(): void
    {
        $parser = new ResultParser();
        $hosts = $parser->parse(__DIR__.'/fixtures/bug-02-status.xml');

        $this->assertCount(1, $hosts);
    }
}

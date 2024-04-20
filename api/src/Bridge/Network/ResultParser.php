<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Bridge\Network;

class ResultParser
{
    /**
     * @var array<int,string>
     */
    private array $onlineIps = [];

    /**
     * @return array<int,string>
     */
    public function getOnlineIps(): array
    {
        return $this->onlineIps;
    }

    /**
     * @return array<int, ResultNode>
     */
    public function parse(string $filename): array
    {
        if (!is_file($filename)) {
            throw NetworkException::resultFileNotExists($filename);
        }

        $this->onlineIps = [];
        $xml = simplexml_load_file($filename);
        $json = json_decode(json_encode($xml, JSON_THROW_ON_ERROR), true);

        $hosts = [];

        // single result node
        if (array_key_exists('address', $json['host'])) {
            $hosts[] = $this->parseHost($json['host']);
        } else {
            foreach ($json['host'] as $host) {
                $hosts[] = $this->parseHost($host);
            }
        }

        return $hosts;
    }

    /**
     * @param array<string,mixed> $hosts
     */
    private function parseHost(array $hosts): ResultNode
    {
        $ip = null;
        $mac = null;
        $vendor = null;
        $address = $hosts['address'];

        if (1 == count($address)) {
            $ip = $address['@attributes']['addr'];
        } else {
            foreach ($address as $addr) {
                $attr = $addr['@attributes'];
                $type = $attr['addrtype'];
                $value = $attr['addr'];
                if ('ipv4' === $type) {
                    $ip = $value;
                }
                if ('mac' === $type) {
                    $mac = $value;
                }
                if (array_key_exists('vendor', $attr)) {
                    $vendor = $attr['vendor'];
                }
            }
        }

        $hostname = $ip;
        if (count($hosts['hostnames']) > 0) {
            $hostname = $hosts['hostnames']['hostname']['@attributes']['name'];
        }

        $this->onlineIps[] = $ip;

        return new ResultNode(
            $hostname,
            $ip,
            $mac,
            $vendor
        );
    }
}

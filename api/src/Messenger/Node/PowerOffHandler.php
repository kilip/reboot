<?php

/*
 * This file is part of the reboot project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Reboot\Messenger\Node;

use Reboot\Contracts\Entity\NodeInterface;
use Reboot\Contracts\Entity\NodeRepositoryInterface;
use Reboot\Contracts\SshFactoryInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(fromTransport: 'async')]
final readonly class PowerOffHandler
{
    public function __construct(
        private NodeRepositoryInterface $nodeRepository,
        private SshFactoryInterface $sshFactory,
        private HubInterface $mercureHub
    ) {
    }

    public function __invoke(PowerOffCommand $command): void
    {
        $node = $this->nodeRepository
            ->findById($command->getNodeId());

        if(!$node instanceof NodeInterface){
            throw NodeCommandException::powerOffNodeNotExists(
                $command->getNodeId()
            );
        }

        if(!$node->isOnline()){
            $this->publish([
                'success' => true,
                'message' => "Node {$node->getHostname()} already turned off"
            ]);
            return;
        }

        try{

            $ssh = $this->sshFactory
                ->createSshClient($node);

            $ssh->addCommand('sudo poweroff');
            $ssh->execute();
        }catch (\Exception $e){
            $msg = $e->getMessage();
            $regex = '/Connection closed \(by server\) prematurely/';
            $success = false;

            if(false !== preg_match($regex, $msg)){
                $success = true;
                $msg = "Successfully turned off {$node->getHostname()}";
            }

            $data = [
                'success' => $success,
                'message' => $msg,
            ];

            $this->publish($data);
        }

    }

    /**
     * @param array<string,string> $data
     */
    private function publish(array $data): void
    {
        $hub = $this->mercureHub;
        $update = new Update(
            $hub->getPublicUrl().'/power-off',
            json_encode($data, JSON_THROW_ON_ERROR)
        );
        $hub->publish($update);
    }


}

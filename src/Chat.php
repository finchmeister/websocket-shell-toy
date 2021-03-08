<?php declare(strict_types=1);

namespace WebSocketShell;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Symfony\Component\Process\Process;

class Chat implements MessageComponentInterface {
    /**
     * @var ConnectionInterface[]
     */
    protected $clients;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d sending message "%s" "\n"', $from->resourceId, $msg);

        $process = Process::fromShellCommandline(trim($msg));
        $process->run(function ($type, $buffer) use ($from) {
            echo $buffer;
            foreach ($this->clients as $client) {
                if ($client === $from) {
                    $client->send($buffer);
                }
            }
        });
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }
}
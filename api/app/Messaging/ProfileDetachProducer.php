<?php

namespace App\Messaging;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class ProfileDetachProducer
{
    public function publish(int $userId, int $profileId): void
    {
        $connection = new AMQPStreamConnection(
            config('rabbitmq.host'),
            config('rabbitmq.port'),
            config('rabbitmq.user'),
            config('rabbitmq.password'),
            config('rabbitmq.vhost'),
        );

        $channel = $connection->channel();
        $queue = config('rabbitmq.queue');

        try {
            $channel->queue_declare($queue, false, true, false, false);

            $message = new AMQPMessage(
                json_encode([
                    'user_id' => $userId,
                    'profile_id' => $profileId,
                ], JSON_THROW_ON_ERROR),
                [
                    'content_type' => 'application/json',
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
                ],
            );

            $channel->basic_publish($message, '', $queue);
        } finally {
            $channel->close();
            $connection->close();
        }
    }
}

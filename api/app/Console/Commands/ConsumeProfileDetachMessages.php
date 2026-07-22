<?php

namespace App\Console\Commands;

use App\Repositories\UserProfileRepositoryInterface;
use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class ConsumeProfileDetachMessages extends Command
{
    protected $signature = 'rabbitmq:consume-profile-detaches';

    protected $description = 'Consome desassociações de perfis enviadas ao RabbitMQ';

    public function handle(UserProfileRepositoryInterface $repository): int
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

        $channel->queue_declare($queue, false, true, false, false);
        $channel->basic_qos(null, 1, null);

        $this->info("Aguardando mensagens na fila [{$queue}]...");

        $channel->basic_consume(
            $queue,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($repository): void {
                try {
                    $data = json_decode($message->getBody(), true, flags: JSON_THROW_ON_ERROR);

                    $repository->detach(
                        (int) $data['user_id'],
                        (int) $data['profile_id'],
                    );

                    $message->ack();

                    $this->info(
                        "Perfil {$data['profile_id']} desassociado do usuário {$data['user_id']}.",
                    );
                } catch (Throwable $exception) {
                    report($exception);
                    $message->nack(false, true);
                    $this->error('Erro ao processar mensagem: '.$exception->getMessage());
                }
            },
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();

        return self::SUCCESS;
    }
}

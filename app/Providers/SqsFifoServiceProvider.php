<?php

namespace App\Providers;

use Aws\Sqs\SqsClient;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Connectors\SqsConnector;
use Illuminate\Queue\SqsQueue;
use Illuminate\Support\Arr;

class SqsFifoQueue extends SqsQueue
{
    public function pushRaw($payload, $queue = null, array $options = [])
    {
        $response = $this->sqs->sendMessage([
            'QueueUrl' => $this->getQueue($queue),
            'MessageBody' => $payload,
            'MessageGroupId' => uniqid(),
            'MessageDeduplicationId' => uniqid()
        ]);

        return $response->get('MessageId');
    }
}

class SqsFifoConnector extends SqsConnector
{
    public function connect(array $config)
    {
        $config = $this->getDefaultConfiguration($config);

        if ($config['key'] && $config['secret']) {
            $config['credentials'] = Arr::only($config, ['key', 'secret']);
        }

        return new SqsFifoQueue(
            new SqsClient($config), $config['queue'], Arr::get($config, 'prefix', '')
        );
    }
}

class SqsFifoServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->afterResolving('queue', function ($manager) {
            $manager->addConnector('sqsfifo', function () {
                return new SqsFifoConnector;
            });
        });
    }
}
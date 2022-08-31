<?php

namespace App\ActionHandler;

use App\Exception\GatewayException;
use App\Service\SynchronizationService;
use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
use Respect\Validation\Exceptions\ComponentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SynchronizationWebhookHandler implements ActionHandlerInterface
{
    private SynchronizationService $synchronizationService;

    /**
     * @param ContainerInterface $container
     * @throws GatewayException
     */
    public function __construct(ContainerInterface $container)
    {
        $synchronizationService = $container->get('synchronizationservice');
        if ($synchronizationService instanceof SynchronizationService) {
            $this->synchronizationService = $synchronizationService;
        } else {
            throw new GatewayException('The service container does not contain the required services for the SynchronizationWebhookHandler');
        }
    }

    /**
     *  This function returns the requered configuration as a [json-schema](https://json-schema.org/) array.
     *
     * @throws array a [json-schema](https://json-schema.org/) that this  action should comply to
     */
    public function getConfiguration(): array
    {
        return [
            '$id'        => 'https://example.com/person.schema.json',
            '$schema'    => 'https://json-schema.org/draft/2020-12/schema',
            'title'      => 'Notification Action',
            'required'   => ['source', 'entity', 'locationIdField'],
            'properties' => [
                'source' => [
                    'type'        => 'string',
                    'description' => 'The source where to sink from',
                    'example'     => 'native://default',
                ],
                'entity' => [
                    'type'        => 'string',
                    'description' => 'The enitity to sink',
                    'example'     => '',
                ],
                'locationIdField' => [
                    'type'        => 'string',
                    'description' => 'The location of the id field in the external object',
                    'example'     => 'id',
                ],

            ],
        ];
    }

    /**
     * Run the actual business logic in the appropriate server
     *
     * @param array $data
     * @param array $configuration
     * @return array
     * @throws GatewayException|InvalidArgumentException|ComponentException|CacheException
     */
    public function __run(array $data, array $configuration): array
    {
        $result = $this->synchronizationService->SynchronizationWebhookHandler($data, $configuration);

        return $data;
    }
}

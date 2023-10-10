<?php

namespace App\Service;

use App\Entity\Activity;
use DateTime;
use Exception;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HAService
{
    public function __construct(
        private HttpClientInterface $client,
        private UrlGeneratorInterface $urlGenerator,
        private string $url,
        private string $token
    ) {
    }

    /**
     * @param array<Activity> $activities
     */
    public function enqueueScript(DateTime $time, array $activities, string $script, string $name): ?int
    {
        $id = rand(0, 999999);
        try {
            $response = $this->client->request(
                'POST',
                $this->url . '/api/config/automation/config/' . $id,
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $this->token,
                    ],
                    'json' => [
                        'alias' => $name,
                        'trigger' => [
                            [
                                'platform' => 'template',
                                'value_template' => "now().strftime('%Y-%m-%d %H:%M:%S') == '" . $time->format('Y-m-d H:i:00') . "'",
                            ],
                        ],
                        'action' => [
                            [
                                'service' => $script,
                            ],
                            [
                                'service' => 'rest_command.symfony_soaking',
                                'data' => [
                                    'ids' => array_map(fn (Activity $a): array => ['id' => $a->getId()], $activities)
                                ],
                            ],
                            [

                                'service' => 'automation.turn_off',
                                'entity_id' => 'automation.' . $id
                            ]
                        ],
                    ],
                ],
            );
            $response->getContent();
            
            return $id;
        } catch (Exception $e) {
            return null;
        }
    }
}

<?php

namespace App\Service\Navigation\Client;

use App\Dto\BoundingBoxDto;
use App\Dto\LocationDto;
use App\Dto\RouteDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class OpenStreetRouterClient implements RouterClientInterface
{
    private const DIRECTIONS_ENTRYPOINT = '/ors/v2/directions/driving-car';
    private HttpClientInterface $client;

    public function __construct(
        HttpClientInterface $client,
        #[Autowire(env: 'OSR_API_URL')]
        private string $url,
    ) {
        $this->client = $client->withOptions([
            'base_uri' => $this->url,
        ]);
    }

    public function fetchRoute(LocationDto $start, LocationDto $finish): RouteDto
    {
        $response = $this->client->request('POST', static::DIRECTIONS_ENTRYPOINT, [
            'json' => [
                'coordinates' => [
                    array_reverse($start->toLatLng()),
                    array_reverse($finish->toLatLng()),
                ],
            ],
        ]);

        $route = $response->toArray();

        return new RouteDto(
            $start,
            $finish,
            $route['routes'][0]['summary']['distance'],
            $route['routes'][0]['summary']['duration'],
            $route['routes'][0]['geometry'],
            BoundingBoxDto::fromArray($route['routes'][0]['bbox']),
        );
    }
}

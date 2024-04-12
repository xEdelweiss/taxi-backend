<?php

namespace App\Tests\Integration\Service\Navigation;

use App\Dto\LocationDto;
use App\Service\Navigation\OpenStreetRouterClient;
use App\Tests\Support\IntegrationTester;
use Codeception\Test\Unit;
use Symfony\Component\HttpClient\CurlHttpClient;

class OpenStreetRouteClientTest extends Unit
{
    protected IntegrationTester $tester;

    public function testGetRoute_returns_distance_and_duration(): void
    {
        $client = new OpenStreetRouterClient(new CurlHttpClient(), 'http://localhost:8090');
        $route = $client->fetchRoute(
            new LocationDto(46.4273814334286, 30.751279752912698, ''),
            new LocationDto(46.423173199108106, 30.74705368639186, ''),
        );

        $this->assertSame(634.5, $route->distance);
        $this->assertSame(68.3, $route->duration);
    }
}

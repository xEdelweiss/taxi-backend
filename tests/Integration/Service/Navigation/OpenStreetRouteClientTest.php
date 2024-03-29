<?php

namespace App\Tests\Integration\Service\Navigation;

use App\Service\Navigation\OpenStreetRouterClient;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\CurlHttpClient;

class OpenStreetRouteClientTest extends KernelTestCase
{
    public function testGetRoute_returns_distance_and_duration(): void
    {
        $client = new OpenStreetRouterClient(new CurlHttpClient(), 'http://localhost:8090');
        $route = $client->fetchRoute([46.4273814334286, 30.751279752912698], [46.423173199108106, 30.74705368639186]);

        $this->assertSame([
            'distance' => 634.5,
            'duration' => 68.3,
        ], $route);
    }
}

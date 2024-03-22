<?php

namespace App\Service\Geolocation;

use App\Dto\AddressDto;
use App\Dto\CoordinatesDto;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NominatimGeocoder implements GeocoderInterface
{
    public function __construct(
        private HttpClientInterface $client,
        #[Autowire('%kernel.default_locale%')]
        private $locale,
    ) {}

    public function useLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function addressToCoordinates(string $address): CoordinatesDto
    {
        $result = $this->makeSearchRequest($address);

        return new CoordinatesDto(
            $result[0]['lat'],
            $result[0]['lon'],
        );
    }

    public function coordinatesToAddress(float $latitude, float $longitude): AddressDto
    {
        $result = $this->makeReverseRequest($latitude, $longitude);

        $address = $result['address']['railway']
            ?? $result['address']['road'] . ', ' . $result['address']['house_number'];

        return new AddressDto($address);
    }

    private function makeSearchRequest(string $address, string $city = 'Odesa', string $country = 'Ukraine'): array
    {
        $query = rawurlencode(implode(', ', [$address, $city, $country]));

        $response = $this->client->request(
            'GET',
            "https://nominatim.openstreetmap.org/search.php?q={$query}&format=jsonv2&limit=1",
            [
                'headers' => $this->getDefaultHeaders(),
            ]
        );

        return $response->toArray();
    }

    private function makeReverseRequest(float $latitude, float $longitude): array
    {
        $response = $this->client->request(
            'GET',
            "https://nominatim.openstreetmap.org/reverse?lat={$latitude}&lon={$longitude}&format=jsonv2",
            [
                'headers' => $this->getDefaultHeaders(),
            ],
        );

        return $response->toArray();
    }

    private function getDefaultHeaders(): array
    {
        return [
            'Accept-Language' => $this->locale,
            'User-Agent' => 'PetProject/Demo-Taxi-App',
        ];
    }
}

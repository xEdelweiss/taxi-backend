<?php

namespace App\Service\Geolocation\Geocoder;

use App\Dto\AddressDto;
use App\Dto\CoordinatesDto;
use App\Exception\Geolocation\AddressNotFound;
use App\Service\Geolocation\AddressFormatter\NominatimAddressFormatter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[When(env: 'prod')]
#[When(env: 'stage')]
class NominatimGeocoder implements GeocoderInterface
{
    public function __construct(
        private readonly HttpClientInterface       $client,
        private readonly NominatimAddressFormatter $addressFormatter,
        #[Autowire('%kernel.default_locale%')]
        private                                    $locale,
    ) {}

    public function useLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function addressToCoordinates(string $address): CoordinatesDto
    {
        $result = $this->makeSearchRequest($address);

        if (empty($result)) {
            throw new AddressNotFound($address);
        }

        return new CoordinatesDto(
            $result[0]['lat'],
            $result[0]['lon'],
        );
    }

    public function coordinatesToAddress(float $latitude, float $longitude): AddressDto
    {
        $result = $this->makeReverseRequest($latitude, $longitude);

        $address = $this->addressFormatter->format($result);

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

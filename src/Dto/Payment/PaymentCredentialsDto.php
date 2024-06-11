<?php

namespace App\Dto\Payment;

readonly class PaymentCredentialsDto implements \Stringable
{
    public function __construct(
        public string $provider,
        public array  $credentials,
    ) {}

    public function get(string $key): mixed
    {
        return $this->credentials[$key] ?? null;
    }

    public static function fromString(string $credentials): self
    {
        $decoded = json_decode(base64_decode($credentials), true, flags: JSON_THROW_ON_ERROR);

        return new self(
            $decoded['provider'],
            $decoded['credentials'],
        );
    }

    public function __toString(): string
    {
        return base64_encode(json_encode([
            'provider' => $this->provider,
            'credentials' => $this->credentials,
        ], JSON_THROW_ON_ERROR));
    }
}

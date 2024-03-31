<?php

namespace App\Exception\Geolocation;

use JetBrains\PhpStorm\Pure;
use Throwable;

class AddressNotFound extends \RuntimeException
{
    #[Pure]
    public function __construct(string $address, int $code = 0, ?Throwable $previous = null)
    {
        $message = sprintf('Address "%s" not found', $address);
        parent::__construct($message, $code, $previous);
    }
}

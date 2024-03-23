<?php

namespace App\Service\Payment\ValueResolver;

use App\Service\Payment\Dto\PaymentHoldDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PaymentHoldValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PaymentHoldDto::class) {
            return [];
        }

        $value = $request->attributes->get($argument->getName());
        if (!is_string($value)) {
            return [];
        }

        return [new PaymentHoldDto($value)];
    }
}

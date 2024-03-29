<?php

namespace App\Service\Payment\ValueResolver;

use App\Service\Payment\Dto\PaymentCredentialsDto;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class PaymentCredentialsValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PaymentCredentialsDto::class) {
            return [];
        }

        // @fixme use MapQueryString?
        $value = $request->query->get($argument->getName());
        if (!is_string($value)) {
            return [];
        }

        return [PaymentCredentialsDto::fromString($value)];
    }
}

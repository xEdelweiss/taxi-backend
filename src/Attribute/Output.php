<?php

namespace App\Attribute;

use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Output extends OA\Response
{
    public function __construct(?string $class, string $code = Response::HTTP_OK, string $description = null) {
        parent::__construct(
            response: $code,
            description:$description ?? Response::$statusTexts[$code] ?? 'Unknown',
            content: $class ? new Model(type: $class) : null,
        );
    }
}

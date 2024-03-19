<?php

function p(string|int $phone): string
{
    $template = '380990000000';

    if (strlen($phone) > strlen($template)) {
        return throw new InvalidArgumentException('Phone number is too long.');
    }

    return substr_replace($template, $phone, strlen($template) - strlen($phone));
}

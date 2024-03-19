<?php

namespace PHPSTORM_META {

    //<editor-fold desc="// codeceptionHttpCodes">
    registerArgumentsSet('codeceptionHttpCodes',
        \Codeception\Util\HttpCode::SWITCHING_PROTOCOLS,
        \Codeception\Util\HttpCode::PROCESSING,
        \Codeception\Util\HttpCode::EARLY_HINTS,
        \Codeception\Util\HttpCode::OK,
        \Codeception\Util\HttpCode::CREATED,
        \Codeception\Util\HttpCode::ACCEPTED,
        \Codeception\Util\HttpCode::NON_AUTHORITATIVE_INFORMATION,
        \Codeception\Util\HttpCode::NO_CONTENT,
        \Codeception\Util\HttpCode::RESET_CONTENT,
        \Codeception\Util\HttpCode::PARTIAL_CONTENT,
        \Codeception\Util\HttpCode::MULTI_STATUS,
        \Codeception\Util\HttpCode::ALREADY_REPORTED,
        \Codeception\Util\HttpCode::IM_USED,
        \Codeception\Util\HttpCode::MULTIPLE_CHOICES,
        \Codeception\Util\HttpCode::MOVED_PERMANENTLY,
        \Codeception\Util\HttpCode::FOUND,
        \Codeception\Util\HttpCode::SEE_OTHER,
        \Codeception\Util\HttpCode::NOT_MODIFIED,
        \Codeception\Util\HttpCode::USE_PROXY,
        \Codeception\Util\HttpCode::RESERVED,
        \Codeception\Util\HttpCode::TEMPORARY_REDIRECT,
        \Codeception\Util\HttpCode::PERMANENTLY_REDIRECT,
        \Codeception\Util\HttpCode::BAD_REQUEST,
        \Codeception\Util\HttpCode::UNAUTHORIZED,
        \Codeception\Util\HttpCode::PAYMENT_REQUIRED,
        \Codeception\Util\HttpCode::FORBIDDEN,
        \Codeception\Util\HttpCode::NOT_FOUND,
        \Codeception\Util\HttpCode::METHOD_NOT_ALLOWED,
        \Codeception\Util\HttpCode::NOT_ACCEPTABLE,
        \Codeception\Util\HttpCode::PROXY_AUTHENTICATION_REQUIRED,
        \Codeception\Util\HttpCode::REQUEST_TIMEOUT,
        \Codeception\Util\HttpCode::CONFLICT,
        \Codeception\Util\HttpCode::GONE,
        \Codeception\Util\HttpCode::LENGTH_REQUIRED,
        \Codeception\Util\HttpCode::PRECONDITION_FAILED,
        \Codeception\Util\HttpCode::REQUEST_ENTITY_TOO_LARGE,
        \Codeception\Util\HttpCode::REQUEST_URI_TOO_LONG,
        \Codeception\Util\HttpCode::UNSUPPORTED_MEDIA_TYPE,
        \Codeception\Util\HttpCode::REQUESTED_RANGE_NOT_SATISFIABLE,
        \Codeception\Util\HttpCode::EXPECTATION_FAILED,
        \Codeception\Util\HttpCode::I_AM_A_TEAPOT,
        \Codeception\Util\HttpCode::MISDIRECTED_REQUEST,
        \Codeception\Util\HttpCode::UNPROCESSABLE_ENTITY,
        \Codeception\Util\HttpCode::LOCKED,
        \Codeception\Util\HttpCode::FAILED_DEPENDENCY,
        \Codeception\Util\HttpCode::RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL,
        \Codeception\Util\HttpCode::UPGRADE_REQUIRED,
        \Codeception\Util\HttpCode::PRECONDITION_REQUIRED,
        \Codeception\Util\HttpCode::TOO_MANY_REQUESTS,
        \Codeception\Util\HttpCode::REQUEST_HEADER_FIELDS_TOO_LARGE,
        \Codeception\Util\HttpCode::UNAVAILABLE_FOR_LEGAL_REASONS,
        \Codeception\Util\HttpCode::INTERNAL_SERVER_ERROR,
        \Codeception\Util\HttpCode::NOT_IMPLEMENTED,
        \Codeception\Util\HttpCode::BAD_GATEWAY,
        \Codeception\Util\HttpCode::SERVICE_UNAVAILABLE,
        \Codeception\Util\HttpCode::GATEWAY_TIMEOUT,
        \Codeception\Util\HttpCode::VERSION_NOT_SUPPORTED,
        \Codeception\Util\HttpCode::VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL,
        \Codeception\Util\HttpCode::INSUFFICIENT_STORAGE,
        \Codeception\Util\HttpCode::LOOP_DETECTED,
        \Codeception\Util\HttpCode::NOT_EXTENDED,
        \Codeception\Util\HttpCode::NETWORK_AUTHENTICATION_REQUIRED
    );
    //</editor-fold>

    expectedArguments(\App\Tests\Support\ApiTester::seeResponseCodeIs(), 0, argumentsSet('codeceptionHttpCodes'));
    expectedArguments(\App\Tests\Support\ApiTester::seeResponse(), 0, argumentsSet('codeceptionHttpCodes'));

    // modules autocompletion

    registerArgumentsSet('codeceptionModules', 'Symfony', 'REST', 'Doctrine2', 'Asserts', 'Db', 'PhpBrowser');

    expectedArguments(\Codeception\Test\Unit::getModule(), 0, argumentsSet('codeceptionModules'));
    override(\Codeception\Test\Unit::getModule(), map([
        '' => '\Codeception\Module\@',
    ]));

    expectedArguments(\Codeception\Module::getModule(), 0, argumentsSet('codeceptionModules'));
    override(\Codeception\Module::getModule(), map([
        '' => '\Codeception\Module\@',
    ]));
}

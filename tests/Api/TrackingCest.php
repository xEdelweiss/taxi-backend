<?php


namespace App\Tests\Api;

use App\Document\TrackingLocation;
use App\Tests\Support\ApiTester;
use Codeception\Util\HttpCode;

class TrackingCest
{
    public function _after(ApiTester $i): void
    {
        $i->markRateLimiterAllowed();
    }

    public function trackingHelperWorks(ApiTester $i): void
    {
        $user = $i->haveUser(p(1));
        $i->moveToLocation($user, 46.4273814334286, 30.751279752912698);

        $i->seeInCollection(TrackingLocation::class, [
            'userId' => 1,
            'role' => 'user',
            'coordinates.latitude' => 46.42738,
            'coordinates.longitude' => 30.75128,
        ]);
    }

    public function userCanTrackLocation(ApiTester $i): void
    {
        $i->amLoggedInAsNewUser(p(1));
        $i->seeNumElementsInCollection(TrackingLocation::class, 0);

        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.4273814334286,
            'longitude' => 30.751279752912698,
        ]);

        $i->clearEntityManager();

        $i->seeResponse(HttpCode::NO_CONTENT);

        $i->seeInCollection(TrackingLocation::class, [
            'userId' => 1,
            'role' => 'user',
            'coordinates.latitude' => 46.42738,
            'coordinates.longitude' => 30.75128,
        ]);
    }

    public function driverCanTrackLocation(ApiTester $i): void
    {
        $i->amLoggedInAsNewDriver(p(1));
        $i->seeNumElementsInCollection(TrackingLocation::class, 0);

        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.4273814334286,
            'longitude' => 30.751279752912698,
        ]);

        $i->clearEntityManager();

        $i->seeResponse(HttpCode::NO_CONTENT);

        $i->seeInCollection(TrackingLocation::class, [
            'userId' => 1,
            'role' => 'driver',
            'coordinates.latitude' => 46.42738,
            'coordinates.longitude' => 30.75128,
        ]);
    }

    public function oneRecordPerUser(ApiTester $i): void
    {
        $i->amLoggedInAsNewUser(p(1));
        $i->seeNumElementsInCollection(TrackingLocation::class, 0);

        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.4273814334286,
            'longitude' => 30.751279752912698,
        ]);

        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.423173199108106,
            'longitude' => 30.74705368639186,
        ]);

        $i->seeResponse(HttpCode::NO_CONTENT);

        $i->seeNumElementsInCollection(TrackingLocation::class, 1);
        $i->seeInCollection(TrackingLocation::class, [
            'userId' => 1,
            'coordinates.latitude' => 46.42317,
            'coordinates.longitude' => 30.74705,
        ]);
    }

    public function trackingLocationIsThrottled(ApiTester $i): void
    {
        $i->amLoggedInAsNewUser(p(1));
        $i->markRateLimiterExceeded();

        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.423173199108106,
            'longitude' => 30.74705368639186,
        ]);

        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.423173199108106,
            'longitude' => 30.74705368639186,
        ]);

        $i->seeResponse(HttpCode::TOO_MANY_REQUESTS);
    }

    public function trackingLocationIsThrottledPerUser(ApiTester $i): void
    {
        $i->amLoggedInAsNewUser(p(1));
        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.423173199108106,
            'longitude' => 30.74705368639186,
        ]);

        $i->amLoggedInAsNewUser(p(2));
        $i->sendPost('/api/tracking/locations', [
            'latitude' => 46.423173199108106,
            'longitude' => 30.74705368639186,
        ]);

        $i->seeResponse(HttpCode::NO_CONTENT);
    }
}

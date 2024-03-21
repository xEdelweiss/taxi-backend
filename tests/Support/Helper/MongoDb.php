<?php

namespace App\Tests\Support\Helper;

use Codeception\TestInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Throwable;

class MongoDb extends \Codeception\Module
{
    public function _before(TestInterface $test)
    {
        $symfony = $this->getModule('Symfony');
        $mongoDb = $this->getModule('MongoDb');

        try {
            // @fixme `ns not found symfony.TrackingLocation` on dropIndexes
            $symfony->runSymfonyConsoleCommand('doctrine:mongo:schema:drop', ['--quiet', '--env' => 'test']);
        } catch (Throwable) {}
        try {
            $symfony->runSymfonyConsoleCommand('doctrine:mongo:schema:create', ['--quiet', '--env' => 'test']);
        } catch (Throwable) {}

        $defaultDb = $symfony->grabService('doctrine_mongodb.odm.default_document_manager')
            ->getConfiguration()->getDefaultDB();

        $mongoDb->useDatabase($defaultDb);
    }

    public function seeInCollection(string $collection, array $criteria = []): void
    {
        $this->getModule('MongoDb')
            ->seeInCollection($this->getCollectionNameFromClass($collection), $criteria);
    }

    public function seeNumElementsInCollection(string $collection, int $expected, array $criteria = []): void
    {
        $this->getModule('MongoDb')
            ->seeNumElementsInCollection($this->getCollectionNameFromClass($collection), $expected, $criteria);
    }

    private function getCollectionNameFromClass(string $class): string
    {
        /** @var DocumentManager $documentManager */
        $documentManager = $this->getModule('Symfony')
            ->grabService('doctrine_mongodb.odm.default_document_manager');

        return $documentManager->getDocumentCollection($class)->getCollectionName();
    }
}

<?php

use PHPUnit\Framework\TestCase;
use Halapi\Factory\RelationFactory;
use Halapi\Relation\RelationInterface;
use Halapi\Tests\Fixtures\Entity\BlueCar;

/**
 * Class RelationFactoryTest
 * @author Romain Richard
 */
class RelationFactoryTest extends TestCase
{
    /**
     * Test relation factory
     */
    public function testGetRelations()
    {
        $linksRelationMock = $this->createMock(RelationInterface::class);
        $linksRelationMock->method('getName')->willReturn('_links');
        $linksRelationMock->method('getRelation')->willReturn(['self' => '/tests/1']);
        $embeddedRelationMock = $this->createMock(RelationInterface::class);
        $embeddedRelationMock->method('getName')->willReturn('_embedded');
        $embeddedRelationMock->method('getRelation')->willReturn(['friend' => ['id' => 1, 'name' => 'bob']]);
        $relationFactory = new RelationFactory([$linksRelationMock, $embeddedRelationMock]);
        
        $this->assertEquals(
            $relationFactory->getRelations(new BlueCar()),
            [
                '_links' => ['self' => '/tests/1'],
                '_embedded' => ['friend' => ['id' => 1, 'name' => 'bob']],
            ]
        );
    }
}

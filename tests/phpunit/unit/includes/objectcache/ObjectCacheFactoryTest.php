<?php

use MediaWiki\Config\ServiceOptions;
use MediaWiki\Logger\Spi;
use Wikimedia\Stats\StatsFactory;

/**
 * @covers ObjectCacheFactory
 */
class ObjectCacheFactoryTest extends MediaWikiUnitTestCase {
	private function newObjectCacheFactory() {
		$factory = new ObjectCacheFactory(
			$this->createMock( ServiceOptions::class ),
			$this->createMock( StatsFactory::class ),
			$this->createMock( Spi::class ),
			'testWikiId'
		);

		return $factory;
	}

	public function testNewObjectCacheFactory() {
		$this->assertInstanceOf(
			ObjectCacheFactory::class,
			$this->newObjectCacheFactory()
		);
	}

	public function testNewFromParams() {
		$factory = $this->newObjectCacheFactory();

		$objCache = $factory->newFromParams( [
			'class' => 'HashBagOStuff',
			'args' => [ 'foo', 'bar' ],
		] );

		$this->assertInstanceOf( HashBagOStuff::class, $objCache );
	}
}

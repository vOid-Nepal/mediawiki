<?php

use MediaWiki\MainConfigNames;

class ObjectCacheTest extends MediaWikiIntegrationTestCase {

	protected function setUp(): void {
		// Parent calls ObjectCache::clear() among other things
		parent::setUp();

		$this->setCacheConfig();
		$this->setMainCache( CACHE_NONE );
		$this->overrideConfigValues( [
			MainConfigNames::MessageCacheType => CACHE_NONE,
			MainConfigNames::ParserCacheType => CACHE_NONE,
		] );
	}

	private function setCacheConfig( $arr = [] ) {
		$defaults = [
			CACHE_NONE => [ 'class' => EmptyBagOStuff::class ],
			CACHE_DB => [ 'class' => SqlBagOStuff::class ],
			CACHE_ANYTHING => [ 'factory' => 'ObjectCache::newAnything' ],
			// Mock ACCEL with 'hash' as being installed.
			// This makes tests deterministic regardless of APC.
			CACHE_ACCEL => [ 'class' => HashBagOStuff::class ],
			'hash' => [ 'class' => HashBagOStuff::class ],
		];
		$this->overrideConfigValue( MainConfigNames::ObjectCaches, $arr + $defaults );
	}

	/** @covers ObjectCache::newAnything */
	public function testNewAnythingNothing() {
		$this->assertInstanceOf(
			SqlBagOStuff::class,
			ObjectCache::newAnything( [] ),
			'No available types. Fallback to DB'
		);
	}

	/** @covers ObjectCache::newAnything */
	public function testNewAnythingHash() {
		$this->setMainCache( CACHE_HASH );

		$this->assertInstanceOf(
			HashBagOStuff::class,
			ObjectCache::newAnything( [] ),
			'Use an available type (hash)'
		);
	}

	/** @covers ObjectCache::newAnything */
	public function testNewAnythingAccel() {
		$this->setMainCache( CACHE_ACCEL );

		$this->assertInstanceOf(
			HashBagOStuff::class,
			ObjectCache::newAnything( [] ),
			'Use an available type (CACHE_ACCEL)'
		);
	}

	/** @covers ObjectCache::newAnything */
	public function testNewAnythingNoAccel() {
		$this->setMainCache( CACHE_ACCEL );

		$this->setCacheConfig( [
			// Mock APC not being installed (T160519, T147161)
			CACHE_ACCEL => [ 'class' => EmptyBagOStuff::class ]
		] );

		$this->assertInstanceOf(
			SqlBagOStuff::class,
			ObjectCache::newAnything( [] ),
			'Fallback to DB if available types fall back to Empty'
		);
	}

	/** @covers ObjectCache::newAnything */
	public function testNewAnythingNoAccelNoDb() {
		$this->setMainCache( CACHE_ACCEL );

		$this->setCacheConfig( [
			// Mock APC not being installed (T160519, T147161)
			CACHE_ACCEL => [ 'class' => EmptyBagOStuff::class ]
		] );

		$this->getServiceContainer()->disableStorage();

		$this->assertInstanceOf(
			EmptyBagOStuff::class,
			ObjectCache::newAnything( [] ),
			'Fallback to none if available types and DB are unavailable'
		);
	}

	/** @covers ObjectCache::newAnything */
	public function testNewAnythingNothingNoDb() {
		$this->getServiceContainer()->disableStorage();

		$this->assertInstanceOf(
			EmptyBagOStuff::class,
			ObjectCache::newAnything( [] ),
			'No available types or DB. Fallback to none.'
		);
	}
}

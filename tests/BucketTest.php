<?php namespace Rackbeat\Throttler\Tests;

use PHPUnit\Framework\TestCase;
use Rackbeat\Throttler\Bucket;

class BucketTest extends TestCase
{
	/** @var Bucket */
	protected $bucket;

	public function setUp() {
		parent::setUp();

		$this->bucket = new Bucket( 100, 60 );
	}
	
	// todo test bursts

	/** @test */
	public function it_can_be_picked_from() {
		$this->bucket->setSize( 10 );

		$this->assertEquals( 10, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );

		$this->bucket->pick();

		$this->assertEquals( 9, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );

		$this->bucket->pick();
		$this->bucket->pick();

		$this->assertEquals( 7, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );

		// pick till empty
		while ( $this->bucket->getRemaining() > 0 ) {
			$this->bucket->pick();
		}

		$this->assertEquals( true, $this->bucket->isExhausted() );
	}

	/** @test */
	public function it_can_have_unlimited_picks() {
		$this->bucket->makeUnlimited();

		$this->assertEquals( null, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );

		$this->bucket->pick();

		$this->assertEquals( null, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );
	}

	/** @test */
	public function it_can_calculate_expected_time_per_iteration() {
		$this->bucket->setSize( 100 )->setSeconds( 10 );

		$this->assertEquals( 0.1, $this->bucket->expectedSecondsPerIteration() );
		$this->assertEquals( 0.1 * 1000000, $this->bucket->expectedTimePerIteration() );

		$this->bucket->setSize( 10 )->setSeconds( 10 );

		$this->assertEquals( 1, $this->bucket->expectedSecondsPerIteration() );
		$this->assertEquals( 1 * 1000000, $this->bucket->expectedTimePerIteration() );

		$this->bucket->setSize( 10 )->setSeconds( 20 );

		$this->assertEquals( 2, $this->bucket->expectedSecondsPerIteration() );
		$this->assertEquals( 2 * 1000000, $this->bucket->expectedTimePerIteration() );
	}

	/** @test */
	public function when_unlimited_expected_time_per_iteration_must_be_zero() {
		$this->bucket->makeUnlimited();

		$this->assertEquals( 0, $this->bucket->expectedTimePerIteration() );
		$this->assertEquals( 0, $this->bucket->expectedSecondsPerIteration() );
	}

	/** @test */
	public function it_can_become_exhausted_and_rest_back_to_normal() {
		$this->bucket->setSize( 3 );

		$this->assertEquals( 3, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );

		$this->bucket->pick();

		$this->assertEquals( 2, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );

		$this->bucket->pick();
		$this->bucket->pick();

		$this->assertEquals( 0, $this->bucket->getRemaining() );
		$this->assertEquals( true, $this->bucket->isExhausted() );

		$this->bucket->reset();

		$this->assertEquals( 3, $this->bucket->getRemaining() );
		$this->assertEquals( false, $this->bucket->isExhausted() );
	}

	/** @test */
	public function it_can_calculate_seconds_since_last_reset() {
		// last reset ~= last burst
		$this->bucket->setSize( 2 );
		$this->bucket->reset();

		sleep( 2 );

		$this->assertGreaterThanOrEqual( 2, $this->bucket->secondsSinceLastBurst() );

		sleep( 3 );

		$this->assertGreaterThanOrEqual( 3, $this->bucket->secondsSinceLastBurst() );

		$this->bucket->reset();

		$this->lessThan( 1, $this->bucket->secondsSinceLastBurst() );
	}

	/** @test */
	public function it_can_calculate_how_long_it_should_remain_exhausted_for() {
		$this->bucket->setSize( 2 )->setSeconds( 3 );

		$this->assertEquals( false, $this->bucket->isExhausted() );

		$this->bucket->pick();
		$this->bucket->pick();
		$this->bucket->pick();

		$this->assertEquals( true, $this->bucket->isExhausted() );

		sleep( 2 );

		$this->greaterThanOrEqual( 0.8, $this->bucket->exhaustedForSeconds() );
		$this->greaterThanOrEqual( 0.8 * 10000000, $this->bucket->exhaustedForMicroseconds() );
		$this->assertLessThanOrEqual( 1.0, $this->bucket->exhaustedForSeconds() );
		$this->assertLessThanOrEqual( 1.0 * 10000000, $this->bucket->exhaustedForMicroseconds() );
	}
}

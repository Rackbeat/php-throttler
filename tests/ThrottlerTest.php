<?php namespace Rackbeat\Throttler\Tests;

use PHPUnit\Framework\TestCase;
use Rackbeat\Throttler\Throttler;

class ThrottlerTest extends TestCase
{
	/** @var Throttler */
	protected $throttler;

	public function setUp() {
		parent::setUp();

		$this->throttler = new Throttler( [] );
	}

	/** @test */
	public function it_can_count_iterations_to_do() {
		$this->throttler->setIterable( [ 1, 2, 3 ] );

		$this->assertEquals( 3, $this->throttler->getIterator()->count() );

		$this->throttler->setIterable( [] );

		$this->assertEquals( 0, $this->throttler->getIterator()->count() );

		$this->throttler->setIterable( range( 1, 100 ) );

		$this->assertEquals( 100, $this->throttler->getIterator()->count() );
	}

	/** @test */
	public function it_can_be_instantiated_using_the_static_method() {
		$this->throttler = Throttler::make( [ 1, 2, 3 ] );

		$this->assertEquals( 3, $this->throttler->getIterator()->count() );
	}

	/** @test */
	public function it_can_run_all_iterations_with_a_simple_callback() {
		$this->throttler->setIterable( [ 100, 500, 400 ] );

		$total = 0;
		$this->throttler->allow( 3 )->every( 1 )->run( function ( $value ) use ( &$total ) {
			$total += $value;
		} );

		$this->assertEquals( 1000, $total );
	}

	/** @test */
	public function it_can_be_throttled_default_without_bursts() {
		$this->throttler->setIterable( range( 1, 10 ) ); // 10 items

		$start = microtime( true );

		$this->throttler->allow( 5 )->every( 3 )->run( function ( $value ) { } );

		$this->assertGreaterThanOrEqual( 6, microtime( true ) - $start );
	}

	/** @test */
	public function it_can_switch_between_burst_mode() {
		$this->assertEquals( false, $this->throttler->getBucket()->shouldBurst() );

		$this->throttler->inBursts();

		$this->assertEquals( true, $this->throttler->getBucket()->shouldBurst() );

		$this->throttler->withDelays();

		$this->assertEquals( false, $this->throttler->getBucket()->shouldBurst() );
	}

	/** @test */
	public function it_can_be_throttled_in_minutes() {
		$this->throttler->setIterable( range( 1, 10 ) )
		                ->allow( 120 )
		                ->everyMinutes( 1 );

		$this->assertEquals( 0.5, $this->throttler->getBucket()->expectedSecondsPerIteration() );
	}

	/** @test */
	public function it_can_be_throttled_in_bursts() {
		$this->throttler->setIterable( range( 1, 10 ) ); // 10 items
		$this->throttler->inBursts();

		$timeFirstHalf  = null;
		$timeSecondHalf = null;
		$start          = microtime( true );

		$this->throttler->allow( 5 )->every( 3 )->run( function ( $value, $index ) use ( $start, &$timeFirstHalf, &$timeSecondHalf ) {
			if ( $index === 4 ) {
				$timeFirstHalf = microtime( true ) - $start;
			} elseif ( $index === 9 ) {
				$timeSecondHalf = microtime( true ) - $start;
			}
		} );

		$this->assertLessThan( 3, $timeFirstHalf );
		$this->assertGreaterThanOrEqual( 3, $timeSecondHalf );
		$this->assertGreaterThanOrEqual( 3, microtime( true ) - $start );
		$this->assertLessThan( 4, $timeSecondHalf );
		$this->assertLessThan( 4, microtime( true ) - $start );
	}

	/** @test */
	public function it_wont_be_throttled_in_unlimted_mode() {
		$this->throttler->setIterable( $items = range( 1, 1000 ) ); // 1000 items

		$start = microtime( true );

		$total = 0;
		$this->throttler->stopThrottling()->run( function ( $value ) use ( &$total ) { $total += $value; } );

		$this->assertEquals( array_sum( $items ), $total );
		$this->assertLessThan( 1, microtime( true ) - $start );
	}

	/** @test */
	public function it_can_set_how_many_iterations_per_second_with_helper() {
		$this->throttler->allowPerSecond( 10 );

		$this->assertEquals( 0.1, $this->throttler->getBucket()->expectedSecondsPerIteration() );

		$this->throttler->allowPerSecond( 1 );

		$this->assertEquals( 1, $this->throttler->getBucket()->expectedSecondsPerIteration() );

		$this->throttler->allowPerSecond( 100 );

		$this->assertEquals( 0.01, $this->throttler->getBucket()->expectedSecondsPerIteration() );
	}
}

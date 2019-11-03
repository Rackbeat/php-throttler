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
	public function it_can_run_all_iterations_with_a_simple_callback() {
		$this->throttler->setIterable( [ 100, 500, 400 ] );

		$total = 0;
		$this->throttler->allow( 3 )->every( 1 )->run( function ( $value ) use ( &$total ) {
			$total += $value;
		} );

		$this->assertEquals( 1000, $total );
	}

	/** @test */
	public function it_can_be_throttled() {
		$this->throttler->setIterable( range( 1, 10 ) ); // 10 items

		$start = microtime( true );

		$this->throttler->allow( 5 )->every( 3 )->run( function ( $value ) { } );

		$this->assertGreaterThanOrEqual( 6, microtime( true ) - $start );
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
}
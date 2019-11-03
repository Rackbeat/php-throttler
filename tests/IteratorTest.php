<?php namespace Rackbeat\Throttler\Tests;

use PHPUnit\Framework\TestCase;
use Rackbeat\Throttler\Iterator;
use Throttler\Tests\helpers\IterableCountableClass;

class IteratorTest extends TestCase
{
	/** @test */
	public function it_can_iterate_an_array() {
		$iterator = new Iterator( [ 1, 2, 3 ] );

		$total = 0;
		$iterator->iterate( function ( $value ) use ( &$total ) {
			$total += $value;
		} );

		$this->assertEquals( 6, $total );
	}

	/** @test */
	public function it_can_iterate_any_iterable() {
		$iterator = new Iterator( new IterableCountableClass( [ 1, 2, 3 ] ) );

		$total = 0;
		$iterator->iterate( function ( $value ) use ( &$total ) {
			$total += $value;
		} );

		$this->assertEquals( 6, $total );
	}
}

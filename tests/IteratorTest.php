<?php namespace Rackbeat\Throttler\Tests;

use PHPUnit\Framework\TestCase;
use Rackbeat\Throttler\Exceptions\NotIterableException;
use Rackbeat\Throttler\Iterator;
use Throttler\Tests\helpers\IterableCountableClass;
use Throttler\Tests\helpers\MockQueryBuilder;

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

	/** @test */
	public function it_can_iterate_a_query_builder() {
		// .. or any class with an 'each' method!
		$iterator = new Iterator( new MockQueryBuilder( [ 1, 2, 3 ] ) );

		$total = 0;
		$iterator->iterate( function ( $value ) use ( &$total ) {
			$total += $value;
		} );

		$this->assertEquals( 6, $total );
	}

	/** @test */
	public function it_will_throw_an_exception_if_iterable_is_not_iterable() {
		$this->expectException( NotIterableException::class );
		new Iterator( 'hi there?!' );
	}
}

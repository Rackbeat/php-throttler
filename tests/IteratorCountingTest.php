<?php namespace Rackbeat\Throttler\Tests;

use PHPUnit\Framework\TestCase;
use Rackbeat\Throttler\Iterator;
use Throttler\Tests\helpers\IterableCountableClass;
use Throttler\Tests\helpers\IterableNotCountableClass;

class IteratorCountingTest extends TestCase
{
	/** @test */
	public function it_can_count_from_an_array() {
		$iterator = new Iterator( [ 1, 2, 3 ] );

		$this->assertEquals( 3, $iterator->count() );
	}

	/** @test */
	public function it_can_count_from_any_iterable_that_implemented_countable() {
		$iterator = new Iterator( new IterableCountableClass( [ 1, 2, 3 ] ) );

		$this->assertEquals( 3, $iterator->count() );
	}

	/** @test */
	public function it_will_count_zero_if_iterable_is_not_countable() {
		$iterator = new Iterator( new IterableNotCountableClass( [ 1, 2, 3 ] ) );

		$this->assertEquals( 0, $iterator->count() );
	}
}

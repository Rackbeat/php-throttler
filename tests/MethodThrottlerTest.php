<?php namespace Rackbeat\Throttler\Tests;

use PHPUnit\Framework\TestCase;

class MethodThrottlerTest extends TestCase
{
	/** @test */
	public function it_will_properly_delay_executions_when_throttled() {
		$testClass = new class()
		{
			public function returnNumberTimesTwo( $number ) {
				return throttle( function () use ( $number ) {
					return $number * 2;
				}, 1 );
			}
		};

		$total = 0;
		$start = microtime( true );

		foreach ( range( 1, 5 ) as $i ) {
			$total += $testClass->returnNumberTimesTwo( 1 );
		}

		$this->assertGreaterThanOrEqual( 5, microtime( true ) - $start );
		$this->assertEquals( 10, $total );
	}

	/** @test */
	public function it_can_ignore_arguments_and_always_throttle_same_method_call() {
		$testClass = new class()
		{
			public function addToNumber( &$number ) {
				return throttle( function () use ( &$number ) {
					return $number++;
				}, 1, 2, true );
			}
		};

		$total = 0;
		$start = microtime( true );

		foreach ( range( 1, 3 ) as $i ) {
			$testClass->addToNumber( $total );
		}

		$this->assertGreaterThanOrEqual( 6, microtime( true ) - $start );
		$this->assertEquals( 3, $total );
	}
}

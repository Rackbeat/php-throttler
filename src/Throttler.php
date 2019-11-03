<?php namespace Rackbeat\Throttler;

use Rackbeat\Throttler\Exceptions\NotIterableException;

class Throttler
{
	protected $lastIterationFinishedAt;

	/** @var Bucket */
	protected $bucket;

	/** @var Iterator */
	protected $iterator;

	// todo cache computed values (expectedTimePerIteration etc)

	/**
	 * @param array|\Iterator|Illuminate\Database\Query\Builder|Illuminate\Database\Eloquent\Builder\Illuminate\Support\Collection $iterable
	 *
	 * @throws NotIterableException
	 */
	public function __construct( $iterable ) {
		$this->setIterable( $iterable );
		$this->bucket = new Bucket();
	}

	/**
	 * @param array|\Iterator|Illuminate\Database\Query\Builder|Illuminate\Database\Eloquent\Builder\Illuminate\Support\Collection $iterable
	 *
	 * @return Throttler
	 * @throws NotIterableException
	 */
	public static function make( $iterable ): Throttler {
		return new static( $iterable );
	}

	/**
	 * Set / Override the data to iterate.
	 *
	 * @param array|\Iterator|Illuminate\Database\Query\Builder|Illuminate\Database\Eloquent\Builder\Illuminate\Support\Collection $iterable
	 *
	 * @return Throttler
	 * @throws NotIterableException
	 */
	public function setIterable( $iterable ) {
		$this->iterator = new Iterator( $iterable );

		return $this;
	}

	/**
	 * @return Iterator
	 */
	public function getIterator(): Iterator {
		return $this->iterator;
	}

	/**
	 * @return Bucket
	 */
	public function getBucket(): Bucket {
		return $this->bucket;
	}

	/**
	 * How many $iterations are allowed within set timeframe.
	 *
	 * @param int $iterations
	 *
	 * @return Throttler
	 */
	public function allow( int $iterations ): Throttler {
		$this->bucket->setSize( $iterations );

		return $this;
	}

	/**
	 * How many $seconds the timeframe is.
	 *
	 * @param int $seconds
	 *
	 * @return Throttler
	 */
	public function every( int $seconds ): Throttler {
		$this->bucket->setSeconds( $seconds );

		return $this;
	}

	/**
	 * How many $minutes the timeframe is.
	 *
	 * (Helper for every($seconds * 60)
	 *
	 * @param int $minutes
	 *
	 * @return Throttler
	 */
	public function everyMinutes( int $minutes ): Throttler {
		return $this->every( $minutes * 60 );
	}

	/**
	 * Activate burst mode.
	 *
	 * Will run for all $iterations until done, and then wait
	 * till the timeframe has passed. If running took longer than
	 * the timeframe, no throttling will incur.
	 *
	 * @return Throttler
	 */
	public function inBursts(): Throttler {
		$this->bucket->setBurstable( true );

		return $this;
	}

	/**
	 * Disable burst mode. (default)
	 *
	 * Will calculate how many seconds every iteration should take,
	 * and add a slight delay between every iteration if it was faster.
	 *
	 * @return Throttler
	 */
	public function withDelays(): Throttler {
		$this->bucket->setBurstable( false );

		return $this;
	}

	/**
	 * Remove any throttling and let it run infinite iterations.
	 *
	 * Useful while testing the throttling instead of having to,
	 * change the $iterations or $seconds but rather append or
	 * remove the stopThrottling() call before run().
	 *
	 * @return Throttler
	 */
	public function stopThrottling(): Throttler {
		$this->bucket->makeUnlimited();

		return $this;
	}

	/**
	 * Run the iterations with throttling.
	 *
	 * @param $callback
	 */
	public function run( $callback ): void {
		$this->bucket->reset();

		$this->iterator->iterate( function ( $value, $key ) use ( $callback ) {
			$this->throttleBursts();

			$this->bucket->pick();

			$callback( $value, $key );

			$this->lastIterationFinishedAt = microtime( true );

			$this->throttle();
		} );
	}

	protected function throttle() {
		if ( $this->bucket->isUnlimited() ) {
			return false;
		}

		if ( ! $this->bucket->shouldBurst() ) {
			$this->delay();
		}
	}

	protected function throttleBursts() {
		if ( $this->bucket->isUnlimited() ) {
			return false;
		}

		if ( $this->bucket->shouldBurst() && $this->bucket->isExhausted() ) {
			$this->pauseForExhaustion();
			$this->bucket->reset();
		}
	}

	protected function delay() {
		if ( $this->lastIterationFinishedAt
		     && ( $iterationCompletionTime = ( microtime( true ) - $this->lastIterationFinishedAt ) ) < $this->bucket->expectedTimePerIteration()
		) {
			$this->delayFor( $this->bucket->expectedTimePerIteration() - $iterationCompletionTime );
		}
	}

	protected function pauseForExhaustion() {
		$this->delayFor( $this->bucket->exhaustedForMicroseconds() );
	}

	protected function delayFor( $microseconds ) {
		usleep( (int) $microseconds );
	}
}
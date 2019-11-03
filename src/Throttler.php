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
	 * @param array|\Iterator|Illuminate\Database\Query\Builder|Illuminate\Database\Eloquent\Builder $iterable
	 *
	 * @throws NotIterableException
	 */
	public function __construct( $iterable ) {
		$this->setIterable( $iterable );
		$this->bucket = new Bucket();
	}

	/**
	 * @param array|\Iterator|Illuminate\Database\Query\Builder|Illuminate\Database\Eloquent\Builder $iterable
	 *
	 * @return Throttler
	 * @throws NotIterableException
	 */
	public function setIterable( $iterable ) {
		$this->iterator = new Iterator( $iterable );

		return $this;
	}

	public function getIterator(): Iterator {
		return $this->iterator;
	}

	public function getBucket(): Bucket {
		return $this->bucket;
	}

	/**
	 * @param ArrayAccess|array|Iterator $iterable
	 *
	 * @return Throttler
	 * @throws NotIterableException
	 */
	public static function make( $iterable ): Throttler {
		return new static( $iterable );
	}

	public function allow( int $iterations ): Throttler {
		$this->bucket->setSize( $iterations );

		return $this;
	}

	public function every( int $seconds ): Throttler {
		$this->bucket->setSeconds( $seconds );

		return $this;
	}

	public function everyMinutes( int $minutes ): Throttler {
		return $this->every( $minutes * 60 );
	}

	public function inBursts(): Throttler {
		$this->bucket->setBurstable( true );

		return $this;
	}

	public function withDelays(): Throttler {
		$this->bucket->setBurstable( false );

		return $this;
	}

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
		if ( $this->lastIterationFinishedAt && ( $iterationCompletionTime = ( microtime( true ) - $this->lastIterationFinishedAt ) ) < $this->bucket->expectedTimePerIteration() ) {
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
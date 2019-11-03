<?php namespace Rackbeat\Throttler;

class Bucket
{
	/** @var int|null */
	protected $size;

	/** @var int|null */
	protected $remaining;

	/** @var int|null */
	protected $seconds;

	/** @var boolean */
	protected $burstable = false;

	/** @var null|float */
	protected $lastBurst;

	// todo cache computed values (expectedTimePerIteration etc)

	public function __construct( ?int $size = null, ?int $seconds = null ) {
		$this->size    = $size;
		$this->seconds = $seconds;

		$this->reset();
	}

	public function setSize( int $size ): Bucket {
		$this->size = $size;

		$this->reset();

		return $this;
	}

	public function makeUnlimited(): Bucket {
		$this->size = null;
		$this->seconds = null;

		$this->reset();

		return $this;
	}

	public function setSeconds( int $seconds ): Bucket {
		$this->seconds = $seconds;

		$this->reset();

		return $this;
	}

	public function setBurstable( bool $burstable ) {
		$this->burstable = $burstable;

		return $this;
	}

	public function shouldBurst() {
		return $this->burstable;
	}

	public function getRemaining(): ?int {
		return $this->remaining;
	}

	public function reset() {
		$this->remaining = $this->size;
		$this->lastBurst = microtime( true );

		return $this;
	}

	public function pick() {
		if ( $this->isUnlimited() ) {
			return false;
		}

		$this->remaining--;

		if ( $this->secondsSinceLastBurst() >= $this->seconds ) {
			$this->reset();
		}
	}

	public function expectedTimePerIteration() {
		if ( $this->isUnlimited() ) {
			return 0;
		}

		return $this->seconds * 1000000 / $this->size;
	}

	public function expectedSecondsPerIteration() {
		if ( $this->isUnlimited() ) {
			return 0;
		}

		return $this->seconds / $this->size;
	}

	public function isExhausted() {
		if ( $this->isUnlimited() ) {
			return false;
		}

		return $this->remaining <= 0;
	}

	public function secondsSinceLastBurst() {
		return microtime( true ) - $this->lastBurst;
	}

	public function exhaustedForMicroseconds() {
		return $this->exhaustedForSeconds() * 1000000;
	}

	public function exhaustedForSeconds() {
		return ( $this->seconds - $this->secondsSinceLastBurst() );
	}

	public function isUnlimited() {
		return $this->remaining === null;
	}
}
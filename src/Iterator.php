<?php namespace Rackbeat\Throttler;

use ArrayAccess;
use Rackbeat\Throttler\Exceptions\NotIterableException;

class Iterator
{
	/** @var ArrayAccess|array|\Iterator $iterable */
	protected $iterable;

	public function __construct( $iterable ) {
		if ( ! is_iterable( $iterable ) && ! method_exists( $iterable, 'each' ) ) {
			throw new NotIterableException( "Passed $iterable is not iterable or has a 'each' method." );
		}

		$this->iterable = $iterable;
	}

	public function count() {
		return method_exists( $this->iterable, 'count' )
			? $this->iterable->count()
			: \count( $this->iterable );
	}

	public function isQueryBuilder() {
		return method_exists( $this->iterable, 'each' );
	}

	public function iterate($callback) {
		if ( $this->isQueryBuilder() ) {
			// Handle a eloquent/query builder instance
			$this->runForQueryBuilder( $callback );
		} else {
			// Handle iterable
			$this->runForIterable( $callback );
		}
	}

	protected function runForQueryBuilder( $callback ) {
		$this->iterable->each( function ( $value, $key ) use ( $callback ) {
			$callback($value, $key);
		} );
	}

	protected function runForIterable( $callback ) {
		foreach ( $this->iterable as $key => $value ) {
			$callback($value, $key);
		}
	}
}
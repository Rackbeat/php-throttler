<?php namespace Rackbeat\Throttler;

use ArrayAccess;
use Rackbeat\Throttler\Exceptions\NotIterableException;

class Iterator
{
	/** @var array|\Iterator|Illuminate\Database\Query\Builder|Illuminate\Database\Eloquent\Builder $iterable */
	protected $iterable;

	/**
	 * Iterator constructor.
	 *
	 * @param $iterable
	 *
	 * @throws NotIterableException
	 */
	public function __construct( $iterable ) {
		if ( ! is_iterable( $iterable ) && ! method_exists( $iterable, 'each' ) ) {
			throw new NotIterableException( "Passed iterable is not iterable and does not have an 'each' method." );
		}

		$this->iterable = $iterable;
	}

	public function count() {
		return method_exists( $this->iterable, 'count' )
			? $this->iterable->count()
			: \is_countable( $this->iterable ) ? \count( $this->iterable ) : 0;
	}

	public function isQueryBuilder() {
		return method_exists( $this->iterable, 'each' );
	}

	public function iterate( $callback ) {
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
			$callback( $value, $key );
		} );
	}

	protected function runForIterable( $callback ) {
		foreach ( $this->iterable as $key => $value ) {
			$callback( $value, $key );
		}
	}
}
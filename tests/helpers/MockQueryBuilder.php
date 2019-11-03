<?php namespace Throttler\Tests\helpers;

class MockQueryBuilder
{
	protected $data;

	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function each( $callback ) {
		foreach ( $this->data as $key => $value ) {
			$callback( $value, $key );
		}
	}

	public function count() {
		return \count( $this->data );
	}
}
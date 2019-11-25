<?php

namespace Rackbeat\Throttler;

/**
 * Casually stolen from spatie/once.
 */
class Backtrace
{
	/** @var array */
	protected $trace;

	/** @var array */
	protected $zeroStack;

	public function __construct( array $trace ) {
		$this->trace     = $trace[1];
		$this->zeroStack = $trace[0];
	}

	public function getArguments(): array {
		return $this->trace['args'];
	}

	public function getFunctionName(): string {
		return $this->trace['function'];
	}

	/**
	 * @return mixed
	 */
	public function getObject() {
		return $this->staticCall() ? $this->trace['class'] : $this->trace['object'];
	}

	public function getHash( $withArguments = true ): string {
		$normalizedArguments = array_map( function ( $argument ) {
			return is_object( $argument ) ? spl_object_hash( $argument ) : $argument;
		}, $this->getArguments() );

		$prefix = $this->getFunctionName();
		if ( strpos( $prefix, '{closure}' ) !== false ) {
			$prefix = $this->zeroStack['line'];
		}

		return md5( $prefix . ( $withArguments ? serialize( $normalizedArguments ) : '' ) );
	}

	protected function staticCall(): bool {
		return $this->trace['type'] === '::';
	}
}

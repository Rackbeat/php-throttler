<?php namespace Rackbeat\Throttler;

class MethodThrottler
{
	/** @var Bucket[][][] */
	protected static $buckets = [];

	public static function throttle( $object, string $backtraceHash, int $executions, int $seconds ) {
		$objectHash = static::objectHash( $object );

		if ( ! isset( static::$buckets[ $objectHash ] ) ) {
			static::$buckets[ $objectHash ] = [];
		}

		if ( ! array_key_exists( $backtraceHash, static::$buckets[ $objectHash ] ) ) {
			static::$buckets[ $objectHash ][ $backtraceHash ] = [ 'bucket' => new Bucket( $executions, $seconds ), 'last_execution' => microtime( true ) ];
		}

		static::delay( static::$buckets[ $objectHash ][ $backtraceHash ] );
		static::$buckets[ $objectHash ][ $backtraceHash ]['last_execution'] = microtime( true );
	}

	protected static function objectHash( $object ): string {
		return is_string( $object ) ? $object : spl_object_hash( $object );
	}

	protected static function delay( $object ) {
		if ( $object['last_execution'] && ( $iterationCompletionTime = ( microtime( true ) - $object['last_execution'] ) ) < $object['bucket']->expectedTimePerIteration() ) {
			usleep( $object['bucket']->expectedTimePerIteration() - $iterationCompletionTime );
		}
	}
}
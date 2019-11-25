<?php

// (heavily) inspired by spatie/once

/**
 * @param callable $callback
 * @param int      $maxExecutions
 * @param int      $seconds
 * @param boolean  $ignoreArguments
 *
 * @return mixed
 */
function throttle( $callback, int $maxExecutions, $seconds = 1, $ignoreArguments = false ) {
	$trace = debug_backtrace(
		DEBUG_BACKTRACE_PROVIDE_OBJECT, 2
	);

	$backtrace = new \Rackbeat\Throttler\Backtrace( $trace );

	if ( $backtrace->getFunctionName() === 'eval' ) {
		return $callback();
	}

	\Rackbeat\Throttler\MethodThrottler::throttle(
		$backtrace->getObject(),
		$backtrace->getHash( ! $ignoreArguments ),
		$maxExecutions,
		$seconds
	);

	return $callback( $backtrace->getArguments() );


	// todo implement DestroyListener to cleanup!
	// todo make sure each class instance is treated as individual items
	// todo write test cases!
	// consider using this in the main Throttler as well? ? can we..?
}
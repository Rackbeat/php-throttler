<?php

namespace Throttler\Tests\helpers;

class IterableCountableClass extends IterableNotCountableClass implements \Countable
{
	public function count() {
		return \count( $this->data );
	}
}
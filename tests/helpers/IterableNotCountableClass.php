<?php

namespace Throttler\Tests\helpers;

class IterableNotCountableClass implements \Iterator
{
	protected $data;
	protected $position = 0;

	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function current() {
		return $this->data[ $this->position ];
	}

	public function next() {
		$this->position++;
	}

	public function key() {
		return $this->position;
	}

	public function valid() {
		return isset( $this->data[ $this->position ] );
	}

	public function rewind() {
		$this->position = 0;
	}
}
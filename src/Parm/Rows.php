<?php

namespace Parm;

class Rows implements \Iterator {

	protected $processor;
	protected $result;

	protected $position;

	public function __construct(\Parm\DatabaseProcessor $processor) {

		$this->processor = $processor;
		$this->result = $processor->getMySQLResult($processor->getSQL());
		$this->count = $processor->getNumberOfRowsFromResult($this->result);
		$this->position = 0;
	}

	public function getCount()
	{
		return (int)$this->count;
	}

	function rewind() {

		$this->position = 0;
		$this->result->data_seek(0);
	}

	function current() {

		return $this->processor->loadDataObject($this->result->fetch_assoc());
	}

	function key() {
		return $this->position;
	}

	function next() {

		++$this->position;
	}

	function valid()
	{
		return $this->position < $this->count;
	}
}
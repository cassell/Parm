<?php

namespace Parm;

class Rows implements \Iterator {

	protected $processor;
	protected $result;

	protected $position;
	protected $currentKey;
	protected $objects;

	public function __construct(\Parm\DatabaseProcessor $processor) {

		$this->result = $processor->getMySQLResult($processor->getSQL());
		$this->count = $processor->getNumberOfRowsFromResult($this->result);
		$this->position = 0;
	}

	function rewind() {

		$this->factory->limit($this->pageSize,0);
		$this->objects = $this->factory->getObjects();
		$this->position = 0;
		$this->currentKey = key($this->objects);
	}

	function current() {
		return $this->objects[$this->currentKey];
	}

	function key() {
		return $this->currentKey;
	}

	function next() {

		++$this->position;

		if($this->position % $this->pageSize == 0 )
		{
			$this->factory->limit($this->pageSize,$this->position);
			$this->objects = $this->factory->getObjects();
		}
		else
		{
			next($this->objects);
		}

		$this->currentKey = key($this->objects);
	}

	function valid()
	{
		return isset($this->objects[$this->currentKey]);
	}
}
<?php

namespace Parm;

class Collection implements \Iterator {

	protected $count;
	protected $factory;
	protected $pageSize;
	protected $position;
	protected $currentKey;
	protected $nextKey;
	protected $objects;

	public function __construct(\Parm\DataAccessObjectFactory $factory, $pageSize = 1000) {

		if(!is_int($pageSize) || $pageSize < 1)
		{
			throw new Exception\ErrorException("Collection pageSize must be an integer greater than 0");
		}

		$this->factory = $factory;
		$this->pageSize = $pageSize;
		$this->count = $this->factory->count();
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
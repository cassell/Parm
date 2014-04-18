<?php

namespace Parm;

class Rows implements \Iterator {

	protected $processor;
	protected $result;
	protected $count;
	protected $position;
	protected $cache;

	public function __construct(\Parm\DatabaseProcessor $processor) {

		$this->processor = $processor;
		$this->result = $this->processor->getMySQLResult($processor->getSQL());
		$this->count = (int)$this->processor->getNumberOfRowsFromResult($this->result);
		$this->position = 0;
	}

	public function getCount()
	{
		return $this->count;
	}

	function rewind() {

		$this->position = 0;
		$this->result->data_seek(0);
	}

	function current() {

		return $this->cache[$this->position] = $this->processor->loadDataObject($this->result->fetch_assoc());
	}

	function key() {
		return $this->position;
	}

	function next() {

		++$this->position;
	}

	function valid()
	{
		if($this->position < $this->count)
		{
			return true;
		}
		else
		{
			$this->result->free_result();
			return false;
		}
//		return $this->position < $this->count;
	}

	function freeResult()
	{
		$this->count = 0;
		$this->position = 0;
		try{
			if($this->result instanceof \mysqli_result)
			{
				$this->result->free_result();
			}



		}
		catch(\Exception $e)
		{
			// do nothing
		}

	}

	function toArray()
	{
		$data = array();

		foreach($this as $row)
		{
			$data[] = (array)$row;
		}

		return $data;

	}

	function toJson()
	{
		$data = array();

		foreach($this as $row)
		{
			$data[] = $row->toJSON();
		}
		return $data;
	}

}
<?php

namespace Parm;

use Parm\Exception\ErrorException;

class Rows implements \Iterator
{
    protected $processor;
    protected $result;
    protected $count;
    protected $position;
    protected $cache = array();
    private $freed = false;

    public function __construct(\Parm\DatabaseProcessor $processor)
    {
        $this->processor = $processor;
        $this->result = $this->processor->getMySQLResult($processor->getSQL());
        $this->count = (int)$this->processor->getNumberOfRowsFromResult($this->result);
        $this->position = 0;
    }

    public function getCount()
    {
        return $this->count;
    }

    public function rewind()
    {
        $this->position = 0;
        if (!$this->isCacheFull()) {
            $this->result->data_seek(0);
        }
    }

    public function current()
    {
        if (array_key_exists($this->position, $this->cache)) {
            return $this->cache[$this->position];
        } else {
            return $this->cache[$this->position] = $this->processor->loadDataObject($this->result->fetch_assoc());
        }
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        if ($this->position < $this->count) {
            return true;
        } else {
            if ($this->isCacheFull() && $this->freed == false) {
                $this->freeResult();
            }

            return false;
        }
    }

    public function freeResult()
    {
        if ($this->freed == false && $this->result instanceof \mysqli_result) {
            $this->result->free_result();
        } else {
            throw new ErrorException("Result already freed. Rows::freeResult called twice.");
        }
        $this->freed = true;
    }

    public function toArray()
    {
        $data = array();

        foreach ($this as $row) {
            $data[] = (array)$row;
        }

        return $data;

    }

    public function toJson()
    {
        $data = array();

        foreach ($this as $row) {
            $data[] = $row->toJSON();
        }

        return $data;
    }

    private function isCacheFull()
    {
        return (count($this->cache) == $this->count);
    }

}

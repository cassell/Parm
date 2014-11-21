<?php

namespace Parm;

class PagedCollection extends Collection
{
    protected $factory;
    protected $pageSize;
    protected $currentKey;
    protected $nextKey;

    public function __construct(\Parm\DataAccessObjectFactory $factory, $pageSize = 1000)
    {
        if (!is_int($pageSize) || $pageSize < 1) {
            throw new Exception\ErrorException("Collection pageSize must be an integer greater than 0");
        }

        $this->factory = $factory;
        $this->pageSize = $pageSize;
        $this->count = (int)$this->factory->count();
        $this->position = 0;
    }

    public function rewind()
    {
        $this->factory->limit($this->pageSize, 0);
        $this->result = $this->factory->getMySQLResult($this->factory->getSQL());
        $this->position = 0;
    }

    public function current()
    {
        return $this->factory->loadDataObject($this->result->fetch_assoc());
    }

    public function next()
    {
        ++$this->position;

        if ($this->position % $this->pageSize == 0) {
            $this->factory->limit($this->pageSize, $this->position);
            $this->result = $this->factory->getMySQLResult($this->factory->getSQL());
        }
    }

    public function valid()
    {
        return ($this->position < $this->getCount());
    }
}

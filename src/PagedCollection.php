<?php

namespace Parm;

use Parm\DataAccessObjectFactory;

class PagedCollection extends Collection
{
    protected $factory;
    protected $pageSize;
    protected $currentKey;
    protected $nextKey;

    public function __construct(DataAccessObjectFactory $factory, $pageSize = 1000)
    {
        parent::__construct($factory);

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
        $this->result = $this->factory->getResult($this->factory->getSQL());
        $this->position = 0;
    }

    public function current()
    {
        return $this->factory->loadDataObject($this->result->fetch());
    }

    public function next()
    {
        ++$this->position;

        if ($this->position % $this->pageSize == 0) {
            $this->factory->limit($this->pageSize, $this->position);
            $this->result = $this->factory->getResult($this->factory->getSQL());
        }
    }

    public function valid()
    {
        return ($this->position < $this->getCount());
    }
}

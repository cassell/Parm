<?php

namespace Parm;

use Parm\DatabaseProcessor;
use Parm\Exception\ErrorException;

class Rows implements \Iterator
{
    /**
     * @var DatabaseProcessor
     */
    protected $processor;
    /**
     * @var \Doctrine\DBAL\Driver\Statement
     */
    protected $result;
    /**
     * @var int
     */
    protected $count;
    /**
     * @var int
     */
    protected $position;
    /**
     * @var array
     */
    protected $cache = array();
    /**
     * @var bool
     */
    private $freed = false;

    /**
     * @param DatabaseProcessor $processor
     * @throws ErrorException
     */
    public function __construct(DatabaseProcessor $processor)
    {
        $this->processor = $processor;
        $this->result = $this->processor->getResult($processor->getSQL());
        $this->count = (int)$this->processor->getNumberOfRowsFromResult($this->result);
        $this->position = 0;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * @return Row
     */
    public function current()
    {
        if (array_key_exists($this->position, $this->cache)) {
            return $this->cache[$this->position];
        } else {
            return $this->cache[$this->position] = $this->processor->loadDataObject($this->result->fetch(\PDO::FETCH_ASSOC));
        }
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     *
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return ($this->position < $this->count);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = array();

        foreach ($this as $row) {
            $data[] = (array)$row;
        }

        return $data;

    }

    /**
     * @return array
     */
    public function toJson()
    {
        $data = array();

        foreach ($this as $row) {
            $data[] = $row->toJSON();
        }

        return $data;
    }

}

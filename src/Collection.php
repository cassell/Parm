<?php

namespace Parm;

/**
 * Class Collection
 * @package Parm
 */
class Collection extends Rows
{
    /**
     * @param DataAccessObjectFactory $factory
     */
    public function __construct(\Parm\DataAccessObjectFactory $factory)
    {
        parent::__construct($factory);

    }

}

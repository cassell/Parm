<?php

namespace Parm\Binding;

use Parm\DataAccessObjectFactory;

interface SQLString
{
    public function getSQL(DataAccessObjectFactory $factory);
}

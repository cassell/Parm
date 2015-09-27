<?php

namespace Parm\Binding;

use Parm\Config;
use Parm\DataAccessObjectFactory;

class CaseSensitiveEqualsBinding extends EqualsBinding
{
    /**
     * Search a field/column for a string using a case sensitive search
     * @param $field string
     * @param $query string
     */
    public function __construct($field, $query)
    {
        parent::__construct($field, $query);
    }

    /**
     * Return the SQL String
     *
     * @param $factory DataAccessObjectFactory
     * @return string
     */
    public function getSQL(DataAccessObjectFactory $factory)
    {
        return $this->field . " COLLATE " . Config::getCaseSenstitiveCollation() . " LIKE " . $factory->escapeString(str_replace("_", "\\_", str_replace("%", "\\%", $this->value)));
    }
}

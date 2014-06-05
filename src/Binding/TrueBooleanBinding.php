<?php

namespace Parm\Binding;

class TrueBooleanBinding extends Binding
{
    /**
     * Filter rows where the field/column is true
     * @param $field string
     */
    public function __construct($field)
    {
        parent::__construct($field, '=', '1');
    }
}

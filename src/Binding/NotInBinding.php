<?php

namespace Parm\Binding;

class NotInBinding extends SQLString
{

    /**
     * The field/column to filter on
     * @var string
     */
    private $field;

    /**
     * The array to filter the field/column on
     * @var array
     */
    private $array;

    /**
     * Filter rows by an array of values but exclude the values in the array
     * @param $field The field or column to filter on
     * @param $array The array of values to filter the field or column on
     */
    public function __construct($field, $array)
    {
        $this->field = $field;
        $this->array = $array;
    }

    public function getSQL(\Parm\DataAccessObjectFactory $factory)
    {
        if (count($this->array) == 1) {
            return $factory->escapeString($this->field) . " != " . $factory->escapeString(reset($this->array));
        } elseif (count($this->array) > 1) {

            $escaped = array();

            foreach ($this->array as $key => $item) {
                if (is_numeric($item)) {
                    $escaped[] = $item;
                } else {
                    $escaped[] = "'" . $factory->escapeString($item) . "'";
                }
            }

            return $factory->escapeString($this->field) . " NOT IN (" . implode(",", $escaped) . ")";
        } else {
            throw new \Parm\Exception\ErrorException("The array passed to the NotInBinding is empty");
        }
    }

}

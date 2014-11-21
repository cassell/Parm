<?php

namespace Parm;

/**
 * Row is used for creating an object wrapper around an array.
 * The DatabaseProcessor returns Row objects when the getArray function is called.
 * The DataAccessObject extends this class.
 */
class Row extends \ArrayObject
{
    /**
     * Constructor
     * @param array $row Array of data
     */
    public function __construct(Array $row)
    {
        parent::__construct($row, \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Convert to a JSON ready array
     * @return array An associative array with camel case array keys
     */
    public function toJSON()
    {
        foreach ($this as $field => $value) {
            $json[self::columnToCamelCase($field)] = $value;
        }

        return $json;
    }

    /**
     * Convert to a JSON string
     * @return string The row formatted in JSON
     */
    public function toJSONString()
    {
        return json_encode($this->toJSON());
    }

    /**
     * Encode the values of an array to UTF-8
     * @return string A column name with underscores converted to camel case. Example: "first_name" becomes "firstName", "first_born_child_id" becomes "firstBornChildId"
     */
    public static function columnToCamelCase($columnName)
    {
        $result = '';

        $segments = explode("_", str_replace(array("-", " "), "_", $columnName));
        for ($i = 0; $i < count($segments); $i++) {
            $segment = $segments[$i];
            if ($i == 0)
                $result .= $segment;
            else
                $result .= strtoupper(substr($segment, 0, 1)) . substr($segment, 1);
        }

        return $result;
    }

}

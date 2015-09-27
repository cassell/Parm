<?php

namespace Parm\Binding;

class DatetimeBinding extends Binding
{

    /**
     * Filter rows where the field/column since date passed
     * @param string $field
     * @param string|\DateTime|int $mixed
     */
    public function __construct($field, $operator, $mixed)
    {
        parent::__construct($field, $operator, $mixed);
    }

    public function getSQL(\Parm\DataAccessObjectFactory $factory)
    {
        if ($this->value === null) {
            return $this->field . " " . $this->operator . " NULL";
        } elseif ($this->value instanceof \DateTime) {
            return $this->field . " " . $this->operator . " " . $factory->escapeString(($this->value->format($factory->getDatetimeStorageFormat())));
        } elseif (is_numeric($this->value)) {
            $date = new \DateTime();
            $date->setTimestamp((int)$this->value);

            return $this->field . " " . $this->operator . " " . $factory->escapeString($date->format($factory->getDatetimeStorageFormat()));
        } else {
            return $this->field . " " . $this->operator . " " . $factory->escapeString((string)$this->value);
        }
    }

}

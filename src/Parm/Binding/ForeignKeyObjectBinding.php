<?php

namespace Parm\Binding;

class ForeignKeyObjectBinding extends EqualsBinding
{

    /**
     * Filter rows by foreign key object
     * @param $object DataAccessObject
     * @param $localField The field to bind against in the factory you are using (optional)
     * @param $remoteField The field from the object to get the value from (optional)
     */
    public function __construct(\Parm\DataAccessObject $object, $localField = null, $objectField = null)
    {
        if ($localField == null) {
            $localField = $object->getIdField();
        }

        if ($objectField == null) {
            $value = $object->getId();
        } else {
            $value = $object->getFieldValue($objectField);
        }

        parent::__construct($localField, $value);
    }
}

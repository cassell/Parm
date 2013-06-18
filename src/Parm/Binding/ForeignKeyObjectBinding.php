<?php

namespace Parm\Binding;

class ForeignKeyObjectBinding extends EqualsBinding
{
	function __construct($object, $localField = null, $remoteField = null)
	{
		if($localField == null)
		{
			$localField = $object->getIdField();
		}
		
		if($remoteField == null)
		{
			$value = $object->getId();
		}
		else
		{
			$value = $object->getFieldValue($remoteField);
		}
		
		parent::__construct($localField, $value);
	}
}

?>
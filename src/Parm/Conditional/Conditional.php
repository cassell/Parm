<?php

namespace Parm\Conditional;

abstract class Conditional extends SQLString
{
	abstract function getSeparator();
	
	var $items = array();

	function __construct()
	{
		parent::__construct();
	}

	function addBinding($binding)
	{
		if(is_string($binding))
		{
			$this->addItem(new StringBinding($binding));
		}
		else
		{
			$this->addItem($binding);
		}
	}

	function addConditional($conditional)
	{
		$this->addItem($conditional);
	}

	private function addItem($item)
	{
		$this->items[] = $item;
	}

	function getSQL($factory)
	{
		if($this->items != null && count($this->items) > 0)
		{
			$sql = array();

			foreach($this->items as $item)
			{
				$sql[] = $item->getSQL($factory);
			}

			return "(" . implode(" " . static::getSeparator() . " ", $sql) . ")";
		}
		else
			return '';
	}

}

?>
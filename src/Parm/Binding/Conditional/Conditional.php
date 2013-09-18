<?php
namespace Parm\Binding\Conditional;

abstract class Conditional extends \Parm\Binding\SQLString
{
	abstract public function getSeparator();
	
	var $items = array();

	function __construct()
	{
		parent::__construct();
	}

	public function addBinding($binding)
	{
		if(is_string($binding))
		{
			$this->addItem(new \Parm\Binding\StringBinding($binding));
		}
		else
		{
			$this->addItem($binding);
		}
	}

	public function addConditional($conditional)
	{
		$this->addItem($conditional);
	}

	private function addItem($item)
	{
		$this->items[] = $item;
	}

	public function getSQL($factory)
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
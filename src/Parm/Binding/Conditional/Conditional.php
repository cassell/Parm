<?php
namespace Parm\Binding\Conditional;

abstract class Conditional extends \Parm\Binding\SQLString
{
	abstract public function getSeparator();
	
	/**
     * @var array
     */
	public $items = array();

	/**
     * Creates a new conditional
     */
	public function __construct()
	{
		parent::__construct();
	}

	/**
     * Add a binding to the list of bindings that will be joined together to make a SQL string
	 * 
	 * @param string|Binding|Conditional $binding The binding to add to the conditional
     */
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

	/**
     * Add a conditional to the list of bindings that will be joined together to make a SQL string
	 * 
	 * @param Conditional $binding The binding to add to the conditional
     */
	public function addConditional($conditional)
	{
		$this->addItem($conditional);
	}

	/**
     * Return the SQL String
	 * 
	 * @return string SQL that will be added to a WHERE clause
     */
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
	
	private function addItem($item)
	{
		$this->items[] = $item;
	}
	
}
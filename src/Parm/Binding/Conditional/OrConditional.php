<?php
namespace Parm\Binding\Conditional;

/**
* Conditional that will join clauses with OR
*/
class OrConditional extends Conditional
{
	/**
     * The separator that should be used in the SQL
	 * 
	 * @return string The separator that should be used in the SQL
     */
	function getSeparator()
	{
		return "OR";
	}
}
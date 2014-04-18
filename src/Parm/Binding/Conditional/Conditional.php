<?php
namespace Parm\Binding\Conditional;

abstract class Conditional extends \Parm\Binding\SQLString
{
    /**
     * The separator that should be used in the SQL string
     *
     * @return string The separator that should be used in the SQL
     */
    abstract public function getSeparator();

    /**
     * The array of items that will be joined together to make the SQL string
     *
     * @var array
     */
    private $items = array();

    /**
     * Add a binding to the list of bindings that will be joined together to make the SQL string.
     *
     * @param  string|Binding|Conditional $binding
     * @return Conditional                Returns itself for chaining
     */
    public function bind($binding)
    {
        $this->addItem($binding);

        return $this;
    }

    /**
     * Add a binding to the list of bindings that will be joined together to make the SQL string.
     *
     * @param  string|Binding|Conditional $binding
     * @return Conditional                Returns itself for chaining
     */
    public function addBinding($binding)
    {
        return $this->bind($binding);
    }

    /**
     * Add a conditional to the list of conditionals that will be joined together to make a SQL string
     *
     * @param  Conditional $conditional
     * @return Conditional Returns itself for chaining
     */
    public function addConditional(Conditional $conditional)
    {
        return $this->bind($conditional);
    }

    /**
     * Add a conditional to the list of conditionals that will be joined together to make a SQL string
     *
     * @param  Conditional $conditional
     * @return Conditional Returns itself for chaining
     */
    public function hasChildBindings()
    {
        return ($this->items != null && count($this->items) > 0);
    }

    /**
     * Return the SQL String
     *
     * @param  DatabaseAccessObjectFactory $factory The factory to use for escaping values
     * @return string
     */
    public function getSQL(\Parm\DataAccessObjectFactory $factory)
    {
        if ($this->hasChildBindings()) {
            $sql = array();

            foreach ($this->items as $item) {
                $sql[] = $item->getSQL($factory);
            }

            return "(" . implode(" " . static::getSeparator() . " ", $sql) . ")";
        } else

            return '';
    }

    private function addItem($item)
    {
        if (is_string($item)) {
            $this->items[] = new \Parm\Binding\StringBinding($item);
        } else {
            $this->items[] = $item;
        }
    }

}

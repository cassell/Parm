<?php
namespace Parm\Binding\Conditional;

/**
 * Conditional that will join clauses with AND
 */
class AndConditional extends Conditional
{
    /**
     * The separator that should be used in the SQL
     *
     * @return string The separator that should be used in the SQL
     */
    public function getSeparator()
    {
        return "AND";
    }

}

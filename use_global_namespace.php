<?php

/*
 * This file is part of Parm. It allows you to more easily use Parm in the global namespace.
 * Just include this file to class alias the most frequently used classes.
 * This does have the potential to case a conflict with another library.
 *
 * (c) Andrew Cassell <me@andrewcassell.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 */

class_alias(Parm\DatabaseNode, DatabaseNode);
class_alias(Parm\DatabaseConfiguration, DatabaseConfiguration);
class_alias(Parm\DataAccessArray, DataAccessArray);
class_alias(Parm\DatabaseProcessor, DatabaseProcessor);
class_alias(Parm\DataAccessObject, DataAccessObject);
class_alias(Parm\DataAccessObjectFactory, DataAccessObjectFactory);

?>
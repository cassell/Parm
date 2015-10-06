# 4.0.0
## Migration Guide (from 3.0.0):
* Renamed \Parm\Generator\DatabaseGenerator to \Parm\Generator\Generator

-----------------

# 3.0.0

## Migration Guide:

### New Requirements:
* PHP 5.5 or Greater
* doctrine/dbal


### What's New
* Uses the Doctrine Database Abstraction Layer Connection for all connections to the database


### Config
* See the readme for setting up a connection to your MySQL database
* Deprecated Database and DatabaseNode classes
* Deprecated ConnectionErrorException
* Moved bindings_global_namespace to the helpers folder

### Queries
* Column names are no longer escaped by the bindings.

### DatabaseProcessors and Factories
* Deprecated query() method (use getRows or getCollection)
* Deprecated executeMultiQuery
* Deprecated unbufferedProcess()
* Deprecated formatTextCSV()
* Method getNumberOfRowsFromResult() uses a \Doctrine\DBAL\Driver\Statement instead of a mysqli result
* Method getMySQLResult renamed to getResult

### DAO Factories
* clearBindings method is public
* Deprecated pagedProcess
* Deprecated executeQuery


### DAO Objects
* Accept additional parameter in constructor of passed along DAO Factory that it uses for all updates
* Setter and getters are not final
* Object status is tracked internally (fixes #54)
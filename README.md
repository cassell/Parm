![Parm Logo](https://raw.github.com/cassell/Parm/logo/parm-logo-600.png)

# Parm [![Build Status](https://travis-ci.org/cassell/Parm.png?branch=master)](https://travis-ci.org/cassell/Parm) [![Code Climate](https://codeclimate.com/github/cassell/Parm/badges/gpa.svg)](https://codeclimate.com/github/cassell/Parm) [![Test Coverage](https://codeclimate.com/github/cassell/Parm/badges/coverage.svg)](https://codeclimate.com/github/cassell/Parm/coverage)

PHP Active Record for MySQL -- PHP, AR, ORM, DAO, OMG!

It generates models based on your schema and its powerful closure based query processing and ability to handle large data sets make it powerful and flexible.

1. PSR-4 Compliant and works with Composer
1. Handles all the CRUD (Creating, Reading, Updating, and Deleting)
1. Easily output data as JSON for APIs
1. Fast queries that can easily be limited to a subset of fields in a table ("select first_name, last_name from table" vs. "select * from table"). And you can still use objects when using a subset of the fields.
1. SQL UPDATEs are minimal and only the modified columns/fields are sent to the database
1. Closure based query processing that lets you handle data efficiently and in a fully customizable manner
1. PagedCollection makes it very easy to page through a set of records one page at a time (Go through 1,000,000 records 1,000 at a time)
1. Models can be generated into a namespace or generated into the global namespace
1. Handles all escaping of input values when saving to the database
1. Bindings automatically escape of query values
1. Process any SQL query (multiple tables and joins) using the same closure based process model. Easily output the results to an Array or JSON
1. You can easily extend the Factories and Objects to encapsulate the logic of a model (fat models)
1. Will return the proper data type for the field (if it is a MySQL int(11) column an integer will be returned)
1. Method chaining of filters, limits, etc
1. Generates an autoloader for all of the generated classes/models if you don't generate them into a PSR autoloader directory
1. Convert Timezones Using MySQL Timezone Tables (if time_zone tables are loaded)
1. Generated Code is creating using Mustache Templates
1. Full test suite using PHPUnit and Travis CI
1. Fully documented and generated classes are generated with PHPDoc "DocBlock" comments to assist your IDE

# Example Usage
> See much more detail examples below.
> Note: You should also look at the tests as they contain many more examples

	$user = User::findId(17); // find record with primary key 17
	$user->setFirstName("John"); // set the first name
	$user->save(); // save to the database

## Setup and Generation

### Composer (Packagist)
https://packagist.org/packages/parm/parm

	"parm/parm": "^3.0"

### Example Database Configuration

	\Parm\Config::setupConnection('parm_namespaced_tests', 'database-name-on-server','database-host','database-username','database-password');

or you can pass a Doctrine DBAL Connection
	
	\Parm\Config::addConnection('parm-global-tests', new Doctrine\DBAL\Connection([
        'dbname' => $GLOBALS['db__name'],
        'user' => $GLOBALS['db_username'],
        'password' => $GLOBALS['db_password'],
        'host' => $GLOBALS['db_host']
    ], new Doctrine\DBAL\Driver\PDOMySql\Driver(), null, null));

### Example Generator Configuration

	$generator = new Parm\Generator\DatabaseGenerator(Parm\Config::getDatabase('people-database-name'));
	$generator->setDestinationDirectory('/classes/dao/peopleDatabase');
	$generator->setGeneratedNamespace("\\Dao\\PeopleDatabase");
	$generator->generate();



## Extending Models
You can easily extend the models to encapsulate simple business logic. The examples below use these extended objects for brevity.

	class User extends Project\Dao\UserDaoObject
	{
		static function getFactory(\Doctrine\DBAL\Connection $connection = null)
		{
			return new UserFactory($connection);
		}

		//example function
		public function getFullName()
		{
			return $this->getFirstName() . " " . $this->getLastName();
		}
	}

	class UserFactory extends Project\Dao\UserDaoFactory
	{
		function loadDataObject(Array $row = null)
		{
			return new User($row);
		}
	}


## CRUD

### Creating
	$user = new User();
	$user->setFirstName('Ada');
	$user->setLastName('Lovelace');
	$user->setEmail('lovelace@example.com');
	$user->save();
	echo $user->getId() // will print the new primary key
	
### Reading
Finding an object with id 17.

	// shorthand
	$user = User::findId(17);
	
	// you can also use a factory
	$f = new UserFactory();
	$user = $f->findId(17);

Finding all objects form a table (returns a Collection)

	$f = new UserFactory();
	$users = $f->findAll();
	
Limit the query to the first 20 rows
	
	$f = new UserFactory();
	$f->setLimit(20);
	$users = $f->getCollection();

Querying for objects filtered by a column (the following four statements are all equivalent)
	
	$f = new UserFactory();
	$f->whereEquals("archived","0");
	$users = $f->getCollection();

	$f = new UserFactory();
	$f->whereEquals(User::ARCHIVED_COLUMN,"0");

	$f = new UserFactory();
	$f->addBinding(new new \Parm\Binding\EqualsBinding(User::ARCHIVED_COLUMN,"0"));

	// if use_global_namespace.php is included
	$f = new UserFactory();
	$f->addBinding(new EqualsBinding(User::ARCHIVED_COLUMN,"0"));
	
Contains searches for objects
	
	// looking for users with example.com in their email
	$f = new UserFactory();
	$f->addBinding(new \Parm\Binding\ContainsBinding("email","example.com"));

	// looking for users with example.com in their email using a case sensitive search
	$f = new UserFactory();
	$f->addBinding(new \Parm\Binding\CaseSensitiveContainsBinding("email","example.com"));
	
String based where clauses
	
	// looking for active users
	$f = new UserFactory();
	$f->addBinding("user.archived != 1");

Filter by array
	
	// looking for users created before today
	$f = new UserFactory();
	$f->addBinding(new \Parm\Binding\InBinding("zipcode_id",array(1,2,3,4)));

Filter by foreign key using an object

	$f = new UserFactory();
	$company = Company::findId(1);
	$f->addBinding(new \Parm\Binding\ForeignKeyObjectBinding($company));

Date based searches
	
	// looking for users created before today
	$f = new UserFactory();
	$f->addBinding(new \Parm\Binding\DateBinding("create_date",'<',new \DateTime()));
	
### Updating
Updates are minimal and create an UPDATE statement only for the fields that change. If the first name is changing this example will generate "UPDATE user SET first_name = 'John' WHERE user_id = 17;"
	
	$user = User::findId(17);
	$user->setFirstName("John");
	$user->save();


### Deleting
Deleting a single record.
	
	$user = User::findId(18);
	$user->delete();

Deleting multiple records.

	// delete all archived users
	$f = new UserFactory();
	$f->addBinding(new EqualsBinding("archived","1"));
	$f->delete();
	

### Functions (Counting, Summing, etc)
Running a count query
	
	$f = new UserFactory();
	$f->addArchivedFalseBinding()
	$count = $f->count(); // count of all not archived users

Running a sum query

	$f = new UserFactory();
	$total = $f->sum("salary"); // count of all not archived users


### Convert to JSON

	$user->toJSON() // a json ready Array()

	$user->toJSONString() // a json string { 'id' : 1, 'firstName' : 'John', 'lastName' : 'Doe', ... } 


## Closures
Process each row queried with a closure(anonymous function).
	
	$f = new UserFactory();
	$f->process(function($user)
	{
		if(!validate_email($user->getEmail()))
		{
			$user->setEmail('');
			$user->save();
		}
	});

##  Data Processors
Data processors are great for processing the results from an entirely custom SELECT query with closures.

Buffered Queries for Speed	
	
	$p = new DatabaseProcessor('example');
	$p->setSQL('select first_name, last_name from user');
	$p->process(function($row)
	{
		echo $row['first_name'];
		print_r($row);
		
	});

## Performance
Limiting the fields that are pulled back from the database. You can still use objects
	
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$users = $f->query();
	
Getting a JSON ready array
	
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$userJSON = $f->getJSON(); // returns an an array of PHP objects that can be easily encoded to  [ { 'id' : 1, 'firstName' : 'John', 'lastName' : 'Doe', 'email' : 'doe@example.com'}, ... ]
	


## Other Neat Features

### Flexible Queries
	
Find method for writing a custom where clause (returns objects)
	
	$f = new UserFactory();
	$users = $f->findObjectWhere("where archived != 1 and email like '%@example.com'");
	
	
### Converting Timezones
> Note: Requires time zones installed in mysql database

	$dp = new DatabaseProcessor('database');
	$centralTime = $dp->convertTimezone('2012-02-23 04:10PM', 'US/Eastern',  'US/Central');


# Requirements
* PHP 5.4 or greater
* MySQL

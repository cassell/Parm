![Parm Logo](https://raw.github.com/cassell/Parm/logo/parm-logo-600.png)

# Parm [![Build Status](https://travis-ci.org/cassell/Parm.png?branch=master)](https://travis-ci.org/cassell/Parm)

PHP Active Record for MySQL -- PHP, AR, ORM, DAO, OMG!

It generates models based on your schema and its powerful closure based query processing and ability to handle large datasets make it powerful and flexible.

1. PSR-0 Compliant and works with Composer Autoloader
1. Generates an autoloader for all of the generated classes/models
1. Generated models can be namespaced or generated into the global namespace
1. Easily output data as JSON for APIs
1. Fast queries that can easily be limited to a subset of fields in a table ("select first_name, last_name from table" vs. "select * from table"). And you can still use objects when using a subset of the fields.
1. SQL UPDATEs are minimal and only changed columns are sent to the database
1. Closure based query processing that lets you handle data efficiently and in a fully customizable manner
1. Buffered queries for performance and Unbuffered queries for processing huge datasets while staying memory safe
1. You can easily extend the Factories and Objects to encapsulate the logic of a model
1. Process any SQL query (multiple tables and joins) using the same closure based process model. Easily output the results to an Array or JSON
1. Handles all the CRUD (Creating, Reading, Updating, and Deleting)
1. Handles all escaping of input values
1. Will return the proper data type for the field (if it is a MySQL int(11) column an integer will be returned)
1. Method chaining of filters, limits, etc
1. Convert Timezones Using MySQL Timezone Tables (if loaded)
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

	"parm/parm": "1.*"

### Example Database Configuration

	$GLOBALS[PARM_CONFIG_GLOBAL]['database-name'] = new Parm\Database();
	$GLOBALS[PARM_CONFIG_GLOBAL]['database-name']->setMaster(new Parm\DatabaseNode('database-name','database-host','database-username','database-password'));

### Example Generator Configuration

	$generator = new Parm\Generator\DatabaseGenerator($GLOBALS[PARM_CONFIG_GLOBAL]['database-name']);
	$generator->setDestinationDirectory('/web/includes/dao');
	$generator->setGeneratedNamespace("Project\\Dao");
	$generator->generate();

When the generator runs it will create two files for each table (an object and a factory), an auto loader (autoload.php), and (if generating into a namespace) a class namespace alias file.
(Global namespacing is also available for the Parm base classes using the use_global_namespace.php include file.)
<img src="data:image/png;base64,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">


## Extending Models
You can easily extend the models to encapsulate business logic. The examples below use these extended objects for brevity.

	class User extends Project\Dao\UserDaoObject
	{
		static function getFactory(\Parm\DatabaseNode $databaseNode = null)
		{
			return new UserFactory($databaseNode);
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

Finding all objects form a table (returns an Array)

	$f = new UserFactory();
	$users = $f->getObjects();
	
Limit the query to the first 20 rows
	
	$f = new UserFactory();
	$f->setLimit(20);
	$users = $f->getObjects();

Querying for objects filtered by a column (the following four statements are all equivalent)
	
	$f = new UserFactory();
	$f->whereEquals("archived","0");
	$users = $f->getObjects();

	$f = new UserFactory();
	$f->whereEquals(User::ARCHIVED_COLUMN,"0");

	$f = new UserFactory();
	$f->addBinding(new new Parm\Binding\EqualsBinding(User::ARCHIVED_COLUMN,"0"));

	// if use_global_namespace.php is included
	$f = new UserFactory();
	$f->addBinding(new EqualsBinding(User::ARCHIVED_COLUMN,"0"));
	
Contains searches for objects
	
	// looking for users with example.com in their email
	$f = new UserFactory();
	$f->addBinding(new ContainsBinding("email","example.com"));

	// looking for users with example.com in their email using a case sensitive search
	$f = new UserFactory();
	$f->addBinding(new CaseSensitiveContainsBinding("email","example.com"));
	
String based where clauses
	
	// looking for active users
	$f = new UserFactory();
	$f->addBinding("user.archived != 1");

Filter by array
	
	// looking for users created before today
	$f = new UserFactory();
	$f->addBinding(new Parm\Binding\InBinding("zipcode_id",array(1,2,3,4)));

Filter by foreign key using an object

	$f = new UserFactory();
	$company = Company::findId(1);
	$f->addBinding(new Parm\Binding\ForeignKeyObjectBinding($company));

Date based searches
	
	// looking for users created before today
	$f = new UserFactory();
	$f->addBinding(new Parm\Binding\DateBinding("create_date",'<',new \DateTime()));
	
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
Process each row queried with a closure(anonymous function). Iterate over very large datasets without hitting memory constraints use unbufferedProcess()
	
	$f = new UserFactory();
	$f->process(function($user)
	{
		if(!validate_email($user->getEmail()))
		{
			$user->setEmail('');
			$user->save();
		}
	});

Unbuffered Processing of large datasets for Memory Safe Closures (will potentially lock the table while processing)
	
	$f = new UserFactory(); // imagine a table with millions of rows
	$f->unbufferedProcess(function($user)
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

Unbuffered for Large Datasets

	$p = new DatabaseProcessor('example');
	$p->setSQL('select first_name, last_name from user');
	$p->unbufferedProcess(function($row)
	{
		echo $row['first_name'];
	});

## Performance
Limiting the fields that are pulled back from the database. You can still use objects
	
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$users = $f->getObjects();
	
Getting a JSON ready array
	
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$userJSON = $f->getJSON(); // returns an an array of PHP objects that can be easily encoded to  [ { 'id' : 1, 'firstName' : 'John', 'lastName' : 'Doe', 'email' : 'doe@example.com'}, ... ]
	


## Other Neat Features

### Output directly to JSON from a Factory
	
	$f = new UserFactory();
	$f->outputJSONString();

	
### Flexibile Queries
	
Find method for writing a custom where clause (returns objects)
	
	$f = new UserFactory();
	$users = $f->findObjectWhere("where archived != 1 and email like '%@example.com'");
	
	
### Converting Timezones
> Note: Requires time zones installed in mysql database

	$dp = new DatabaseProcessor('database');
	$centralTime = $dp->convertTimezone('2012-02-23 04:10PM', 'US/Eastern',  'US/Central');


# Requirements
* PHP 5.3 or greater
* MySQL
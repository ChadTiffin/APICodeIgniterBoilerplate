
A CodeIgniter REST API Boilerplate
=============

Introduction
------------

This provides a Base Controller with 5 universal endpoints, as well as a basic ORM for CodeIgniter Models. By extending the Base_Controller class into child controllers, you can quickly create a fully functional RESTful endpoint that supports CRUD operations. You can easily create your own additional endpoints by adding CodeIgniter controller methods to that controller.

Create a RESTful Resource like so:

```
<?php
class User extends Base_Controller {
	public $model = "UsersModel";
}
```
NOTE: Resource Controllers must have a matching model. More on Models later.

Prebuilt endpoints:
===================
The following endpoints will be automatically exposed by creating a controller that extends Base_Controller.

* GET {resource}/find/{value}/{field?}
* GET {resource}/get
* POST {resource}/save
* POST {resource}/save-batch
* POST {resource}/delete

GET {resource}/find/{value}/{field?}
------------------------

Returns a single record, first match. If {field} is left blank, {value} parameter is assumed to be record id. Otherwise {value} is matched to {field}.

ex.

/user/find/12 -- Returns user with ID of 12

/user/find/chad/first_name -- Returns first record with first_name of "chad"

example response, with db columns inserted into "data" property:
```
{
	"status": "success",
	"data" : {
		"first_name": "Bob",
		"last_name": "Barker",
		"occupation": "Game show host"
	}
}
```

GET {resource}/get
------------------
Returns a collection of records from the resource. This can accept a number of query strings to filter/order/paginate, and include declared relations or not.

### Query Options:
Query options should be included as url parameters 

ex. 
```
/users/get?page=5&filters=[...]
```
#### filters
A json encoded array of arrays that declare conditional statements:
```
filters = [
	[column, value to match, 'and'/'or' (optional string, if blank, 'and' is assumed), 'like' (optional, if blank, '=' is assumed]
];
```
#### order
A json encoded array to set order
```
order = [column, order ('ASC' or 'DESC']
```
#### page
An integer to determine pagination page. If left blank, no pagination occurs.
#### include_relations
If set to true, the response will include all relations that are declared in the Resource's model, nested into properties.

POST {resource}/save
--------------------
Inserts a new record, or updates existing record if 'id' is passed as a field. Send fields as form-data. Returns the id of the created/updated record.

POST {resource}/save-batch
--------------------------
Inserts or updates an array of records. Pass array of records as JSON under "record" form-data field.

POST {resource}/delete
----------------------
Deletes a record. Pass an id in as a form-data field.

Controller Configuration
========================
Defining the Model
------------------
In the controller, hook up the controllers model by setting a public property $model with a string of the model name.
```
<?php
<?php
class User extends Base_Controller {
	public $model = "UsersModel";
}

```
Excluding Fields
----------------
You can easily exclude database fields from being sent to the front end (like password hashes, ids, etc) by adding the field names to a public property called $api_excluded_fields
```
<?php
class User extends Base_Controller {
	public $api_excluded_fields = ["password","id"];
}
```
Hiding Certain Auto-Exposed Endpoints
-----------------------------------
You can prevent some of the pre-build endpoints from being exposed to the front end by adding them to the $hidden_methods property array. For example if you did not want to allow a delete endpoint on the User resource, you would do the following:
```
<?php
class User extends Base_Controller {
	public $hidden_methods = ["delete"];
}
```
Creating Your Own Additional Endpoints
--------------------------------------
Of course you can create additional endpoints easily by just adding your own methods to the Resource Controller, just like you would normally do in CodeIgniter.

Authorization & Permissions
===========================
TO-DO: Documentation

Models & ORM
===========

Every resource controller requires a Model class, extended from BaseModel. Extending your models from BaseModel also gives you access to a basic ORM. Database tables belonging to a model should typically include a column called update_at, in DateTime format.

Models MUST have a $table public property declared.

Example Model:
```
<?php
class ClientModel extends BaseModel {

	public $table = "clients";

	public $soft_delete = true;

	public $relations = [
		['table' => 'locations', 'key' => 'client_id', 'hasMany'=>true]
	];
}
```
Soft Deletes
------------
You can enable soft deletes by simple including a Boolean "deleted" column on your table, and setting the $soft_delete property to true in the model.

Validation Rules
----------------
To set CodeIgniter supported validation rules on the model, set the $validation_rules property to an array using the CodeIgniter Form Validation conventions.

Declaring Database Relations
----------------------------
The $relations public property allows us to define other tables related to a model. It accepts an array of arrays like so:
```
public $relations = [
	[
		'table' => {table},
		'key' => {table_key ex user_id},
		'joins' => [
			['table' => {table}, 'key' => {key}].
		'hasMany' => true (optional, if not set, single child record assumed)
	]
]
```
For example:
```
<?php
class Authors extends BaseModel {
	public $table = "authors";
	
	public $relations = [
		'table' => 'books',
		'key' => 'author_id',
		'hasMany' => true
	];
}
```
Would cause a get() on the Author model to return child records from table "books" into the "books" property like this:
```
{
	"id": 1,
	"name": "C.S. Lewis",
	"year_born": 1898,
	"year_died": 1963,
	"books": [
		{
			"id": 3,
			"title": "Prince Caspian"
		},
		{
			"id": 45,
			"title": "Voyage of the Dawn Treader"
		}
	]
}
```
Omitting the 'hasMany' property in the relation declaration would return only the first record into the "books" property as an object, rather than an array:

```
{
	"id": 1,
	"name": "C.S. Lewis",
	"year_born": 1898,
	"year_died": 1963,
	"books": {
		"id": 3,
		"title": "Prince Caspian"
	},
}
```
You can also join tables inside child records as well:
```
public $relations = [
	'table' => 'books',
	'key' => 'author_id',
	'hasMany' => true,
	'joins' = [
		['table' => 'editors','key' => 'editor_id']
	]
];
```
This would perform a left join onto the books table with the addresses table on editor_id, producing:
```
SELECT * FROM books 
LEFT JOIN editors ON books.editor_id=editors.id
```
This would of course be then nested into the "books" property of the authors record.

Available Model Methods
-----------------------
Models contain methods get(), save(), saveBatch(), delete(), and find(), which is what powers the Resource Controller methods of the same names.

### get(array $options)
Takes an $options parameter:
```
$options = [
	'include_deleted' => false (default),
	'filters' => [
		[field, matched value, operator (optional), like (optional)]
	],
	'order' => [column, ASC/DESC],
	'page' => integer (optional),
	'include_relations' => true
];
```

### save(array $data) 
Takes $data parameter, container an array of fields to save. If validation rules are set on model, validation of $data is performed. Returns $id of record on success, or array of form_errors on validation failure.

### saveBatch(array $records)
Saves a set of records. If validation rules are set on model, validation is performed. Returns count of records saved.

### find(string $field,string/integer $value,boolean $include_children = true)
Returns single record.
Takes $field, $value, $include_children (optional, default true) parameters:
$field (string): Column to match
$value: Value to match with column.
$include_children (boolean, optional, default true): Whether or not to return any child relations as nested properties.

### delete(integer $id)
Deletes (or soft deletes if soft delete model is enabled on model) a record.



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


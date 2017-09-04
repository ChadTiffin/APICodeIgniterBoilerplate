###################
A CodeIgniter REST API Boilerplate
###################

Introduction
============

This provides a Base Controller with 5 universal endpoints, as well as a basic ORM for CodeIgniter Models. By extending the Base_Controller class into child controllers, you can quickly create a fully functional RESTful endpoint that supports CRUD operations. You can easily create your own additional endpoints by adding CodeIgniter controller methods to that controller.

Create a RESTful Resource like so:

```
class User extends Base_Controller {
	public $model = "UsersModel";
}
```

Prebuilt endpoints:
===================

GET {resource}/find/{value}/{field?}
GET {resource}/get
POST {resource}/save
POST {resource}/save-batch
POST {resource}/delete

GET {resource}/find/{value}/{field?}
------------------------

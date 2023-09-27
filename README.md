Licht - Laravel CRUD Generator

Licht - Laravel CRUD Generator
==============================

**Licht** is a Laravel package that simplifies the process of generating CRUD (Create, Read, Update, Delete) operations for your models. It provides a convenient Artisan command to quickly generate the necessary components for your models, such as models, requests, resources, controllers, and migrations.

Installation
------------

To get started with **Licht**, follow these steps:

1.  Install the package via Composer:

```bash
composer require hossamsoliuman/licht
```     

Usage
-----

To generate CRUD operations for a model, you can use the `licht` Artisan command. You can specify the model name as an argument or run the command without arguments to be prompted for the model name:

```bash
php artisan licht ModelName
```

Or:

```
php artisan licht
````
        

If you run the command without specifying the model name, you will be prompted to enter the model name interactively.

You will also be prompted to enter the field names and their types. Once you've provided the necessary information, Licht will generate the following components for you:

*   Model
*   Store Request
*   Update Request
*   Resource
*   Controller
*   Migration

These components will be placed in the appropriate directories within your Laravel project.
## Features

| Feature                                                   | Status         |
| --------------------------------------------------------- | ---------------|
| Support for various field types (string, integer, text, foreignId, image, file, date, datetime) | ✅ Done         |
| Automatic generation of validation rules based on field types                                   | ✅ Done         |
| Generate CRUD operations along with views                                                       | 🔜 Coming Soon  |
| Generate CRUD for existing model and database table created using Licht                         | 🔜 Coming Soon  |
| Customizable CRUD templates                                                                     | 🔜 Coming Soon  |
| Create CRUD for multiple models at once                                                          | 🔜 Coming Soon  |
| Extensive documentation and examples                                                             | 🔜 Coming Soon  |
| Add json type field                                                           | 🔜 Coming Soon  |



License
-------

**Licht** is open-source software licensed under the MIT License.

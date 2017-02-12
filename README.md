# Health Measures Laravel Provider

[![towerhousestudio](http://towerhousestudio.com/wp-content/uploads/2016/04/nuevo-logo-towerhouse2-1s-300x296.png)](http://towerhousestudio.com)

- This is a laravel/lumen provider for the library https://github.com/towerhouse/Healthmeasures
- The provider is on HealthmeasuresServiceProvider.php
- It provides a healthmeasures-routes.php with the format of a REST/API
- You can consult what each function does on HealthmeasuresController.php
- Responses are formatted following the standard from http://jsonapi.org/

### Tech

Health Measures only uses one library to work properly:

* [towerhouse/healthmeasures] >= 1.3.*

### Installation

- composer require towerhouse/healthmeasures-laravel
- Laravel: on the file config/app.php include
- Lumen:  on the file bootstrap/app.php include

```php
$app->register(Healthmeasures\Laravel\HealthmeasuresServiceProvider::class);
```

- The library generates pictures, create a folder healthmeasures under the public folder with write permissions,
currently you're responsible for cleaning the folder. There isn´t other storage to use by the moment.


### Example of use

Call a url as declared on healthmeasures-routes.php for instance, if (your vhost is called mbhealth-api.local)
http://mbhealth-api.local/healthmeasures/measure
and it will render all the measures from the database

### Tests

There are unit tests for each action except for the upload files actions. I have not found a way to do them yet.

### ToDos

Limit with query parameters the number of results (pagination)

### Html report generation

- Laravel: php artisan vendor:publish
- Lumen or Laravel: create the folder resources/views/healthmeasures/ and copy by hand report.blade.php inside there.
Then you are free to modify the view.

### Lumen provider

During the implementation of this package I found some difficulties that I documented here:
http://towerhousestudio.com/implement-a-lumen-provider

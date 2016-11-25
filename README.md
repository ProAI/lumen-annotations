# Lumen Annotations

[![Latest Stable Version](https://poser.pugx.org/proai/lumen-annotations/v/stable)](https://packagist.org/packages/proai/lumen-annotations) [![Total Downloads](https://poser.pugx.org/proai/lumen-annotations/downloads)](https://packagist.org/packages/proai/lumen-annotations) [![Latest Unstable Version](https://poser.pugx.org/proai/lumen-annotations/v/unstable)](https://packagist.org/packages/proai/lumen-annotations) [![License](https://poser.pugx.org/proai/lumen-annotations/license)](https://packagist.org/packages/proai/lumen-annotations)

This package enables annotations in Laravel Lumen to define routes and event bindings.

## Installation

Lumen Annotations is distributed as a composer package. So you first have to add the package to your `composer.json` file:

```
"proai/lumen-annotations": "~1.0"
```

Then you have to run `composer update` to install the package. Once this is completed, you have to add the service provider to the providers array in `config/app.php`:

```
'ProAI\Annotations\AnnotationsServiceProvider'
```

Copy `config/annotations.php` from this package to your configuration directory to use a custom configuration file.

##### Include generated routes

Once you have run `php artisan route:scan` (see below), you have to include the generated `routes.php` file in your `bootstrap/app.php` file:

```php
require __DIR__.'/../storage/framework/routes.php';
```

##### Include generated event bindings

After you have executed `php artisan event:scan` (see below), you have to add the service provider to the providers array in `config/app.php`:

```
'ProAI\Annotations\EventServiceProvider'
```

## Usage

By using annotations you can define your routes directly in your controller classes and your event bindings directly in your event handlers (see examples for usage of annotations).

##### Class Annotations

For routes:

Annotation | Description
--- | ---
`@Controller` | This annotation must be set to indicate that the class is a controller class. Optional parameters `prefix` and `middleware`.
`@Resource` | First parameter is resource name. Optional parameters `only` and `except`.
`@Middleware` | First parameter is middleware name.

For events:

Annotation | Description
--- | ---
`@Hears` | This annotation binds an event handler class to an event.

##### Method Annotations

For routes:

Annotation | Description
--- | ---
`@Get`,<br>`@Post`,<br>`@Options`,<br>`@Put`,<br>`@Patch`,<br>`@Delete`,<br>`@Any` | First parameter is route url. Optional parameters `as` and `middleware`.
`@Middleware` | First parameter is middleware name.

### Commands

After you have defined the routes and event bindings via annotations, you have to run the scan command:

* Use `php artisan route:scan` to register all routes.
* Use `php artisan route:clear` to clear the registered routes.
* Use `php artisan event:scan` to register all event bindings.
* Use `php artisan event:clear` to clear the registered events.

### Examples

##### Example #1

```php
<?php

namespace App\Http\Controllers;

use ProAI\Annotations\Annotations as Route;

/**
 * Class annotation for UserController (belongs to all class methods).
 *
 * @Route\Controller(prefix="admin")
 */
class UserController
{
    /**
     * Method annotations for showProfile() method.
     *
     * @Route\Get("/profiles/{id}", as="profiles.show")
     * @Route\Middleware("auth")
     */
    public function showProfile()
    {
      return view('profile');
    }

}
```

##### Example #2

```php
<?php

namespace App\Http\Controllers;

use ProAI\Annotations\Annotations as Route;

/**
 * Class annotations for resource controller CommentController (belongs to all class methods).
 *
 * @Route\Controller
 * @Route\Resource("comments", only={"create", "index", "show"})
 * @Route\Middleware("auth")
 */
class CommentController
{
    ...
}
```

##### Example #3

```php
<?php

namespace App\Handlers\Events;

use ProAI\Annotations\Annotations\Hears;

/**
 * Annotation for event binding.
 *
 * @Hears("UserWasRegistered")
 */
class SendWelcomeMail
{
    ...
}
```

## Support

Bugs and feature requests are tracked on [GitHub](https://github.com/proai/lumen-annotations/issues).

## License

This package is released under the [MIT License](LICENSE).

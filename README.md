# Lumen Route Annotations

[![Latest Stable Version](https://poser.pugx.org/proai/lumen-route-annotations/v/stable)](https://packagist.org/packages/proai/lumen-route-annotations) [![Total Downloads](https://poser.pugx.org/proai/lumen-route-annotations/downloads)](https://packagist.org/packages/proai/lumen-route-annotations) [![Latest Unstable Version](https://poser.pugx.org/proai/lumen-route-annotations/v/unstable)](https://packagist.org/packages/proai/lumen-route-annotations) [![License](https://poser.pugx.org/proai/lumen-route-annotations/license)](https://packagist.org/packages/proai/lumen-route-annotations)

This package enables annotations in Laravel Lumen to define routes.

## Installation

Lumen Route Annotationsis distributed as a composer package. So you first have to add the package to your `composer.json` file:

```
"proai/lumen-route-annotations": "~1.0@dev"
```

Then you have to run `composer update` to install the package. Once this is completed, you have to add the service provider to the providers array in `config/app.php`:

```
'ProAI\RouteAnnotations\RouteAnnotationsServiceProvider'
```

Run `php artisan vendor:publish` to publish this package configuration. Afterwards you can edit the file `config/route-annotations.php`.

## Usage

By using annotations you can define your routes directly in your controller classes (see examples for usage of annotations).

### Class Annotations

Annotation | Description
--- | ---
`@Controller` | Optional parameters `prefix` and `domain`.
`@Resource` | First parameter is resource name. Optional parameters `only` and `except`.
`@Middleware` | First parameter is middleware name.

### Method Annotations

Annotation | Description
--- | ---
`@Get`,<br>`@Post`,<br>`@Options`,<br>`@Put`,<br>`@Patch`,<br>`@Delete`,<br>`@Any` | First parameter is route url. Optional parameters `as`, `where`, `middleware`.
`@Middleware` | First parameter is middleware name.

### Commands

After you have defined the routes via annotations, you have to run `php artisan route:register`:

* Use `php artisan route:register` to register all routes.
* Use `php artisan route:clear` to clear the registered routes.

### Example #1

```php
<?php

namespace App\Http\Controllers;

use ProAI\RouteAnnotations\Annotations as Route;

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
     * @Route\Get("/profiles/{id}", as="profiles.show", where={"id": "[0-9]+"})
     * @Route\Middleware("auth")
     */
    public function showProfile()
    {
      return view('profile');
    }

}
```

### Example #2

```php
<?php

namespace App\Http\Controllers;

use ProAI\RouteAnnotations\Annotations as Route;

/**
 * Class annotations for resource controller CommentController (belongs to all class methods).
 *
 * @Route\Resource("comments", only={"create", "index", "show"})
 * @Route\Middleware("auth", except={"index", "show"})
 */
class CommentController
{
    ...
}
```

## Support

Bugs and feature requests are tracked on [GitHub](https://github.com/proai/lumen-route-annotations/issues).

## License

This package is released under the [MIT License](LICENSE).

# Slim modules

[![Build Status](https://travis-ci.org/ComPHPPuebla/slim-modules.svg?branch=master)](https://travis-ci.org/ComPHPPuebla/slim-modules)
[![Latest Stable Version](https://poser.pugx.org/comphppuebla/slim-modules/v/stable.svg)](https://packagist.org/packages/comphppuebla/slim-modules)
[![Latest Unstable Version](https://poser.pugx.org/comphppuebla/slim-modules/v/unstable.svg)](https://packagist.org/packages/comphppuebla/slim-modules)
[![License](https://poser.pugx.org/comphppuebla/slim-modules/license.svg)](https://packagist.org/packages/comphppuebla/slim-modules)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b86318b0-47ce-4d47-a0a4-db6e98dc8451/mini.png)](https://insight.sensiolabs.com/projects/b86318b0-47ce-4d47-a0a4-db6e98dc8451)

This package allow you to organize your Slim applications in a modular structure.
It provides the following features:

* Group your service definitions in service providers classes
* Group and configure your controllers in controller providers classes
* Group all your application controllers and services in single classes

## Installation

Using composer

```bash
$ composer require comphppuebla/slim-modules:~1.0@dev
```

## Usage

### Registering services

You can register the services of your module in a single class implementing
`ComPHPPuebla\Slim\ServiceProvider`.


For instance, suppose you have a product catalog module that is part of small
application.

```php
namespace Modules\ProductCatalog;

use ComPHPPuebla\Slim\ServiceProvider;
use Modules\ProductCatalog\Forms\ProductInformationForm;

class ProductCatalogServices implements ServiceProvider
{
    /**
     * Register all your module services as usual
     */
    public function configure(Slim $app)
    {
        $this->container->singleton(
            'catalog.product_repository',
            function() use ($app) {
                return new Catalog($app->container->get('dbal.connection'));
            }
        );
        $this->container->singleton(
            'catalog.product_controller',
            function() use ($app) {
                return new ProductController(
                    $app->container->get('twig.environment'),
                    new ProductInformationForm(),
                    $app->container->get('catalog.product_repository')
                );
            }
        );
    }
}
```

In order to use it in your application you would register this services in your
`public/index.php` file.

```php
$app = new Slim\Slim();

$services = new Modules\ProductCatalog\ProductCatalogServices();
$services->configure($app);


$app->run();
```

### Registering routes

Following the example, you would have routes to update the information of your products.
In order to group the routes of your module in a single class, you need to implement
`ComPHPPuebla\Slim\ControllerProvider`

```php
namespace Modules\ProductCatalog;

use ComPHPPuebla\Slim\ControllerProvider;
use ComPHPPuebla\Slim\ControllerResolver;
use Slim\Slim;

class ProductCatalogControllers implements ControllerProvider
{
    public function register(Slim $app, ControllerResolver $resolver)
    {
        $app->get('/catalog/product/edit/:id', $resolver->resolve(
            $app, 'catalog.product_controller:showProductForm'
        ));
        $app->post('/catalog/product/update', $resolver->resolve(
            $app, 'catalog.product_controller:updateProductInformation'
        ));
    }
}
```

The `ControllerResolver` class resolves the controller, method, and arguments to be
used when Slim matches the route being defined. It works the following way:

* It splits the string with the format `controller_key:method`, looking for the
controller in the application container using the `controller_key` part, and it will
use the `method` part to build a valid `callable`.
* The controller won't be instantiated unless the route is matched. The resolver
generates a function, instead of instantiating the object (giving the same effect as
if you were using `$app->container->protect`). This function will create the
controller, and it will pass the original route's arguments, the request
and the application itself as the 2 last arguments to your controller method.
* Once the arguments are resolved it will execute the method.

Let's suppose you have the following controller:

```php
namespace Modules\ProductCatalog\Controllers;

use Modules\ProductCatalog\Forms\ProductForm;
use ProductCatalog\Catalog;
use Twig_Environment as View

class ProductController
{
    protected $view;
    protected $catalog;
    protected $form;

    public function __construct(View $view, ProductForm $form, Catalog $catalog)
    {
        $this->view = $view;
        $this->form = $form;
        $this->catalog = $catalog;
    }


    public function showProductForm($productId, Request $request, Slim $app)
    {
        if (!$product = $this->catalog->productOf($productId)) {
            $app->notFound();
        }

        // Populate your form and pass it to the view
    }

    public function updateProductInformation(Request $request)
    {
        if ($this->form->isValid($request->params()) {
            $productInformation = $this->form->getValues();
            $product = $this->catalog->productOf($productInformation['id']);
            $product->update($productInformation);
            $this->catalog->update($product);
        }
        // Render the form with the errors
    }
}
```

In order to add your controllers to your application you would register them in the
`public/index.php` file.

```php
$app = new Slim\Slim();

/* Register your services first */

$services = new Modules\ProductCatalog\ProductCatalogControllers();
$services->register($app, new ComPHPPuebla\Slim\ControllerResolver());


$app->run();
```

One thing to note is that your controllers methods will always have the following
signature style by default:

```php
method(/* $route_param_1, $route_param_2, ... $route_param_n */ $request, $app)
```

The route params are optional, they depend on your route, but the request and the
application arguments will be passed by default.

### Arguments converters

You can pass an anonymous function to the `ControllerResolver` in order to modify the
arguments passed to your controller. Suppose for instance that your method
`Modules\ProductCatalog\Controllers\ProductController::showProductForm` does not need
the request argument. You could remove it by registering your route the following way:

```php
# Modules\ProductCatalog\ProductCatalogControllers

public function register(Slim $app, ControllerResolver $resolver)
{
    $app->get('/catalog/product/edit/:id', $resolver->resolve(
        $app,
        'catalog.product_controller:showProductForm',
        function (array $arguments) {
            // $arguments[0] is the product ID
            unset($arguments[1]); // Remove the request
            // $arguments[2] is our Slim application

            return $arguments;
        }
    ));

    /* ... */
}
```

Consequently, the signature for the method `showProductForm` would result in the
following `showProductForm($productId, Slim $app)`.

Any converter function must return an `array` with the arguments that will be passed to
your controller. In the example above we removed an unnecessary argument, but we can
replace the arguments completely.

Suppose you have a controller in your catalog to search products. This controller
uses the product category and a group of keywords separated by spaces to perform the
search. Also suppose that this search criteria is passed through the query string.
And that we are using the following criteria object:

```php
use Modules\ProductCatalog\Criteria;

class ProductSearchCriteria
{
    protected $category;
    protected $keywords;

    public function __construct($category = null, $keywords = null)
    {
        $this->category = $category;
        $this->keywords = $keywords;
    }

    public function hasCategory()
    {
        return !is_null($this->category);
    }

    public function category()
    {
        return $this->category;
    }

    public function hasKeywords()
    {
        return !is_null($this->keywords);
    }

    public function keywords()
    {
        return $this->keyword;
    }
}
```

Our controller should do something similar to the following:

```php
namespace Modules\ProductCatalog\Controllers;

/* .. */

class SearchController
{
    /* ... */

    public function searchProducts(Request $request)
    {
        $results = $this->catalog->productsMatching(new ProductSearchCriteria(
            $request->get('category'), $request->get('keywords')
        ));

        // Pass your results to the view
    }
}
```

We could use the criteria object directly using the following arguments converter:

```php
# Modules\ProductCatalog\ProductCatalogControllers

public function register(Slim $app, ControllerResolver $resolver)
{
    $app->get('/catalog/product/search', $resolver->resolve(
        $app,
        'catalog.product_controller:searchProducts',
        function (array $arguments) {
            // $arguments[0] is the request. Because our route does not have parameters

            return [new ProductSearchCriteria(
                $arguments[0]->get('category'), $arguments[0]->get('keywords')
            )];
        }
    ));

    /* ... */
}
```

Our controller can now receive the criteria directly:

```php
namespace Modules\ProductCatalog\Controllers;

/* .. */

class SearchController
{
    /* ... */

    public function searchProducts(ProductSearchCriteria $criteria)
    {
        $results = $this->catalog->productsMatching($criteria);

        // Pass your results to the view
    }
}
```

### Registering several modules

If you have more than one module you can register all your controllers and services in
a single place by using the classes `Controllers` and `Services`.

In order to group all your application services you can extend the class `ComPHPPuebla\Slim\Services`
and add all your modules service providers in the constructor by calling the method `add`

```php
namespace Application;

use ComPHPPuebla\Services;
use Modules\ProductCatalog\ProductCatalogServices;

class ApplicationServices extends Services
{
    /**
     * Add the providers for your modules here
     */
    public function __construct()
    {
        $this
            ->add(new ProductCatalogServices())
            /* ... */ //Register more modules here...
            ->add(new DoctrineDbalProvider()) // You could integrate libraries
            ->add(new TwigProvider()) // Using the same approach as with modules
        ;
    }
}
```

Similarly you can group all your controllers definitions using the class
`ComPHPPuebla\Slim\Controllers`. Instead of using the constructor, you need to
add your modules controllers in the `init` method (which is called automatically)

```php
namespace Application;

use ComPHPPuebla\Slim\Controllers;
use Modules\ProductCatalog\ProductCatalogControllers;

class ApplicationControllers extends Controllers
{
    protected function init()
    {
        $this
            ->add(new ProductCatalogControllers())
            /* ... */ //Register more controllers modules here...
        ;
    }
}
```

Then your `index.php` file would only need:

```php
$app = new Slim\Slim();

$services = new Application\ApplicationServices();
$services->configure($app);

$controllers = new Application\ApplicationControllers();
$controllers->register($app);

$app->run();
```

## Tests

Setup the test suite using Composer:

```bash
$ composer install --dev
```

Run it using PHPUnit:

```bash
$ php bin/phpunit --testdox
```

## License

This package is released under the MIT License.

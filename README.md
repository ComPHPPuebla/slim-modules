# Slim modules

This package allow you to organize your Slim applications in a modular structure.
It provides the following features:

* Ability to group your services in modules
* Ability to group and configure your controllers in modules
* Ability to group all your application controllers and services

# Installation

Using composer

```bash
$ composer require comphppuebla/slim-modules:~1.0@dev
```

# Usage

## Registering services

You can register the services of your module in a single class implementing
`ComPHPPuebla\Slim\ServiceProvider`.


For instance, suppose you have a product catalog module that is part of small
application.

```php
namespace Modules\ProductCatalog;

use ComPHPPuebla\Slim\ServiceProvider
use Modules\ProductCatalog\Forms\ProductInformationForm;

class ProductCatalogServices implements ServiceProvider
{
    /**
     * Register all your module services as usual
     */
    public function configure(Slim $app)
    {
        $this->container->singleton('catalog.product_repository', function() use ($app) {
            return new Catalog($app->container->get('dbal.connection'));
        });
        $this->container->singleton('catalog.product_controller', function() use ($app) {
            return new ProductController(
                $app->container->get('twig.environment'),
                new ProductInformationForm(),
                $app->container->get('catalog.product_repository')
            );
        });
    }
}
```

In order to use it in your application you would register this services in your
`public/index.php` file.

```php
require 'vendor/autoload.php';

$app = new \Slim\Slim();

$services = new Modules\ProductCatalog\ProductCatalogServices();
$services->configure($app);


$app->run();
```

# Registering routes

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
generates a function (giving the same effect as if you were using
`$app->container->protect`). This function will create the controller, and it will
pass the original arguments for the route, and the request and the application itself
as the 2 last arguments to your controller method.
* Once the arguments are resolved it will execute the method.

Let's suppose you have the following controller:

```
namespace Modules\ProductCatalog\Controllers;

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

# Controller providers

A controller provider is a special type of service provider. A controller provider binds a service
definition to a Slim route.

## Creating a controller provider

Following the catalog of products example, let's register some routes to update the information of
existing products. In order to group the routes of your module in a single class, you need to 
implement the interface `ComPHPPuebla\Slim\ControllerProvider`

```php
use Slim\Slim;

interface ControllerProvider
{
    public function register(Slim $app, Resolver $resolver);
}
```

Suppose you want to register two routes, one to show the form to edit a product (GET) and another
to update the product information in the database (POST).

```php
namespace Modules\ProductCatalog;

use ComPHPPuebla\Slim\ControllerProvider;
use ComPHPPuebla\Slim\Resolver;
use Slim\Slim;

/**
 * This provider registers to routes
 *
 * - `GET /catalog/product/edit/:id`
 * - `POST /catalog/product/update`
 */
class CatalogControllerProvider implements ControllerProvider
{
    public function register(Slim $app, Resolver $resolver)
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

The `Resolver` class resolves the controller, method, and arguments to be
used when Slim matches the route being defined. It works the following way:

1. It splits the string with the format `controller_key:method`
1. It looks for the controller in the application container using the `controller_key` part
1. It will use both the controller and the `method` to build a `callable` (`[$object, 'methodName']`).
1. This callable will be wrapped in an anonymous function, that will acts as a proxy to your 
   controller (similar to `$app->container->protect` which returns a function instead of the object).
   This "controller function" has the following characteristics:
    * It will create your controller once the route is matched 
    * It will pass the original route's arguments, as well as the request and the Slim application 
      to your controller method
    * It will execute the controller method

Let's suppose the controller you registered in your provider is as follows:

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

    /**
     * @param int $productId The `:id` coming from the path `/catalog/product/edit/:id`
     */
    public function showProductForm($productId, Request $request, Slim $app)
    {
        if (!$product = $this->catalog->productOf($productId)) {
            $app->notFound();
        }

        // Populate your form and pass it to the view
    }

    /**
     * When there are no parameters in the path, like in this case: `/catalog/product/update`.
     * The first argument will be the `Request` object, then the Slim application
     */ 
    public function updateProductInformation(Request $request)
    {
        if ($this->form->isValid($request->params()) {
            $product = $this->catalog->productWith($this->form->getProductId());
            $product->update($this->form->getValues());
            $this->catalog->update($product);
        }
        // Render the form with the errors
    }
}
```

In order to add your controllers to your application you would register the provider in your
`index` file.

```php
$app = new Slim\Slim();

/* Register your services first */

$controllers = new Modules\ProductCatalog\CatalogControllerProvider();
$controllers->register($app, new ComPHPPuebla\Slim\Resolver());

$app->run();
```

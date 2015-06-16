# Controller providers

Following the example, you would have routes to update the information of your products.
In order to group the routes of your module in a single class, you need to implement
`ComPHPPuebla\Slim\ControllerProvider`

```php
namespace Modules\ProductCatalog;

use ComPHPPuebla\Slim\ControllerProvider;
use ComPHPPuebla\Slim\Resolver;
use Slim\Slim;

class ProductCatalogControllers implements ControllerProvider
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

* It splits the string with the format `controller_key:method`, looking for the
controller in the application container using the `controller_key` part, and it will
use the `method` part to build a valid `callable`.
* The controller won't be instantiated unless the route is matched. The resolver
generates a function, instead of instantiating the object (giving the same effect as
if you were using `$app->container->protect`). This function will create the
controller, and it will pass the original route's arguments, the request
and the Slim application as arguments to your controller method.
* Once the arguments are resolved it will execute the method.

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
            $product = $this->catalog->productOf($this->form->getProductId());
            $product->update($this->form->getValues());
            $this->catalog->update($product);
        }
        // Render the form with the errors
    }
}
```

In order to add your controller to your application you would register it in your
`index` file.

```php
$app = new Slim\Slim();

/* Register your services first */

$controllers = new Modules\ProductCatalog\ProductCatalogControllers();
$controllers->register($app, new ComPHPPuebla\Slim\Resolver());

$app->run();
```

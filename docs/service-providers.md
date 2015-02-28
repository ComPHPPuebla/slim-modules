# Service providers

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

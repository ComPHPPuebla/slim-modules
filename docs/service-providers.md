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

## Integrating libraries

You can also use a service provider to integrate third party libraries like Twig.
As shown in the following example:

```php
use ComPHPPuebla\Slim\ServiceProvider;
use Slim\Slim;
use Twig_Loader_Filesystem as Loader;
use Twig_Environment as Environment;

class TwigServiceProvider implements ServiceProvider
{
    /**
     * @param Slim $app
     * @param array $parameters
     */
    public function configure(Slim $app, array $parameters = [])
    {
        $app->container->singleton('loader', function () {
            return new Loader($parameters['loader_paths']);
        });
        $app->container->singleton('twig', function () use ($app) {
            return new Environment($app->container->get('loader'), $parameters['options']);
        });
    }
}
```

Note that the interface allows you to pass parameters to your services, you can
pass values like paths, and other configuration settings that you don't want to
hard-code in your providers.

The following is an example of what kind of values you would pass to your provider.
They are still hard-coded to simplify the example, but this values should come from
a configuration file or from environment variables.

```php
$app = new Slim\Slim();

/* other providers.. */

$twig = new TwigServiceProvider();
$twig->configure($app, [
    'loader_paths' => 'application/templates',
    'options' => [
        'cache' => 'var/cache/twig',
        'debug' => true,
        'strict_variables' => true,
    ],
]);

/* your modules... */

$app->run();
```
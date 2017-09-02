# Service providers

A service is an object that does something as part of a larger system. Examples of services: a
database connection, a templating engine, an HTTP client, or a mailer. Almost any global object 
can be a service.

With a service provider you will be able to:

* Register services in the Slim application container
* Extend existing service definitions, which is a feature the original Slim service container lacks
* Pass parameters like: paths, passwords, API keys, and any configuration value that should not be
  hard-coded in the provider
  
## Contents 

* [Creating service providers](#creating-service-providers)
* [Integrating third-party libraries](#integrating-third-party-libraries)
* [Extending service definitions](#extending-service-definitions)

## Creating service providers

In order to package your services definitions in a class, it must implement the 
`ComPHPPuebla\Slim\ServiceProvider` interface.

```php
use Slim\Slim;

interface ServiceProvider
{
    public function configure(Slim $app, Resolver $resolver, array $options = []);
}
```

Suppose you have a product catalog module that is part of a Slim application. This module defines
a repository and a controller for products.

```php
namespace Modules\ProductCatalog;

use ComPHPPuebla\Slim\ServiceProvider;
use ComPHPPuebla\Slim\Resolver;

/**
 * This provider registers the following services
 *
 * - `catalog.product_repository`
 * - `catalog.product_controller`
 */
class ProductCatalogServices implements ServiceProvider
{
    public function configure(Slim $app, Resolver $resolver, array $options = [])
    {
        $app->container->singleton(
            'catalog.product_repository',
            function() use ($app) {
                return new Catalog($app->container->get('dbal.connection'));
            }
        );
        $app->container->singleton(
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

Once you have your `ServiceProvider` definition you can configure your Slim application
to use your module's services.

```php
$app = new Slim\Slim();

$services = new Modules\ProductCatalog\ProductCatalogServices();
$services->configure($app, new ComPHPPuebla\Slim\Resolver());

$app->run();
```

## Integrating third-party libraries

You can also use a service provider to integrate third-party libraries. The following is an example 
of a service provider for Twig:

```php
use ComPHPPuebla\Slim\ServiceProvider;
use ComPHPPuebla\Slim\Resolver;
use Slim\Slim;
use Twig_Loader_Filesystem as Loader;
use Twig_Environment as Environment;

/**
 * This provider registers the following services
 *
 * - `twig.loader`
 * - `twig.environment`
 */
class TwigServiceProvider implements ServiceProvider
{
    public function configure(Slim $app, Resolver $resolver, array $options = [])
    {
        $app->container->singleton('twig.loader', function () {
            return new Loader($options['loader_paths']);
        });
        $app->container->singleton('twig.environment', function () use ($app) {
            return new Environment($app->container->get('loader'), $options['options']);
        });
    }
}
```

Note that the interface allows you to pass parameters to your services. You can
pass values like filesystem paths, and other configuration settings that you
don't want to hard-code in your providers.

The following is an example of what kind of values you would pass to your provider.
They are still hard-coded to simplify the example, but these values should come from
a configuration file or from environment variables.

```php
$app = new Slim\Slim();

/* other providers.. */

$twig = new TwigServiceProvider();
$twig->configure($app, new Resolver(), [
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

## Extending service definitions

If you have used Twig, you will know it is a common use case to register extensions.
In this case you will need to extend a service definition, i. e. take the original
service and modify it. 

Suppose our module needs to register its own Twig extension. You would have to use the `Resolver` 
and call its `extend` method.

You will need to pass 3 arguments:
 
* Your Slim application
* The original service key and 
* A callable to modify the original service. The callable will receive the original service object
as argument.

```php
use ComPHPPuebla\Slim\ServiceProvider;
use ComPHPPuebla\Slim\Resolver;
use Slim\Slim;
use Twig_Environment as Environment;

/**
 * This provider extends the following service
 *
 * - `twig.environment`
 */
class ProductCatalogServices implements ServiceProvider
{
    public function configure(Slim $app, Resolver $resolver, array $options = [])
    {
        /* More service definitions... */
        $resolver->extend($app, 'twig.environment', function(Environment $twig) {
            $twig->addExtension(new MyTwigExtension());

            return $twig;
        });
    }
}
```

# Application middleware

Slim implements a version of the Rack protocol, therefore it can have middleware
that may inspect, or modify the application environment, request, and response
before and/or after the Slim application is invoked.

You can register all your application middleware in a single place. You only
need to extend the class `ComPHPPuebla\Slim\MiddlewareLayers` and add all your
middleware in the `init` method by calling the method `add`.

Suppose you have a middleware that logs the requests, matched routes and responses
of your application. You would have to register it the following way.

```php
namespace Application;

use Slim\Helper\Set;
use ComPHPPuebla\MiddlewareLayers;

class ApplicationMiddleware extends MiddlewareLayers
{
    /**
     * Add the middleware for your application here
     */
    protected function init(Set $container)
    {
        $this
            ->add(new RequestLoggingMiddleware())
        ;
    }
}
```

## Using the container

Suppose you want to use dependency injection for your middleware in order to
control what logger your application should use, instead of simply using Slim's
logger.

You could register a [Monolog][1] logger in a service provider.

```php
namespace Application;

use ComPHPPuebla\Slim\Resolver;
use ComPHPPuebla\Slim\ServiceProvider;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Slim\Slim;

class MonologServiceProvider implements ServiceProvider
{
    public function configure(Slim $app, Resolver $resolver, array $options = [])
    {
        $app->container->singleton(
            'logger',
            function () use ($app, $options) {
                $logger = new Logger($options['monolog']['channel']);
                $logger->pushHandler(new StreamHandler(
                    $options['monolog']['path'], Logger::DEBUG
                ));

                return $logger;
            }
        );
    }
}
```

And then retrieve the object inside the `init` method through the container.

```php
protected function init(Set $container)
{
    $this
        ->add(new RequestLoggingMiddleware($container->get('logger'))
    ;
}
```

Alternatively, you could register the middleware class inside a service provider
and simply retrieve it from the container.

```php
protected function init(Set $container)
{
    $this
        ->add($container->get('slim.middleware.request_logger')
    ;
}
```

Then your `index.php` file would only need:

```php
$app = new Slim\Slim();

$middleware = new Application\ApplicationMiddleware();
$middleware->configure($app);

$app->run();
```

[1]: https://github.com/Seldaek/monolog

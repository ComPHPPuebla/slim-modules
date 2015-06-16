# Grouping definitions

If you have more than one module you can register all your controllers and services in
a single place by using the classes `Controllers` and `Services`.

In order to group all your application services you can extend the class `ComPHPPuebla\Slim\Services`
and add all your modules service providers in the `init` method by calling the method `add`

```php
namespace Application;

use ComPHPPuebla\Services;

class ApplicationServices extends Services
{
    /**
     * Add the providers for your modules here
     */
    protected function init()
    {
        $this
            ->add(new ProductCatalogServices())
            //Register more modules here...
            ->add(new DoctrineDbalProvider()) // You could integrate libraries
            ->add(new TwigProvider()) // Using the same approach as with modules
        ;
    }
}
```

Similarly you can group all your controllers definitions using the class
`ComPHPPuebla\Slim\Controllers`. Similarly, you need to add your modules
controllers in the `init` method (which is called automatically).

```php
namespace Application;

use ComPHPPuebla\Slim\Controllers;

class ApplicationControllers extends Controllers
{
    protected function init()
    {
        $this
            ->add(new ProductCatalogControllers())
            //Register more controllers modules here...
        ;
    }
}
```

Then your `index.php` file would only need:

```php
$app = new Slim\Slim();
$resolver = new ComPHPPuebla\Slim\Resolver();

$services = new Application\ApplicationServices($resolver, $parameters);
$services->configure($app);

$controllers = new Application\ApplicationControllers($resolver);
$controllers->register($app);

$app->run();
```

Note that the `Services` class allows you to pass parameters to
your services through the constructor. These values are passed when
we call the `configure` method to each of the registered services.

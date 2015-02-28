# Grouping definitions

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

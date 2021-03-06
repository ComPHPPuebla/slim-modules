# Argument converters

One thing to note is that your controllers methods will always have the following
signature style by default:

```php
method(/* $route_param_1, $route_param_2, ... $route_param_n */ $request, $app)
```

The route parameters are optional, they depend on your route, but the request and the
application arguments will be passed by default.

You can modify this behaviour by passing an *arguments converter function* to the 
`Resolver::resolve` method.

## Contents

* [Modifying arguments](#modifying-arguments)
* [Custom arguments](#custom-arguments)

## Modifying arguments

Suppose for instance that your method
`Modules\ProductCatalog\Controllers\ProductController::showProductForm` does not need
the request argument. You could remove it by registering your route the following way:

```php
# Modules\ProductCatalog\CatalogControllerProvider

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
customize the arguments completely.

## Custom arguments

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

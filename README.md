# Slim modules

[![Build Status](https://travis-ci.org/ComPHPPuebla/slim-modules.svg?branch=master)](https://travis-ci.org/ComPHPPuebla/slim-modules)
[![Latest Stable Version](https://poser.pugx.org/comphppuebla/slim-modules/v/stable.svg)](https://packagist.org/packages/comphppuebla/slim-modules)
[![Latest Unstable Version](https://poser.pugx.org/comphppuebla/slim-modules/v/unstable.svg)](https://packagist.org/packages/comphppuebla/slim-modules)
[![License](https://poser.pugx.org/comphppuebla/slim-modules/license.svg)](https://packagist.org/packages/comphppuebla/slim-modules)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/b86318b0-47ce-4d47-a0a4-db6e98dc8451/mini.png)](https://insight.sensiolabs.com/projects/b86318b0-47ce-4d47-a0a4-db6e98dc8451)

This package allows you to organize your Slim applications in a modular structure. By introducing
the following components
 
* **Service providers**. For third-party libraries, services, controllers and Slim middleware 
  classes. It adds the capability of **extending existing services**.
* **Route providers**. To match paths with controllers registered as services
* **Argument converters**. To customize the method signatures of your controllers
* **Definition groups**. For services, middleware and route providers

## Tests

Setup the test suite using Composer:

```bash
$ composer install --dev
```

Run it using PHPUnit:

```bash
$ php bin/phpunit --testdox
```

## License

This package is released under the MIT License.

## Documentation

The documentations is available at [https://comphppuebla.github.io/slim-modules/][1]

[1]: https://comphppuebla.github.io/slim-modules/

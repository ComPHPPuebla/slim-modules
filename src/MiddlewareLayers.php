<?php
/**
 * PHP version 5.5
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 *
 * @copyright Comunidad PHP Puebla 2015 (http://www.comunidadphppuebla.com)
 */
namespace ComPHPPuebla\Slim;

use Slim\Helper\Set;
use Slim\Middleware;
use Slim\Slim;

class MiddlewareLayers
{
    /** @var array */
    private $middleware = [];

    /**
     * Override this in subclasses to add your Slim middleware classes
     *
     * This method will be called at the beginning of ComPHPPuebla\Slim\MiddlewareLayers::configure
     *
     * @param Set $container
     */
    protected function init(Set $container)
    {
    }

    /**
     * @param Middleware $middleware
     * @return MiddlewareLayers
     */
    public function add(Middleware $middleware)
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Configure the middleware layers for your application
     *
     * @param Slim $app
     */
    public function configure(Slim $app)
    {
        $this->init($app->container);

        /** @var MiddlewareProvider $middleware */
        foreach ($this->middleware as $middleware) {
            $app->add($middleware);
        }
    }
}

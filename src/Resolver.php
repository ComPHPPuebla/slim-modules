<?php
/**
 * PHP version 5.5
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 *
 * @copyright Comunidad PHP Puebla 2015 (http://www.comunidadphppuebla.com)
 */
namespace ComPHPPuebla\Slim;

use Slim\Slim;

class Resolver
{
    /**
     * Resolve a controllers definition
     *
     * It takes a string with the form 'controller:action', this method will split the string in
     * 2 parts. It will look for the controller definition in the application container using the
     * first part of the string, it will use the second part of the string as the controller's
     * method. Those values will be used as a callable ([$controller, 'action']).
     * It will pass the route arguments, if any, followed by the Slim application and Slim request,
     * to the callable.
     * If a parameter converter is passed it will replace the default arguments with the values
     * returned by the converter.
     *
     * @param Slim $app
     * @param string $key
     * @param callable $converter Optional parameter converter
     * @return callable
     */
    public function resolve(Slim $app, $key, callable $converter = null)
    {
        list($controller, $action) = explode(':', $key);

        return function () use ($app, $controller, $action, $converter) {

            $arguments = array_merge(func_get_args(), [$app->request, $app]);
            if ($converter) {
                $arguments = call_user_func($converter, $arguments);
            }

            call_user_func_array([$app->container->get($controller), $action], $arguments);
        };
    }

    /**
     * Extend an existing service definition
     *
     * The $extension callable will receive the original service as its first argument and the
     * Slim application as its second argument.
     *
     * @param Slim $app
     * @param string $key
     * @param callable $extension
     */
    public function extend(Slim $app, $key, callable $extension)
    {
        // Unset the original factory defined by $key in order to avoid a recursion loop
        $services = $app->container->all();
        $original = $services[$key];
        unset($services[$key]);
        $app->container->replace($services);

        $app->container->singleton($key, function() use ($original, $app, $extension) {

            // Execute the original factory before calling, its extension
            return call_user_func_array($extension, [$original($app)]);
        });
    }
}

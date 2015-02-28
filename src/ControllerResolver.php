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

class ControllerResolver
{
    /**
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
}

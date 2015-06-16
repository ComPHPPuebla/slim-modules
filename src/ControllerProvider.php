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

interface ControllerProvider
{
    /**
     * Register your controllers here
     *
     * @param Slim $app
     * @param Resolver $resolver
     */
    public function register(Slim $app, Resolver $resolver);
}

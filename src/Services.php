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

class Services implements ServiceProvider
{
    /** @var ServiceProvider[] */
    protected $providers = [];

    /**
     * @param ServiceProvider $provider
     */
    public function add(ServiceProvider $provider)
    {
        $this->providers[] = $provider;
    }

    /**
     * Configure the services for all your modules
     *
     * @param Slim $app
     */
    public function configure(Slim $app)
    {
        /** @var ServiceProvider $provider */
        foreach ($this->providers as $provider) {
            $provider->configure($app);
        }
    }
}

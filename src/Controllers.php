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

class Controllers
{
    /** @var RouteProvider[] */
    private $providers;

    /** @var ControllerResolver */
    protected $resolver;

    /**
     * @param ControllerResolver $resolver
     */
    public function __construct(ControllerResolver $resolver = null)
    {
        $this->providers = [];
        $this->resolver = $resolver ?: new ControllerResolver();
    }

    /**
     * Override this in subclasses to add your module's controllers
     *
     * This method will be called at the beginning of ComPHPPuebla\Slim\Controllers::register
     */
    protected function init()
    {
    }

    /**
     * @param ControllerProvider $provider
     * @return Controllers
     */
    public function add(ControllerProvider $provider)
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @param Slim $app
     */
    public function register(Slim $app)
    {
        $this->init();

        /** @var RouteProvider $provider */
        foreach ($this->providers as $provider) {
            $provider->register($app, $this->resolver);
        }
    }
}

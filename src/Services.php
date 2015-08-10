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

class Services
{
    /** @var Resolver */
    protected $resolver;

    /** @var ServiceProvider[] */
    private $providers = [];

    /** @var array */
    protected $options;

    /**
     * @param Resolver
     * @param array $options
     */
    public function __construct(Resolver $resolver, array $options = [])
    {
        $this->resolver = $resolver;
        $this->options = $options;
    }

    /**
     * Override this in subclasses to add your module's services
     *
     * This method will be called at the beginning of ComPHPPuebla\Slim\Services::configure
     */
    protected function init()
    {
    }

    /**
     * @param ServiceProvider $provider
     * @return Services
     */
    public function add(ServiceProvider $provider)
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * Configure the services for all your modules
     *
     * @param Slim $app
     */
    public function configure(Slim $app)
    {
        $this->init();

        /** @var ServiceProvider $provider */
        foreach ($this->providers as $provider) {
            $provider->configure($app, $this->resolver, $this->options);
        }
    }
}

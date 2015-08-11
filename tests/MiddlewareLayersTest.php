<?php
/**
 * PHP version 5.5
 *
 * This source file is subject to the license that is bundled with this package in the file LICENSE.
 *
 * @copyright Comunidad PHP Puebla 2015 (http://www.comunidadphppuebla.com)
 */
namespace ComPHPPuebla\Slim;

use PHPUnit_Framework_TestCase as TestCase;
use Slim\Helper\Set;
use Slim\Middleware;
use Slim\Slim;
use stdClass;

class FakeMiddleware extends Middleware
{
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function call()
    {
    }
}

class FakeMiddlewareLayers extends MiddlewareLayers
{
    public function init(Set $container)
    {
        $this->add(new FakeMiddleware($container->get('logger')));
    }
}

class MiddlewareLayersTest extends TestCase
{
    /** @test */
    function it_should_register_middleware_to_the_given_slim_application()
    {
        $middleware1 = $this->getMock(Middleware::class);
        $middleware2 =  $this->getMock(Middleware::class);

        $builder = $this->getMockBuilder(Slim::class);
        $app = $builder->setMethods(['add'])->getMock();

        $app
            ->expects($spy = $this->exactly(2))
            ->method('add')
            ->with($this->isInstanceOf(Middleware::class))
        ;

        $middleware = new MiddlewareLayers();
        $middleware
            ->add($middleware1)
            ->add($middleware2)
        ;

        $middleware->configure($app);

        $this->assertEquals(
            2, $spy->getInvocationCount(), 'It should have registered 2 middleware layers'
        );
    }

    /** @test */
    function it_should_be_able_to_use_slim_container_to_build_middleware()
    {
        $app = new Slim();
        $container = $this->getMock(Set::class);
        $container
            ->expects($spy = $this->once())
            ->method('get')
            ->with('logger')
            ->willReturn($this->returnValue(new stdClass()))
        ;

        $app->container = $container;

        $middleware = new FakeMiddlewareLayers();

        $middleware->configure($app);

        $this->assertEquals(
            1, $spy->getInvocationCount(), 'It should have retrieved the logger from the container'
        );
    }
}

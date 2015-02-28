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
use Slim\Environment;
use Slim\Http\Request;
use Slim\Slim;

class TestController
{
    public function test() {}

    public function testWithArgument() {}
}

class ControllerResolverTest extends TestCase
{
    /** @test */
    function it_should_call_a_controller_with_the_default_arguments()
    {
        Environment::mock([
            'PATH_INFO' => '/test',
        ]);

        $controller = $this->getMock(TestController::class);

        $controller
            ->expects($this->once())
            ->method('test')
            ->with($this->isInstanceOf(Request::class), $this->isInstanceOf(Slim::class))
        ;

        $resolver = new ControllerResolver();

        $app = new Slim();
        $app->container->singleton('controller', function () use ($controller) {
            return $controller;
        });

        $app->get('/test', $resolver->resolve($app, 'controller:test'));

        $app->run();
    }

    /** @test */
    function it_should_call_a_controller_with_the_default_and_route_arguments()
    {
        $expectedArgument = 'leonard_nimoy';
        Environment::mock([
            'PATH_INFO' => "/test/$expectedArgument",
        ]);

        $controller = $this->getMock(TestController::class);

        $controller
            ->expects($this->once())
            ->method('testWithArgument')
            ->with($expectedArgument, $this->isInstanceOf(Request::class), $this->isInstanceOf(Slim::class))
        ;

        $resolver = new ControllerResolver();

        $app = new Slim();
        $app->container->singleton('controller', function () use ($controller) {
            return $controller;
        });

        $app->get('/test/:name', $resolver->resolve($app, 'controller:testWithArgument'));

        $app->run();
    }

    /** @test */
    function it_should_call_a_controller_with_arguments_modified_by_a_converter()
    {
        $expectedArgument = 'leonard_nimoy';
        Environment::mock([
            'PATH_INFO' => "/test/$expectedArgument",
        ]);

        $controller = $this->getMock(TestController::class);

        $controller
            ->expects($this->once())
            ->method('testWithArgument')
            ->with($expectedArgument) // It removes the request and application arguments
        ;

        $resolver = new ControllerResolver();

        $app = new Slim();
        $app->container->singleton('controller', function () use ($controller) {
            return $controller;
        });

        $app->get('/test/:name', $resolver->resolve($app, 'controller:testWithArgument', function($arguments) {
            return [$arguments[0]];
        }));

        $app->run();
    }
}

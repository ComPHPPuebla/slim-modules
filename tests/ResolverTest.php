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
use stdClass;
use Slim\Environment;
use Slim\Http\Request;
use Slim\Slim;

class TestController
{
    public function test() {}

    public function testWithArgument() {}
}

class ResolverTest extends TestCase
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

        $resolver = new Resolver();
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
            ->with(
                $expectedArgument,
                $this->isInstanceOf(Request::class),
                $this->isInstanceOf(Slim::class)
            )
        ;

        $resolver = new Resolver();
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
            ->with($expectedArgument) // Converter will remove the request and application arguments
        ;

        $resolver = new Resolver();
        $app = new Slim();
        $app->container->singleton('controller', function () use ($controller) {
            return $controller;
        });

        $app->get(
            '/test/:name',
            $resolver->resolve(
                $app,
                'controller:testWithArgument',
                function(array $arguments) {
                    return [$arguments[0]]; // It removes the request and application arguments
                }
            )
        );

        $app->run();
    }

    /** @test */
    function it_should_call_a_controller_with_arguments_defined_by_a_converter()
    {
        $expectedArgument = 'leonard_nimoy';
        Environment::mock([
            'PATH_INFO' => "/test/$expectedArgument",
        ]);

        $controller = $this->getMock(TestController::class);
        $controller
            ->expects($this->once())
            ->method('testWithArgument')
            ->with($this->isInstanceOf(stdClass::class)) // Converter will replace default arguments
        ;

        $resolver = new Resolver();
        $app = new Slim();
        $app->container->singleton('controller', function () use ($controller) {
            return $controller;
        });

        $app->get(
            '/test/:name',
            $resolver->resolve(
                $app,
                'controller:testWithArgument',
                function(array $arguments) {
                    return [new stdClass()]; // It replaces default arguments
                }
            )
        );

        $app->run();
    }

    /** @test */
    function it_should_extend_a_service_definition()
    {
        $resolver = new Resolver();
        $app = new Slim();

        $app->container->singleton('service', function() {
            return new stdClass();
        });

        $resolver->extend($app, 'service', function(stdClass $service) {
            $service->modified = true;

            return $service;
        });

        $service = $app->container->get('service');

        $this->assertObjectHasAttribute('modified', $service);
        $this->assertTrue($service->modified);
    }

    /** @test */
    function it_should_allow_extending_a_service_definition_more_than_once()
    {
        $resolver = new Resolver();
        $app = new Slim();

        $app->container->singleton('service', function() {
            return new stdClass();
        });

        $resolver->extend($app, 'service', function(stdClass $service) {
            $service->first = true;

            return $service;
        });
        $resolver->extend($app, 'service', function(stdClass $service) {
            $service->second = true;

            return $service;
        });

        $service = $app->container->get('service');

        $this->assertObjectHasAttribute('first', $service);
        $this->assertTrue($service->first);
        $this->assertObjectHasAttribute('second', $service);
        $this->assertTrue($service->second);
    }
}

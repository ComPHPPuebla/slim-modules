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
use Slim\Slim;

class ControllersTest extends TestCase
{
    /** @test */
    function it_should_call_its_registered_controller_providers()
    {
        /** @var Resolver $resolver */
        $resolver = $this->getMock(Resolver::class);

        $app = new Slim();

        /** @var ControllerProvider $provider1 */
        $provider1 = $this->getMock(ControllerProvider::class);
        $provider1
            ->expects($this->once())
            ->method('register')
            ->with($app, $resolver)
        ;

        /** @var ControllerProvider $provider2 */
        $provider2 = $this->getMock(ControllerProvider::class);
        $provider2
            ->expects($this->once())
            ->method('register')
            ->with($app, $resolver)
        ;

        $controllers = new Controllers($resolver);
        $controllers->add($provider1);
        $controllers->add($provider2);

        $controllers->register($app);
    }
}

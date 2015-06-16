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

class ServicesTest extends TestCase
{
    /** @test */
    function it_should_call_its_registered_service_providers()
    {
        $app = new Slim();

        /** @var ServiceProvider $provider1 */
        $provider1 = $this->getMock(ServiceProvider::class);
        $provider1
            ->expects($this->once())
            ->method('configure')
            ->with($app)
        ;

        /** @var ServiceProvider $provider2 */
        $provider2 = $this->getMock(ServiceProvider::class);
        $provider2
            ->expects($this->once())
            ->method('configure')
            ->with($app)
        ;

        $services = new Services(new Resolver());
        $services->add($provider1);
        $services->add($provider2);

        $services->configure($app);
    }
}

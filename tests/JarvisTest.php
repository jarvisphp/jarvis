<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\DelegateInterface;
use JarvisPHP\Jarvis;
use JarvisPHP\Middleware\RouteMiddleware;
use PHPUnit\Framework\TestCase;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class JarvisTest extends TestCase
{
    public function testRun()
    {
        $app = new Jarvis();

        $app->getRouter()
            ->beginRoute('test')
                ->setHandler(function () {
                    dump('hello, world! This is a test');
                })
            ->end()
        ;

        $app->run(new ServerRequest('get', '/'));
    }
}

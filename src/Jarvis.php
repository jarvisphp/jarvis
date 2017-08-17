<?php

declare(strict_types=1);

namespace JarvisPHP;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Interop\Http\ServerMiddleware\DelegateInterface;
use JarvisPHP\Middleware\RouteMiddleware;
use JarvisPHP\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class Jarvis
{
    /**
     * @var Router
     */
    protected $router;

    protected $defaultDelegate;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * Runs the application to produce and return a response according to provided
     * request.
     *
     * If no request and/or response are provided, this method will create a request
     * with ServerRequest::fromGlobals() and instanciate a default response.
     *
     * @param  null|ServerRequestInterface $request  Request to use or null
     * @param  null|ResponseInterface      $response Response to use or null
     *
     * @return ResponseInterface
     */
    public function run(ServerRequestInterface $request = null, ResponseInterface $response = null): ResponseInterface
    {
        $request = $request ?? ServerRequest::fromGlobals();
        $response = $response ?? new Response();

        $pipeline = new class() extends \SplQueue implements DelegateInterface {
            /**
             * Dispatch the next available middleware and return the response.
             *
             * @param ServerRequestInterface $request
             *
             * @return ResponseInterface
             */
            public function process(ServerRequestInterface $request)
            {
                dump($request);
                die;
                $this->next();
                $middleware = $this->current();

                return $middleware->process($request, $this);
            }
        };

        $pipeline->enqueue(new RouteMiddleware($this->router));

        $pipeline->rewind();
        $middleware = $pipeline->dequeue();
        $response = $middleware->process($request, $pipeline);

        return $response;
    }
}

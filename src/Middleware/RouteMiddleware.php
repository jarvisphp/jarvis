<?php

declare(strict_types=1);

namespace JarvisPHP\Middleware;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use JarvisPHP\Routing\Router;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class RouteMiddleware implements MiddlewareInterface
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $result = $this->router->match($request->getMethod(), $request->getUri()->getPath());

        return $delegate->process($request->withAttribute(self::class, [
            'result' => $result,
        ]));
    }
}

<?php

declare(strict_types=1);

namespace JarvisPHP\Routing;

use FastRoute\DataGenerator\GroupCountBased as DataGenerator;
use FastRoute\Dispatcher\GroupCountBased as Dispatcher;
use FastRoute\RouteCollector;
use FastRoute\RouteParser\Std as Parser;
use GuzzleHttp\Psr7\Response;
use JarvisPHP\HttpCode;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class Router extends Dispatcher
{
    /**
     * @var bool
     */
    private $isFresh = true;

    /**
     * @var array
     */
    private $rawRoutes = [];

    /**
     * @var array
     */
    private $routesNames = [];

    /**
     * @var RouteCollector
     */
    private $routeCollector;

    /**
     * Creates an instance of Router.
     *
     * Required to disable DataGenerator constructor.
     */
    public function __construct()
    {
        $this->routeCollector = new RouteCollector(new Parser(), new DataGenerator());
    }

    /**
     * Adds provided route.
     *
     * @param  Route $route Route to add
     */
    public function addRoute(Route $route): void
    {
        $this->rawRoutes[] = [$route->getMethod(), $route->getPattern(), $route->getHandler()];
        $this->isFresh = false;

        if (false != $name = $route->getName()) {
            $this->routesNames[$name] = $route->getPattern();
        }
    }

    /**
     * This method brings to you a smooth syntax to declare new route. Example:
     *
     * $router
     *     ->beginRoute('hello_world')
     *         ->setPattern('/hello/world')
     *         ->setHandler(function () {
     *             return 'Hello, world!';
     *         })
     *     ->end()
     * ;
     *
     * @param  string|null $name Route name or null
     * @return Route
     */
    public function beginRoute(string $name = null): Route
    {
        return new Route($this, $name);
    }

    /**
     * Builds URI associated to provided route name.
     *
     * @param  string $name   The URI route name to generate
     * @param  array  $params Parameters to replace in pattern
     *
     * @throws \InvalidArgumentException if provided route name is unknown
     *
     * @return string
     */
    public function uriOf(string $name, array $params = []): string
    {
        if (!isset($this->routesNames[$name])) {
            throw new \InvalidArgumentException(sprintf(
                '[%s] Route with name "%s" does not exist.',
                __METHOD__,
                $name
            ));
        }

        $uri = $this->routesNames[$name];
        foreach ($params as $key => $value) {
            $regex = sprintf('~\{(%s:?[^}]*)\}~', $key);
            if (1 !== preg_match($regex, $uri, $matches)) {
                continue;
            }

            $value = (string) $value;
            $pieces = explode(':', $matches[1]);
            if (1 < count($pieces) && 1 !== preg_match('~' . $pieces[1] . '~', $value)) {
                throw new \InvalidArgumentException(sprintf(
                    'Parameter "%s" must match regex "%s" for route "%s".',
                    $key,
                    $pieces[1],
                    $name
                ));
            }

            $uri = str_replace($matches[0], $value, $uri);
        }

        return $uri;
    }

    /**
     * Matches the given HTTP method and URI to the route collection and returns
     * an array that containts callback and arguments.
     *
     * @param  string $method
     * @param  string $uri
     *
     * @return array
     */
    public function match(string $method, string $uri): array
    {
        $arguments = [];
        $callback = null;

        $this->refreshRouteCollector();
        [$this->staticRouteMap, $this->variableRouteData] = $this->routeCollector->getData();

        $result = parent::dispatch(strtolower($method), $uri);
        if (Dispatcher::FOUND === $result[0]) {
            [1 => $callback, 2 => $arguments] = $result;
        } else {
            $callback = function () use ($result): Response {
                return new Response(Dispatcher::NOT_FOUND === $result[0]
                    ? HttpCode::NOT_FOUND
                    : HttpCode::METHOD_NOT_ALLOWED
                );
            };
        }

        return [$callback, $arguments];
    }

    /**
     * {@inheritdoc}
     *
     * Overrides DataGenerator::dispatch() to ensure that dispatcher always deals
     * with up-to-date route collection.
     */
    public function dispatch($method, $uri)
    {
        return $this->match($method, $uri);
    }

    /**
     * Refreshes route collector if needed.
     */
    protected function refreshRouteCollector(): void
    {
        if (!$this->isFresh) {
            $this->routeCollector = new RouteCollector(new Parser(), new DataGenerator());
            foreach ($this->rawRoutes as $rawRoute) {
                $this->routeCollector->addRoute(...$rawRoute);
            }

            $this->isFresh = true;
        }
    }
}

<?php

declare(strict_types=1);

namespace JarvisPHP\Routing;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class Route
{
    /**
     * @var null|string
     */
    private $name;

    /**
     * @var array
     */
    private $method = ['get'];

    /**
     * @var string
     */
    private $pattern = '/';

    /**
     * @var mixed
     */
    private $handler;

    /**
     * @var Router
     */
    private $router;

    public function __construct(Router $router, string $name = null)
    {
        $this->name = $name;
        $this->router = $router;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getMethod(): array
    {
        return $this->method;
    }

    public function setMethod($method): Route
    {
        $this->method = array_map('strtolower', (array) $method);

        return $this;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function setPattern(string $pattern): Route
    {
        $this->pattern = $pattern;

        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function setHandler($handler): Route
    {
        $this->handler = $handler;
        return $this;
    }

    public function end(): Router
    {
        $this->router->addRoute($this);

        return $this->router;
    }
}

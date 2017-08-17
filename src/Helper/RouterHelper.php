<?php

declare(strict_types=1);

namespace ;

/**
 * @author Eric Chau <eriic.chau@gmail.com>
 */
class RouterHelper
{
    const DEFAULT_SCHEME = 'http';

    private $host = '';
    private $scheme = self::DEFAULT_SCHEME;

    /**
     * Returns current scheme.
     *
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * Sets new scheme.
     *
     * Calling this method without parameter will reset it to the default scheme (= http).
     *
     * @param string|null $scheme
     */
    public function setScheme(string $scheme = null): void
    {
        $this->scheme = $scheme ?? self::DEFAULT_SCHEME;
    }

    /**
     * Returns current host.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * Sets new host.
     *
     * Calling this method without parameter will reset the host to an empty string.
     *
     * @param  string|null $host
     */
    public function setHost(string $host = null): void
    {
        $this->host = (string) $host;
    }

    /**
     * Uses the provided request to guess the host. This method also set the
     *
     * @param  Request $request
     * @return self
     */
    public function guessHost(RequestInterface $request): void
    {
        $uri = $request->getUri();
        $this->setScheme($uri->getScheme());
        $this->setHost($uri->getHost());

        if (null !== $uri->getPort()) {
            $this->setHost($this->getHost() . ':' . $uri->getPort());
        }
    }

    /**
     * Builds and returns complete URL (with scheme and host) for provided URI.
     *
     * Note that this method works properly if only at least the host is setted.
     *
     * @param  string $uri
     * @return string
     */
    public function buildUrl(string $uri): string
    {
        $scheme = '';
        if ($this->host) {
            $uri = preg_replace('~/+~', '/', $this->host . '/' . $uri);
            $scheme = $this->scheme . '://';
        }

        return $scheme . $uri;
    }
}

<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Matcher;

use Neu\Component\Http\Exception\MethodNotAllowedHttpException;
use Neu\Component\Http\Exception\NotFoundHttpException;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\RequestInterface;

/**
 * Defines the contract for a route matcher within the HTTP routing component.
 *
 * The MatcherInterface is responsible for determining if a given HTTP request
 * matches a defined route in the application. If a match is found, it returns
 * an instance of MatchedRoute that encapsulates details about the matched route
 * and any parameters extracted from the request.
 *
 * Implementations of this interface must handle the routing logic that parses
 * the request URI and method to map it to configured routes, handling different
 * HTTP methods and route patterns.
 *
 * @package Neu\Component\Http\Routing\Matcher
 */
interface MatcherInterface
{
    /**
     * Matches the given HTTP request to a route defined in the routing configuration.
     *
     * This method analyzes the provided request and attempts to find a route that
     * corresponds to the request's URI and method. If a suitable route is found,
     * it returns a MatchedRoute object containing the route and any parameters
     * associated with it.
     *
     * @param RequestInterface $request The HTTP request to match against configured routes.
     *
     * @throws NotFoundHttpException If no route matches the request URI.
     * @throws MethodNotAllowedHttpException If a route matches the request URI but not the HTTP method.
     * @throws RuntimeException If an error occurs during the matching process.
     *
     * @return Result An object containing the matched route and its parameters.
     */
    public function match(RequestInterface $request): Result;
}

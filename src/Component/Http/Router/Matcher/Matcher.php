<?php

declare(strict_types=1);

/*
 * This file is part of the Neutomic package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Neu\Component\Http\Router\Matcher;

use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\UriInterface;
use Neu\Component\Http\Router\Exception\MethodNotAllowedHttpException;
use Neu\Component\Http\Router\Exception\RouteNotFoundHttpException;
use Neu\Component\Http\Router\PrefixMap\PrefixMap;
use Neu\Component\Http\Router\Registry\RegistryInterface;
use Neu\Component\Http\Router\Route;
use Throwable;
use Override;

use function array_key_exists;
use function array_map;
use function is_string;
use function preg_match;
use function strlen;
use function substr;

final class Matcher implements MatcherInterface
{
    private RegistryInterface $registry;

    /**
     * @var array<non-empty-string, null|non-empty-list<Method>> $allowedMethods
     */
    private array $allowedMethods = [];

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function match(RequestInterface $request): Result
    {
        try {
            [$route, $parameters] = $this->matchImpl($request->getMethod(), $request->getUri());
            $parameters = array_map(
                /**
                 * @param non-empty-string $value
                 *
                 * @return non-empty-string
                 */
                static fn (string $value): string => rawurldecode($value) ?: $value,
                $parameters,
            );

            return new Result($route, $this->registry->getHandler($route->name), $parameters);
        } catch (RouteNotFoundHttpException $e) {
            $allowed = $this->getAllowedMethods($request->getUri());
            if (null === $allowed) {
                throw $e;
            }

            if ($allowed === [Method::Get] && $request->getMethod() === Method::Head) {
                [$route, $parameters] = $this->matchImpl(Method::Get, $request->getUri());
                $parameters = array_map(
                    /**
                     * @param non-empty-string $value
                     *
                     * @return non-empty-string
                     */
                    static fn (string $value): string => rawurldecode($value) ?: $value,
                    $parameters,
                );

                return new Result($route, $this->registry->getHandler($route->name), $parameters);
            }

            throw MethodNotAllowedHttpException::create($request->getMethod(), $request->getUri(), $allowed, $e);
        } catch (Throwable $throwable) {
            if ($throwable instanceof MethodNotAllowedHttpException) {
                throw $throwable;
            }

            throw new RuntimeException('An Error accrued while resolving route.', (int)$throwable->getCode(), $throwable);
        }
    }

    /**
     * Return the list of HTTP Methods that are allowed for the given path.
     *
     * @return null|non-empty-list<Method>
     */
    private function getAllowedMethods(UriInterface $uri): null|array
    {
        /** @var non-empty-string $path */
        $path = $uri->getPath() ?: '/';
        if (array_key_exists($path, $this->allowedMethods)) {
            return $this->allowedMethods[$path];
        }

        $allowed = [];
        foreach (Method::cases() as $method) {
            try {
                $this->matchImpl($method, $uri);

                $allowed[] = $method;
            } catch (RouteNotFoundHttpException) {
                continue;
            }
        }

        $this->allowedMethods[$path] = $allowed === [] ? null : $allowed;

        return $this->allowedMethods[$path];
    }

    /**
     * @throws RouteNotFoundHttpException
     *
     * @return array{0: Route, 1: array<non-empty-string, non-empty-string>}
     */
    private function matchImpl(Method $method, UriInterface $uri): array
    {
        $map = $this->registry->getPrefixMaps()[$method->value] ?? null;
        if ($map === null) {
            throw RouteNotFoundHttpException::create($method, $uri);
        }

        /** @var non-empty-string $path */
        $path = $uri->getPath() ?: '/';

        return self::matchWithMap($method, $uri, $path, $map);
    }

    /**
     * @param Method $method
     * @param UriInterface $uri
     * @param string $path
     * @param PrefixMap $map
     *
     * @throws RouteNotFoundHttpException
     *
     * @return array{0: Route, 1: array<non-empty-string, non-empty-string>}
     */
    private static function matchWithMap(Method $method, UriInterface $uri, string $path, PrefixMap $map): array
    {
        if (isset($map->literals[$path])) {
            return[$map->literals[$path], []];
        }

        if ($prefixes = $map->prefixes) {
            $prefix = substr($path, 0, $map->prefixLength);
            if ($prefix_map = $prefixes[$prefix] ?? null) {
                return self::matchWithMap($method, $uri, substr($path, $map->prefixLength), $prefix_map);
            }
        }

        foreach ($map->regexps as $regexp => $sub) {
            if (preg_match('#^' . $regexp . '#', $path, $matches) !== 1) {
                continue;
            }

            /** @var array<non-empty-string, non-empty-string> $data */
            $data = [];
            foreach ($matches as $name => $match) {
                if (is_string($name) && $name !== '' && $match !== '') {
                    $data[$name] = $match;
                }
            }

            $remaining = substr($path, strlen($matches[0]));
            if ($sub->isRoute()) {
                if ($remaining === '') {
                    return [$sub->getRoute(), $data];
                }

                continue;
            }

            try {
                [$route, $sub_data] = self::matchWithMap($method, $uri, $remaining, $sub->getMap());
            } catch (RouteNotFoundHttpException) {
                continue;
            }

            return [$route, $data + $sub_data];
        }

        throw RouteNotFoundHttpException::create($method, $uri);
    }
}

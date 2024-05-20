<?php

declare(strict_types=1);

namespace Neu\Component\Http\Router\Matcher;

use Neu\Component\Cache\StoreInterface;
use Neu\Component\Http\Exception\MethodNotAllowedHttpException;
use Neu\Component\Http\Exception\NotFoundHttpException;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\UriInterface;
use Neu\Component\Http\Router\Internal\PrefixMatching\PrefixMap;
use Neu\Component\Http\Router\Route\Registry\RegistryInterface;
use Neu\Component\Http\Router\Route\Route;
use Throwable;

use function array_key_exists;
use function array_map;
use function is_string;
use function preg_match;
use function strlen;
use function substr;

final class Matcher implements MatcherInterface
{
    private RegistryInterface $registry;
    private StoreInterface $cache;

    /**
     * @var array<non-empty-string, null|non-empty-list<Method>> $allowedMethods
     */
    private array $allowedMethods = [];

    /**
     * @var null|array<non-empty-string, PrefixMap> $map
     */
    private ?array $map = null;

    public function __construct(RegistryInterface $registry, StoreInterface $cache)
    {
        $this->registry = $registry;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function match(RequestInterface $request): Result
    {
        try {
            [$route, $parameters] = $this->matchImpl($request->getMethod(), $request->getUri());
            $parameters = array_map(static fn(string $value): string => rawurldecode($value), $parameters);

            return new Result($route, $this->registry->getHandler($route->name), $parameters);
        } catch (NotFoundHttpException $e) {
            $allowed = $this->getAllowedMethods($request->getUri());
            if (null === $allowed) {
                throw $e;
            }

            if ($allowed === [Method::Get] && $request->getMethod() === Method::Head) {
                [$route, $parameters] = $this->matchImpl(Method::Get, $request->getUri());
                $parameters = array_map(static fn(string $value): string => rawurldecode($value), $parameters);

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
     * @param non-empty-string $path
     *
     * @return null|non-empty-list<Method>
     */
    private function getAllowedMethods(UriInterface $uri): ?array
    {
        $path = $uri->getPath();
        if (array_key_exists($path, $this->allowedMethods)) {
            return $this->allowedMethods[$path];
        }

        $allowed = [];
        foreach (Method::cases() as $method) {
            try {
                $this->matchImpl($method, $uri);

                $allowed[] = $method;
            } catch (NotFoundHttpException) {
                continue;
            }
        }

        $this->allowedMethods[$path] = $allowed === [] ? null : $allowed;

        return $this->allowedMethods[$path];
    }

    /**
     * @return array<non-empty-string, PrefixMap>
     */
    private function getMap(): array
    {
        if ($this->map !== null) {
            return $this->map;
        }

        $this->map = $this->cache->compute('__routing__' . $this->registry->getHash(), function (): array {
            $routes = [];
            foreach ($this->registry->getRoutes() as $route) {
                foreach ($route->methods as $method) {
                    $routes[$method->value][] = $route;
                }
            }

            return array_map(PrefixMap::fromFlatMap(...), $routes);
        });

        return $this->map;
    }

    /**
     * @param Method $method
     * @param non-empty-string $path
     *
     * @throws NotFoundHttpException
     *
     * @return array{0: Route, 1: array<string, string>}
     */
    private function matchImpl(Method $method, UriInterface $uri): array
    {
        $map = $this->getMap()[$method->value] ?? null;
        if ($map === null) {
            throw NotFoundHttpException::create($method, $uri);
        }

        return self::matchWithMap($method, $uri, $uri->getPath(), $map);
    }

    /**
     * @param Method $method
     * @param UriInterface $uri
     * @param string $path
     * @param PrefixMap $map
     *
     * @throws NotFoundHttpException
     *
     * @return array{0: Route, 1: array<string, string>}
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

            /** @var array<string, string> $data */
            $data = [];
            foreach ($matches as $name => $match) {
                if (is_string($name)) {
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
            } catch (NotFoundHttpException) {
                continue;
            }

            return [$route, $data + $sub_data];
        }

        throw NotFoundHttpException::create($method, $uri);
    }
}

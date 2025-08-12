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

namespace Neu\Framework\Middleware;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Runtime\ContentDelivery\ContentDeliverer;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Exception\FileNotFoundHttpException;
use Neu\Component\Http\Runtime\Exception\FilesystemException;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;
use Neu\Component\Http\Runtime\Middleware\PrioritizedMiddlewareInterface;
use Psl\Filesystem;
use Psl\Iter;
use Psl\Str;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Override;

use const Psl\Filesystem;

/**
 * Middleware to serve static content directly from the filesystem, based on the request URI.
 *
 * @psalm-suppress RedundantCondition
 * @psalm-suppress MissingThrowsDocblock
 */
final readonly class StaticContentMiddleware implements PrioritizedMiddlewareInterface
{
    public const int PRIORITY = -128;

    /**
     * @var array<string, string>
     */
    private array $roots;

    /**
     * @var list<string>
     */
    private array $extensions;
    private ContentDeliverer $deliverer;
    private LoggerInterface $logger;
    private int $priority;

    /**
     * Constructs the middleware for static content delivery.
     *
     * @param ContentDeliverer $deliverer The core service used for the actual file delivery.
     * @param array<string, string> $roots Associative array of URI prefixes mapped to filesystem paths.
     *                                     These are used to resolve the physical file locations from the request URIs.
     * @param list<string> $extensions List of allowed file extensions.
     *                                 Requests for files with extensions not in this list will be passed to the next handler.
     * @param LoggerInterface $logger Used for logging various information and errors throughout the file delivery process.
     *
     * @throws RuntimeException if any of the provided document root directories is not readable or does not exist.
     */
    public function __construct(ContentDeliverer $deliverer, array $roots, array $extensions = [], LoggerInterface $logger = new NullLogger(), int $priority = self::PRIORITY)
    {
        foreach ($roots as $prefix => $root) {
            unset($roots[$prefix]);

            $roots['/' . Str\Byte\trim_left($prefix, '/')] = $root;
        }

        $this->roots = $roots;
        $this->extensions = $extensions;
        $this->deliverer = $deliverer;
        $this->logger = $logger;
        $this->priority = $priority;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(Context $context, RequestInterface $request, HandlerInterface $next): ResponseInterface
    {
        if ([] === $this->roots) {
            // If no roots are configured, pass the request to the next handler:
            return $next->handle($context, $request);
        }

        $path = $request->getUri()->getPath();
        $path = '/' . Str\Byte\trim_left($path, '/');

        // If a suspicious path traversal attempt is detected:
        if (Str\Byte\contains($path, "..")) {
            // Log the attempt:
            $this->logger->warning("Suspicious path traversal attempt: {path}", [
                'path' => $path,
            ]);

            // Throw a forbidden exception:
            throw new HttpException(StatusCode::Forbidden, message: 'Suspicious path traversal attempt.');
        }

        // Get the file extension:
        $extension = Filesystem\get_extension($path);
        // If the file has no extension:
        if (null === $extension) {
            assert($this->logger->debug('Skipped file "{path}" with no extension.', [
                'path' => $path,
            ]) || true);

            // Pass the request to the next handler:
            return $next->handle($context, $request);
        }

        // Lowercase the extension:
        $extension = Str\Byte\lowercase($extension);

        // If we have configured extensions:
        if ([] !== $this->extensions) {
            // Check if the extension is not in the list of configured extensions:
            if (!Iter\contains($this->extensions, $extension)) {
                assert($this->logger->debug('Requested file "{path}" has an extension "{extension}" that is not allowed.', [
                    'path' => $path,
                    'extension' => $extension,
                ]) || true);

                // Pass the request
                return $next->handle($context, $request);
            }
        }

        foreach ($this->roots as $prefix => $root) {
            // If the request path does not start with the prefix:
            if (!Str\Byte\starts_with($path, $prefix)) {
                // Log the mismatch:
                assert($this->logger->debug('Request path "{path}" does not start with the prefix "{prefix}" for root "{root}".', [
                    'path' => $path,
                    'prefix' => $prefix,
                    'root' => $root,
                ]) || true);

                // Continue to the next root:
                continue;
            }

            // Remove the prefix from the path:
            $path = Str\Byte\strip_prefix($path, $prefix);
            // Check if the file exists:
            $file = $root . Filesystem\SEPARATOR . $path;
            try {
                // Deliver the file:
                return $this->deliverer->deliver($request, $file);
            } catch (FileNotFoundHttpException) {
                assert($this->logger->debug('Requested file "{path}" does not exist within root "{root}".', [
                    'path' => $path,
                    'root' => $root,
                ]) || true);

                // Continue to the next root:
                continue;
            } catch (FilesystemException $exception) {
                // Log the failure:
                $this->logger->error('Failed to deliver file "{file}": {message}', [
                    'file' => $file,
                    'message' => $exception->getMessage(),
                ]);

                // Throw an internal server error:
                throw new HttpException(StatusCode::InternalServerError, [], 'Failed to deliver file.', $exception);
            }
        }

        // Pass the request to the next handler:
        return $next->handle($context, $request);
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function getPriority(): int
    {
        return $this->priority;
    }
}

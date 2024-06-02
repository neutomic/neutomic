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

namespace Neu\Examples\Framework;

use Neu;
use Neu\Component\DependencyInjection\ContainerBuilder;
use Neu\Component\DependencyInjection\ContainerBuilderInterface;
use Neu\Component\DependencyInjection\Project;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Router\Route;
use Neu\Component\Http\Runtime\Context;
use Neu\Component\Http\Runtime\Handler\HandlerInterface;

use Psl\Type\Exception\AssertException;
use function Neu\Framework\entrypoint;
use function Psl\Json\typed;
use function Psl\Type\non_empty_string;
use function Psl\Type\nonnull;

require_once __DIR__ . '/../../vendor/autoload.php';

#[Route(name: 'cache_get', pattern: '/cache/{key}', methods: [Method::Get])]
final readonly class CacheGetHandler implements HandlerInterface
{
    public function __construct(private Neu\Component\Cache\StoreManagerInterface $storeManager)
    {
    }

    /**
     * @throws AssertException
     * @throws Neu\Component\Http\Exception\LogicException
     * @throws Neu\Component\Cache\Exception\RuntimeException
     * @throws Neu\Component\Cache\Exception\InvalidKeyException
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $key = non_empty_string()->assert($request->getAttribute('key'));

        try {
            $value = non_empty_string()->assert($this->storeManager->getDefaultStore()->get($key));
            return Response\text(sprintf('%s: %s', $key, $value));
        } catch (Neu\Component\Cache\Exception\UnavailableItemException) {
            return Response\text(sprintf('Unavailable key %s', $key))->withStatus(400);
        }
    }
}


#[Route(name: 'cache_set', pattern: '/cache/{key}', methods: [Method::Post])]
final readonly class CacheSetHandler implements HandlerInterface
{
    public function __construct(private Neu\Component\Cache\StoreManagerInterface $storeManager)
    {
    }

    /**
     * @throws AssertException
     * @throws Neu\Component\Http\Exception\LogicException
     * @throws Neu\Component\Cache\Exception\RuntimeException
     * @throws Neu\Component\Cache\Exception\InvalidKeyException
     * @throws Neu\Component\Http\Exception\RuntimeException
     * @throws Neu\Component\Http\Message\Exception\TimeoutException
     * @throws \Psl\Json\Exception\DecodeException
     * @throws Neu\Component\Cache\Exception\InvalidValueException
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $key = non_empty_string()->assert($request->getAttribute('key'));

        $value = typed(
            nonnull()->assert($request->getBody())->getContents(),
            non_empty_string()
        );

        $value = $this->storeManager->getDefaultStore()->update(
            $key,
            static fn () => $value
        );

        return Response\text(sprintf('%s: %s', $key, $value));
    }
}

#[Route(name: 'cache_delete', pattern: '/cache/{key}', methods: [Method::Delete])]
final readonly class CacheDeleteHandler implements HandlerInterface
{
    public function __construct(private Neu\Component\Cache\StoreManagerInterface $storeManager)
    {
    }

    /**
     * @throws AssertException
     * @throws Neu\Component\Http\Exception\LogicException
     * @throws Neu\Component\Cache\Exception\InvalidKeyException
     * @throws Neu\Component\Cache\Exception\RuntimeException
     */
    public function handle(Context $context, RequestInterface $request): ResponseInterface
    {
        $key = non_empty_string()->assert($request->getAttribute('key'));

        $this->storeManager->getDefaultStore()->delete($key);

        return Response\text(sprintf('Deleted: %s', $key));
    }
}

entrypoint(static function (Project $project): ContainerBuilderInterface {
    $project = $project->withConfig(null);

    $builder = ContainerBuilder::create($project);

    $builder->addConfiguration([
        'framework' => [
            'middleware' => [
                'static-content' => [
                    'roots' => [
                        '/' => __DIR__ . '/public'
                    ]
                ],
            ]
        ],
        'monolog' => [
            'default' => 'main',
            'channels' => [
                'main' => [
                    'handlers' => [
                        'monolog.handler.stderr',
                    ],
                ]
            ],
            'handlers' => [
                'stderr' => [
                    'type' => 'stderr',
                    'level' => $project->mode->isProduction() ? 'notice' : 'debug',
                    'formatter' => $project->mode->isProduction() ? 'monolog.formatter.line' : 'monolog.formatter.console',
                ],
            ],
        ],
        'http' => [
            'server' => [
                'sockets' => [
                    [
                        'host' => '127.0.0.1',
                        'port' => 2000,
                        'bind' => [
                            'reuse-port' => false,
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $builder->addExtensions([
        new Neu\Bridge\Monolog\DependencyInjection\MonologExtension(),
        new Neu\Framework\DependencyInjection\FrameworkExtension(),
    ]);

    return $builder;
});

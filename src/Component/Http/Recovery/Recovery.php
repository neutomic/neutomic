<?php

declare(strict_types=1);

namespace Neu\Component\Http\Recovery;

use Neu\Component\Http\Exception\HttpExceptionInterface;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\Response;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\StatusCode;
use Neu\Component\Http\Runtime\Context;
use Psl\Html;
use Psl\Iter;
use Psl\Str;
use Psl\Vec;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Throwable;

/**
 * @psalm-type ThrowablesConfiguration = array<class-string<Throwable>, array{
 *      log_level?: 'emergency'|'alert'|'critical'|'error'|'warning'|'notice'|'info'|'debug',
 *      status?: int,
 *      headers?: array<string, list<string>>,
 *  }>
 */
final readonly class Recovery implements RecoveryInterface
{
    private bool $debug;
    private LoggerInterface $logger;

    /**
     * @var ThrowablesConfiguration
     */
    private array $throwables;

    /**
     * Constructs the recovery handler.
     *
     * @param bool $debug Whether to enable debug mode.
     * @param LoggerInterface $logger The logger instance.
     * @param ThrowablesConfiguration $throwables The configuration for handling specific throwables.
     */
    public function __construct(bool $debug, LoggerInterface $logger, array $throwables = [])
    {
        $this->debug = $debug;
        $this->logger = $logger;
        $this->throwables = $throwables;
    }

    public function recover(Context $context, RequestInterface $request, Throwable $throwable): ResponseInterface
    {
        $log_level = LogLevel::ERROR;
        $status = StatusCode::InternalServerError;
        if ($throwable instanceof HttpExceptionInterface) {
            $log_level = match (true) {
                $throwable->getStatusCode()->isServerError() => LogLevel::CRITICAL,
                $throwable->getStatusCode()->isClientError() => LogLevel::WARNING,
                default => LogLevel::DEBUG,
            };

            $status = $throwable->getStatusCode();
        }

        $configuration = $this->throwables[$throwable::class] ?? [];
        $log_level = $configuration['log_level'] ?? $log_level;
        $status = $configuration['status'] ?? $status;
        $headers = $configuration['headers'] ?? [];

        $this->logger->log($log_level, 'An exception of type {type} occurred: {message}.', [
            'exception' => $throwable,
            'type' => $throwable::class,
            'message' => $throwable->getMessage(),
        ]);

        $content = $this->renderHtml($status, $throwable);

        $response = Response\html($content)->withStatus($status);
        foreach ($headers as $name => $values) {
            $response = $response->withHeader($name, $values);
        }

        return $response;
    }

    private function renderHtml(StatusCode $statusCode, Throwable $exception): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$statusCode->value} | {$statusCode->getReasonPhrase()}</title>
            <style>
                :root {
                    --background-main: #f9f9f9;
                    --background-container: #ffffff;
                    --background-debug: #ffffff;
                    --background-trace: #f8f8f8;
                    --background-status: #ffdddd;
                    --border-color-base: #ccc;
                    --border-color-light: #bbb;
                    --border-color-hover: #999;
                    --text-color-primary: #333;
                    --text-color-secondary: #555;
                    --status-color: #ff0000;
                }
                body {
                    margin: 0;
                    padding: 4rem;
                    font-family: Arial, sans-serif;
                    background: var(--background-main);
                    color: var(--text-color-primary);
                    line-height: 1.5;
                }
                .container, .debug-container {
                    max-width: 800px;
                    background-color: var(--background-container);
                    padding: 2rem;
                    border: 1px solid var(--border-color-base);
                    margin: 0 auto 2rem;
                }
                .debug-container {
                    background-color: var(--background-debug);
                    display: grid;
                    grid-gap: 1rem;
               }
                .title, .description, .text {
                    margin-top: 0;
                    margin-bottom: 1rem;
                }
                .title {
                    font-size: 2.4rem;
                }
                .description {
                    font-size: 1.2rem;
                    color: var(--text-color-secondary);
                }
                .text {
                    font-size: 1rem;
                    color: var(--text-color-secondary);
                }
                .status {
                    color: var(--status-color);
                    background-color: var(--background-status);
                    padding: 0.2rem 0.5rem;
                }
                .links {
                    margin-top: 2rem;
                }
                .link {
                    padding: 0.6rem 1.2rem;
                    color: var(--text-color-secondary);
                    font-size: 1rem;
                    text-decoration: none;
                    border: 1px solid var(--border-color-light);
                    transition: all 0.3s ease;
                    background-color: var(--background-container);
                }
                .link:hover {
                    color: var(--text-color-primary);
                    cursor: pointer;
                    border-color: var(--border-color-hover);
                }
                .throwable {
                    max-width: 800px;
                    padding: 2rem;
                    margin: 2rem auto;
                    border: 1px solid var(--border-color-light);
                    background-color: var(--background-debug);
                }
                .throwable-message {
                    margin: 0;
                    font-weight: normal;
                    font-size: 1.2rem;
                }
                .throwable-type {
                    font-weight: bold;
                    text-decoration: none;
                }
                .throwable-source {
                    display: block;
                    font-weight: normal;
                    color: var(--text-color-secondary);
                    font-size: 1rem;
                    text-decoration: underline;
                }
                .trace-container {
                    background-color: var(--background-trace);
                    border: 1px solid var(--border-color-light);
                    color: var(--text-color-primary);
                    padding: 1rem;
                    margin-top: 1rem;
                    max-height: 20rem;
                    overflow-y: auto;
                }
                .trace-item {
                    margin-bottom: 1rem;
                    font-family: 'Courier New', monospace;
                }
                .trace-call {
                    margin: 0;
                    color: var(--text-color-primary);
                    font-size: 1rem;
                    font-weight: bold;
                    cursor: pointer;
                }
                .trace-source {
                    font-size: 0.9rem;
                    color: var(--text-color-secondary);
                }
            </style>
        </head>
        <body>
        <div class="container">
            <h1 class="title">Oops! an error occurred</h1>
            <p class="description">The server returned a <code class="status">{$statusCode->value}</code> response.</p>
            <p class="text">Something went wrong while trying to process your request. we will fix this as soon as possible. sorry for the inconvenience.</p>
            <div class="links">
                <a class="link" onclick="window.history.back();">back</a>
                <a class="link" onclick="window.location.reload();">retry</a>
            </div>
        </div>
        {$this->renderDebugInformation($exception)}
        </body>
        </html>
        HTML;
    }

    private function renderDebugInformation(Throwable $throwable): string
    {
        if (!$this->debug) {
            return '';
        }

        $comment = '<!-- ' . Html\encode((string) $throwable) . ' -->';

        return  $this->renderThrowableInformation($throwable) . $comment;
    }

    private function renderThrowableInformation(Throwable $throwable): string
    {
        $exceptionType = $throwable::class;
        $shortExceptionType = $this->shortenType($exceptionType);
        $exceptionMessage = $throwable->getMessage();
        $exceptionFile = $throwable->getFile();
        $exceptionLine = $throwable->getLine();
        $exceptionTrace = $this->renderTrace($throwable);

        $result = <<<HTML
            <div class="throwable">
                <div class="throwable-summary">
                    <h2 class="throwable-message"><span class="throwable-type">{$shortExceptionType}</span>: {$exceptionMessage}</h2>
                    <a href="idea://open?file={$exceptionFile}&line={$exceptionLine}" class="throwable-source">{$exceptionFile}:{$exceptionLine}</a>
                </div>
                <div class="trace-container">{$exceptionTrace}</div>
            </div>
        HTML;

        $previous = $throwable->getPrevious();
        if ($previous !== null) {
            $result .= $this->renderThrowableInformation($previous);
        }

        return $result;
    }

    private function renderTrace(Throwable $throwable): string
    {
        $frames = Vec\filter(
            $throwable->getTrace(),
            static fn(array $frame): bool => isset($frame['function'], $frame['file']),
        );

        $output = '<div class="trace-list">';
        foreach ($frames as $frame) {
            $file = Html\encode_special_characters((string) $frame['file']);
            $line = Html\encode_special_characters((string) $frame['line'] ?? '');
            $function = Html\encode_special_characters((string) $frame['function']);

            if (isset($frame['class'])) {
                $class = $this->shortenType($frame['class']);
                $call = $class . Html\encode_special_characters($frame['type']) . $function . '(...)';
            } else {
                $call = $this->shortenType($function) . '(...)';
            }

            $output .= <<<HTML
                <div class="trace-item">
                    <p class="trace-call">{$call}</p>
                    <a href="idea://open?file={$file}&line={$line}" class="trace-source">{$file}:{$line}</a>
                </div>
            HTML;
        }

        return $output . '</div>';
    }

    private function shortenType(string $type): string
    {
        $parts = Str\split($type, '\\');
        $short = Html\encode_special_characters(Iter\last($parts));
        $type = Html\encode_special_characters($type);

        return '<span title="' . $type . '">' . $short . '</span>';
    }
}

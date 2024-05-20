<?php

declare(strict_types=1);

namespace Neu\Component\Http\Server\Internal;

use Amp;
use Amp\ByteStream\ReadableIterableStream;
use Amp\Http\Cookie\CookieAttributes;
use Amp\Http\Cookie\ResponseCookie;
use Amp\Http\Server\Request as AmpRequest;
use Amp\Http\Server\Response as AmpResponse;
use Amp\Http\Server\Trailers;
use DateTimeImmutable;
use Neu\Component\Http\Exception\RuntimeException;
use Neu\Component\Http\Message\Method;
use Neu\Component\Http\Message\ProtocolVersion;
use Neu\Component\Http\Message\Request;
use Neu\Component\Http\Message\RequestBody;
use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Message\Trailer;
use Neu\Component\Http\Message\Uri;
use Neu\Component\Http\Runtime\Context;
use Psl\Vec;

/**
 * Represents a message convertor that converts between Amp and Neu HTTP messages.
 */
final readonly class MessageConvertor
{
    /**
     * Converts an Amp HTTP server request to a Neu HTTP server request.
     *
     * @param Request $request The Amp HTTP server request to convert.
     *
     * @return array{Context, RequestInterface}
     */
    public static function convertRequest(AmpRequest $request): array
    {
        $cookies = [];
        foreach ($request->getCookies() as $cookie) {
            $cookies[$cookie->getName()][] = $cookie->getValue();
        }

        $neuRequest = Request::create(
            Method::from($request->getMethod()),
            Uri::fromUrl((string) $request->getUri()),
            $request->getHeaders(),
        )
            ->withProtocolVersion(ProtocolVersion::from($request->getProtocolVersion()))
            ->withCookies($cookies)
            ->withQueryParameters($request->getQueryParameters())
            ->withAddedAttributes($request->getAttributes())
            ->withAttribute('client', $request->getClient())
            ;

        if ($ampTrailers = $request->getTrailers()) {
            foreach ($ampTrailers->getFields() as $field) {
                $neuRequest = $neuRequest->withTrailer(Trailer::create($field, Amp\async(
                    static fn(): array => $ampTrailers->await()->getHeaderArray($field),
                )));
            }
        }

        $context = new Context(
            Amp\Cluster\Cluster::getContextId(),
            $request->getClient()->getId(),
            $request->getClient()->getRemoteAddress()->toString(),
            $request->getClient()->getLocalAddress()->toString(),
            $request->getClient()->getTlsInfo(),
            static function (ResponseInterface $response): void {
                throw new RuntimeException('Unable to send informational response, feature not supported.');
            },
        );

        $body = $request->getBody();
        if (null !== $body) {
            $neuRequest = $neuRequest->withBody(RequestBody::fromReadableStream(
                $body,
                $body->increaseSizeLimit(...),
            ));
        }

        return [$context, $neuRequest];
    }

    /**
     * Converts a Neu HTTP server response to an Amp HTTP server response.
     *
     * @param ResponseInterface $response The Neu HTTP server response to convert.
     *
     * @return AmpResponse The converted Amp HTTP server response.
     */
    public static function convertResponse(ResponseInterface $response): AmpResponse
    {
        $ampResponse = new AmpResponse($response->getStatusCode());

        if ($body = $response->getBody()) {
            $ampResponse->setBody(new ReadableIterableStream($body->getIterator()));
        }

        // we set the headers after the body as Amphp
        // would remove content-length if we set it before the body
        foreach ($response->getHeaders() as $header => $values) {
            foreach ($values as $value) {
                $ampResponse->addHeader($header, $value);
            }
        }

        foreach ($response->getCookies() as $name => $cookies) {
            foreach ($cookies as $cookie) {
                $attribute = CookieAttributes::empty();
                if ($cookie->getDomain()) {
                    $attribute = $attribute->withDomain($cookie->getDomain());
                }

                if ($cookie->getPath()) {
                    $attribute = $attribute->withPath($cookie->getPath());
                }

                $expires = $cookie->getExpires();
                if (null !== $expires) {
                    $attribute = $attribute->withExpiry(new DateTimeImmutable('@' . $expires));
                }

                if ($cookie->getMaxAge()) {
                    $attribute = $attribute->withMaxAge($cookie->getMaxAge());
                }

                if ($cookie->getSecure()) {
                    $attribute = $attribute->withSecure();
                }

                if ($cookie->getHttpOnly()) {
                    $attribute = $attribute->withHttpOnly();
                }

                if ($cookie->getSameSite()) {
                    $attribute = $attribute->withSameSite($cookie->getSameSite()->value);
                }

                $ampResponse->setCookie(new ResponseCookie(
                    $name,
                    $cookie->getValue(),
                    $attribute,
                ));
            }
        }

        $trailers = $response->getTrailers();
        if ([] !== $trailers) {
            /** @var Amp\Future<array<string, list<string>> $future */
            $future = Amp\async(static function () use ($trailers): array {
                $result = [];
                foreach ($trailers as $trailer) {
                    $result[$trailer->getField()] = $trailer->getValue();
                }

                return $result;
            });

            $ampResponse->setTrailers(new Trailers($future, Vec\keys($trailers)));
        }

        return $ampResponse;
    }
}

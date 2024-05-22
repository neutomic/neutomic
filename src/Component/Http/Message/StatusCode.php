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

namespace Neu\Component\Http\Message;

/**
 * The HTTP status code enumeration.
 *
 * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6
 */
enum StatusCode: int
{
    case Continue = 100;
    case SwitchingProtocols = 101;
    case Processing = 102;
    case EarlyHints = 103;
    case OK = 200;
    case Created = 201;
    case Accepted = 202;
    case NonAuthoritativeInformation = 203;
    case NoContent = 204;
    case ResetContent = 205;
    case PartialContent = 206;
    case MultiStatus = 207;
    case AlreadyReported = 208;
    case IMUsed = 226;
    case MultipleChoices = 300;
    case MovedPermanently = 301;
    case Found = 302;
    case SeeOther = 303;
    case NotModified = 304;
    case UseProxy = 305;
    case TemporaryRedirect = 307;
    case PermanentRedirect = 308;
    case BadRequest = 400;
    case Unauthorized = 401;
    case PaymentRequired = 402;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case NotAcceptable = 406;
    case ProxyAuthenticationRequired = 407;
    case RequestTimeout = 408;
    case Conflict = 409;
    case Gone = 410;
    case LengthRequired = 411;
    case PreconditionFailed = 412;
    case PayloadTooLarge = 413;
    case URITooLong = 414;
    case UnsupportedMediaType = 415;
    case RangeNotSatisfiable = 416;
    case ExpectationFailed = 417;
    case ImATeapot = 418;
    case MisdirectedRequest = 421;
    case UnprocessableEntity = 422;
    case Locked = 423;
    case FailedDependency = 424;
    case TooEarly = 425;
    case UpgradeRequired = 426;
    case PreconditionRequired = 428;
    case TooManyRequests = 429;
    case RequestHeaderFieldsTooLarge = 431;
    case UnavailableForLegalReasons = 451;
    case InternalServerError = 500;
    case NotImplemented = 501;
    case BadGateway = 502;
    case ServiceUnavailable = 503;
    case GatewayTimeout = 504;
    case HTTPVersionNotSupported = 505;
    case VariantAlsoNegotiates = 506;
    case InsufficientStorage = 507;
    case LoopDetected = 508;
    case NotExtended = 510;
    case NetworkAuthenticationRequired = 511;
    case NetworkConnectTimeoutError = 599;

    /**
     * Returns the reason phrase for the status code.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6.1
     *
     * The reason phrases are meant to be understood by humans and are not used by machines to determine the response.
     *
     * @return string The reason phrase for the status code.
     */
    public function getReasonPhrase(): string
    {
        return match ($this) {
            self::Continue => 'Continue',
            self::SwitchingProtocols => 'Switching Protocols',
            self::Processing => 'Processing',
            self::EarlyHints => 'Early Hints',
            self::OK => 'OK',
            self::Created => 'Created',
            self::Accepted => 'Accepted',
            self::NonAuthoritativeInformation => 'Non-Authoritative Information',
            self::NoContent => 'No Content',
            self::ResetContent => 'Reset Content',
            self::PartialContent => 'Partial Content',
            self::MultiStatus => 'Multi-Status',
            self::AlreadyReported => 'Already Reported',
            self::IMUsed => 'IM Used',
            self::MultipleChoices => 'Multiple Choices',
            self::MovedPermanently => 'Moved Permanently',
            self::Found => 'Found',
            self::SeeOther => 'See Other',
            self::NotModified => 'Not Modified',
            self::UseProxy => 'Use Proxy',
            self::TemporaryRedirect => 'Temporary Redirect',
            self::PermanentRedirect => 'Permanent Redirect',
            self::BadRequest => 'Bad Request',
            self::Unauthorized => 'Unauthorized',
            self::PaymentRequired => 'Payment Required',
            self::Forbidden => 'Forbidden',
            self::NotFound => 'Not Found',
            self::MethodNotAllowed => 'Method Not Allowed',
            self::NotAcceptable => 'Not Acceptable',
            self::ProxyAuthenticationRequired => 'Proxy Authentication Required',
            self::RequestTimeout => 'Request Timeout',
            self::Conflict => 'Conflict',
            self::Gone => 'Gone',
            self::LengthRequired => 'Length Required',
            self::PreconditionFailed => 'Precondition Failed',
            self::PayloadTooLarge => 'Payload Too Large',
            self::URITooLong => 'URI Too Long',
            self::UnsupportedMediaType => 'Unsupported Media Type',
            self::RangeNotSatisfiable => 'Range Not Satisfiable',
            self::ExpectationFailed => 'Expectation Failed',
            self::ImATeapot => "I'm a teapot",
            self::MisdirectedRequest => 'Misdirected Request',
            self::UnprocessableEntity => 'Unprocessable Entity',
            self::Locked => 'Locked',
            self::FailedDependency => 'Failed Dependency',
            self::TooEarly => 'Too Early',
            self::UpgradeRequired => 'Upgrade Required',
            self::PreconditionRequired => 'Precondition Required',
            self::TooManyRequests => 'Too Many Requests',
            self::RequestHeaderFieldsTooLarge => 'Request Header Fields Too Large',
            self::UnavailableForLegalReasons => 'Unavailable For Legal Reasons',
            self::InternalServerError => 'Internal Server Error',
            self::NotImplemented => 'Not Implemented',
            self::BadGateway => 'Bad Gateway',
            self::ServiceUnavailable => 'Service Unavailable',
            self::GatewayTimeout => 'Gateway Timeout',
            self::HTTPVersionNotSupported => 'HTTP Version Not Supported',
            self::VariantAlsoNegotiates => 'Variant Also Negotiates',
            self::InsufficientStorage => 'Insufficient Storage',
            self::LoopDetected => 'Loop Detected',
            self::NotExtended => 'Not Extended',
            self::NetworkAuthenticationRequired => 'Network Authentication Required',
            self::NetworkConnectTimeoutError => 'Network Connect Timeout Error',
        };
    }

    /**
     * Returns whether the status code is informational.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6.2
     *
     * @return bool True if the status code is informational, false otherwise.
     */
    public function isInformational(): bool
    {
        return $this->value < 200;
    }

    /**
     * Returns whether the status code is successful.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6.3
     *
     * @return bool True if the status code is successful, false otherwise.
     */
    public function isSuccessful(): bool
    {
        return $this->value >= 200 && $this->value < 300;
    }

    /**
     * Returns whether the status code is a redirection.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6.4
     *
     * @return bool True if the status code is a redirection, false otherwise.
     */
    public function isRedirection(): bool
    {
        return $this->value >= 300 && $this->value < 400;
    }

    /**
     * Returns whether the status code is a client error.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6.5
     *
     * @return bool True if the status code is a client error, false otherwise.
     */
    public function isClientError(): bool
    {
        return $this->value >= 400 && $this->value < 500;
    }

    /**
     * Returns whether the status code is a server error.
     *
     * @see https://datatracker.ietf.org/doc/html/rfc7231#section-6.6
     *
     * @return bool True if the status code is a server error, false otherwise.
     */
    public function isServerError(): bool
    {
        return $this->value >= 500;
    }
}

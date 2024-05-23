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

namespace Neu\Component\Http\Message\Form;

use Neu\Component\Http\Exception\HttpException;
use Neu\Component\Http\Message\RequestInterface;

/**
 * Interface for parsing form data from HTTP requests.
 */
interface StreamedParserInterface
{
    /**
     * Parses the form data from the given HTTP request incrementally.
     *
     * @param RequestInterface $request The HTTP request containing form data.
     * @param null|ParseOptions $options Optional parsing options.
     *
     * @throws HttpException If the request body is too large or the maximum number of fields is exceeded.
     *
     * @return StreamedFormInterface The parsed form data.
     */
    public function parseStreamed(RequestInterface $request, null|ParseOptions $options = null): StreamedFormInterface;
}

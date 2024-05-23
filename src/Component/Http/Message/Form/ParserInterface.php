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
 * Interface for non-streamed form data parsing.
 */
interface ParserInterface
{
    /**
     * Parses the entire form data into memory.
     *
     * This method loads all form data into memory at once.
     *
     * If the data contains large amounts of data, it is recommended to use {@see StreamedParserInterface::parseStreamed()}
     * to parse the form incrementally, which is better for memory usage.
     *
     * @param RequestInterface $request The HTTP request containing form data.
     * @param null|ParseOptions $options Optional parsing options.
     *
     * @throws HttpException If the request body is too large or the maximum number of fields is exceeded.
     *
     * @return FormInterface A form data instance.
     */
    public function parse(RequestInterface $request, null|ParseOptions $options = null): FormInterface;
}

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

use Neu\Component\Http\Message\RequestInterface;

/**
 * Interface for parsing form data from HTTP requests.
 */
interface IncrementalFormParserInterface
{
    /**
     * Parses the form data from the given HTTP request.
     *
     * @param RequestInterface $request The HTTP request containing form data.
     * @param null|ParseOptions $options Optional parsing options.
     *
     * @return FormInterface The parsed form data.
     */
    public function parse(RequestInterface $request, null|ParseOptions $options = null): FormInterface;
}

<?php

declare(strict_types=1);

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

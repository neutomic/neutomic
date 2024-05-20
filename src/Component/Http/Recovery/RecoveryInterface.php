<?php

declare(strict_types=1);

namespace Neu\Component\Http\Recovery;

use Neu\Component\Http\Message\RequestInterface;
use Neu\Component\Http\Message\ResponseInterface;
use Neu\Component\Http\Runtime\Context;
use Throwable;

interface RecoveryInterface
{
    public function recover(Context $context, RequestInterface $request, Throwable $throwable): ResponseInterface;
}

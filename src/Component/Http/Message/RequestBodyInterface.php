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
 * Interface for HTTP request bodies that extends the general {@see BodyInterface} with features specific to handling HTTP requests.
 *
 * This interface includes methods to manage and enforce size limits on the data being read from the request body.
 */
interface RequestBodyInterface extends BodyInterface
{
    /**
     * Adjusts the size limit of the request body.
     *
     * This method can be used to increase or decrease the size limit based on specific request handling requirements,
     * such as when parsing large forms where the expected content might exceed initial size constraints.
     *
     * @param int $sizeLimit The new size limit for the request body, in bytes.
     */
    public function upgradeSizeLimit(int $sizeLimit): void;
}

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

namespace Neu\Component\DependencyInjection\Configuration;

/**
 * The strategy to use when combining two configuration documents.
 */
enum CombineStrategy
{
    /**
     * Merge the documents.
     *
     * @see Document::merge()
     */
    case Merge;

    /**
     * Merge the documents recursively.
     *
     * @see Document::merge()
     */
    case MergeRecursive;

    /**
     * Replace the document.
     *
     * @see Document::replace()
     */
    case Replace;

    /**
     * Replace the document recursively.
     *
     * @see Document::replace()
     */
    case ReplaceRecursive;
}

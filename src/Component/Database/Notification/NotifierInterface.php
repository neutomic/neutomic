<?php

declare(strict_types=1);

namespace Neu\Component\Database\Notification;

use Neu\Component\Database\Exception\ConnectionException;
use Neu\Component\Database\Exception\RuntimeException;
use Neu\Component\Database\QueryResultInterface;

interface NotifierInterface
{
    /**
     * Retrieve the channel name that is used for sending notifications.
     *
     * @return non-empty-string
     */
    public function getChannel(): string;

    /**
     * Send a notification to the channel.
     *
     * @param string $message - The message payload
     *
     * @throws RuntimeException If the operation fails due to unexpected condition.
     * @throws ConnectionException If the connection to the database is lost.
     */
    public function notify(string $message = ''): QueryResultInterface;
}

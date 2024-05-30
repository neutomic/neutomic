<?php

namespace Neu\Component\Http\Server;

use Amp\Sync\Channel;
use Amp\Sync\Mutex;
use Amp\Sync\Parcel;
use Neu\Component\Http\Exception\RuntimeException;

/**
 * Defines the contract for shared resources in a clustered server environment.
 *
 * This interface provides methods for accessing shared resources between the cluster and its workers.
 *
 * @see ClusterInterface
 * @see ClusterWorkerInterface
 */
interface SharedResourcesInterface
{
    /**
     * Get the mutex instance for the cluster.
     *
     * The mutex instance can be used to synchronize access to shared resources between the cluster and its workers.
     *
     * @return Mutex The mutex instance for the cluster.
     *
     * @throws RuntimeException If the mutex is unavailable, e.g., the worker has not started yet.
     */
    public function getMutex(): Mutex;

    /**
     * Get the parcel instance for the cluster.
     *
     * The parcel instance can be used to share data between the cluster and its workers.
     *
     * @return Parcel The parcel instance for the cluster.
     *
     * @throws RuntimeException If the parcel is unavailable, e.g., the worker has not started yet.
     */
    public function getParcel(): Parcel;

    /**
     * Get the channel instance for the cluster.
     *
     * The channel instance can be used to communicate between the cluster and its workers.
     *
     * @return Channel The channel instance for the cluster.
     */
    public function getChannel(): Channel;

    /**
     * Get the shared object for the specified identifier.
     *
     * The shared object can be used to share data between the cluster and its workers.
     *
     * @template T
     *
     * @param non-empty-string $identifier The identifier of the shared object.
     * @param T $default The default value of the shared object.
     *
     * @return SharedResource<T> The shared object for the specified identifier.
     */
    public function getOrCreateSharedResource(string $identifier, mixed $default): SharedResource;
}

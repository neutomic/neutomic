<?php

namespace Neu\Component\Http\Server;

use Amp\Sync\Parcel;
use Closure;

/**
 * A shared resource that can be used to share data between the cluster and the workers.
 *
 * @template T
 */
final readonly class SharedResource
{
    /**
     * The identifier of the shared object.
     *
     * @var non-empty-string
     */
    private string $identifier;

    /**
     * The parcel that contains the shared object value.
     *
     * @var Parcel<array<string, mixed>>
     */
    private Parcel $parcel;

    /**
     * The default value of the shared object.
     *
     * @var T
     */
    private mixed $default;

    /**
     * Create a new {@see SharedResource} instance.
     *
     * @param Parcel<array<string, mixed>> $parcel The parcel to use.
     */
    public function __construct(string $identifier, Parcel $parcel, mixed $default)
    {
        $this->identifier = $identifier;
        $this->parcel = $parcel;
        $this->default = $default;
    }

    /**
     * Get the value of the shared object.
     *
     * @return T The value of the shared object.
     *
     * @see Parcel::unwrap()
     */
    public function unwrap(): mixed
    {
        $values = $this->parcel->unwrap();

        return $values[$this->identifier] ?? $this->default;
    }

    /**
     * Invokes a closure while maintaining a lock on the shared object. The current value of the shared object is
     * provided as the first argument to the closure. The return value of the closure is stored as the new value of the
     * shared object.
     *
     * @template R of T
     *
     * @param (Closure(T):R) $closure
     *
     * @return R The value of the shared object after the closure was invoked.
     *
     * @see Parcel::synchronized()
     */
    public function synchronized(Closure $closure): mixed
    {
        /** @var T $previous */
        return $this->parcel->synchronized(function (array $values) use ($closure) {
            /** @var T $previous */
            $previous = $values[$this->identifier] ?? $this->default;

            $values[$this->identifier] = $closure($previous);

            return $values;
        })[$this->identifier];
    }
}

<?php

declare(strict_types=1);

namespace Neu\Component\Contract;

use Amp\Sync\Parcel;

interface ParcelManagerInterface
{
    /**
     * Retrieves the parcel with the specified name, creating it if it does not exist.
     *
     * @param string $name The name of the parcel to retrieve or create.
     *
     * @return Parcel The parcel with the specified name.
     */
    public function getOrCreateParcel(string $name): Parcel;
}

<?php

declare(strict_types=1);

namespace Rickytech\Library\DataTransferObject;

interface Caster
{
    public function cast(mixed $value): mixed;
}

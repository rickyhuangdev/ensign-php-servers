<?php

namespace Rickytech\Library\DataTransferObject;

interface Caster
{
    public function cast(mixed $value): mixed;
}

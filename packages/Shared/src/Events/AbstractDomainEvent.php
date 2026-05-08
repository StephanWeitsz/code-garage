<?php

namespace CodeGarage\Shared\Events;

use DateTimeImmutable;

abstract class AbstractDomainEvent implements DomainEvent
{
    public function __construct(
        protected DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}

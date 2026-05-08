<?php

namespace CodeGarage\Shared\Events;

interface DomainEvent
{
    public function occurredAt(): \DateTimeImmutable;
}

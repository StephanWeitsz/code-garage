<?php

namespace CodeGarage\Shared\Application\Services;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;

abstract class ApplicationService
{
    public function __construct(
        protected Dispatcher $events,
    ) {
    }

    /**
     * Execute a use case inside a database transaction.
     *
     * @template TReturn
     *
     * @param  callable(): TReturn  $callback
     * @return TReturn
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Dispatch a domain or integration event.
     */
    protected function dispatch(object $event): void
    {
        $this->events->dispatch($event);
    }
}

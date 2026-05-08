<?php

namespace CodeGarage\Shared\Infrastructure\Persistence;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use CodeGarage\Shared\Domain\Repositories\Repository;

abstract class EloquentRepository implements Repository
{
    /**
     * Return the fully-qualified Eloquent model class handled by this repository.
     */
    abstract protected function modelClass(): string;

    public function find(int|string $id): ?Model
    {
        return $this->newQuery()->find($id);
    }

    public function findOrFail(int|string $id): Model
    {
        $model = $this->find($id);

        if ($model === null) {
            throw (new ModelNotFoundException())->setModel($this->modelClass(), [$id]);
        }

        return $model;
    }

    public function all(array $columns = ['*']): Collection
    {
        return $this->newQuery()->get($columns);
    }

    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->newQuery()->paginate($perPage, $columns);
    }

    public function create(array $attributes): Model
    {
        return $this->newModel()->newQuery()->create($attributes);
    }

    public function update(Model $model, array $attributes): Model
    {
        $model->fill($attributes);
        $model->save();

        return $model->refresh();
    }

    public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    protected function newQuery(): Builder
    {
        return $this->newModel()->newQuery();
    }

    protected function newModel(): Model
    {
        $modelClass = $this->modelClass();

        return new $modelClass();
    }
}

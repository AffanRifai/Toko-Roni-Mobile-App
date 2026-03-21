<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

abstract class BaseEloquentRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseEloquentRepository constructor.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @inheritDoc
     */
    public function all(array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->get($columns);
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }

    /**
     * @inheritDoc
     */
    public function findById($id, array $columns = ['*'], array $relations = [], array $appends = []): ?Model
    {
        $model = $this->model->with($relations)->select($columns)->find($id);

        if ($model) {
            $model->append($appends);
        }

        return $model;
    }

    /**
     * @inheritDoc
     */
    public function create(array $payload): Model
    {
        return $this->model->create($payload);
    }

    /**
     * @inheritDoc
     */
    public function update($id, array $payload): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->update($payload);
    }

    /**
     * @inheritDoc
     */
    public function delete($id): bool
    {
        $model = $this->findById($id);

        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    /**
     * @inheritDoc
     */
    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Collection
    {
        return $this->model->with($relations)->where($criteria)->get($columns);
    }

    /**
     * @inheritDoc
     */
    public function firstOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->firstOrCreate($attributes, $values);
    }

    /**
     * @inheritDoc
     */
    public function updateOrCreate(array $attributes, array $values = []): Model
    {
        return $this->model->updateOrCreate($attributes, $values);
    }
}

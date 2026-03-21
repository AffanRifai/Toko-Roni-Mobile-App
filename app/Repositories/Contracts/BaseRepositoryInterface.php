<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all models.
     *
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function all(array $columns = ['*'], array $relations = []): Collection;

    /**
     * Get models with pagination.
     *
     * @param int $perPage
     * @param array $columns
     * @param array $relations
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []);

    /**
     * Find model by id.
     *
     * @param int|string $id
     * @param array $columns
     * @param array $relations
     * @param array $appends
     * @return Model|null
     */
    public function findById($id, array $columns = ['*'], array $relations = [], array $appends = []): ?Model;

    /**
     * Create a new model.
     *
     * @param array $payload
     * @return Model
     */
    public function create(array $payload): Model;

    /**
     * Update existing model.
     *
     * @param int|string $id
     * @param array $payload
     * @return bool
     */
    public function update($id, array $payload): bool;

    /**
     * Delete model by id.
     *
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool;

    /**
     * Find by specific criteria.
     *
     * @param array $criteria
     * @param array $columns
     * @param array $relations
     * @return Collection
     */
    public function findByCriteria(array $criteria, array $columns = ['*'], array $relations = []): Collection;

    /**
     * First or Create.
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function firstOrCreate(array $attributes, array $values = []): Model;

    /**
     * Update or Create.
     *
     * @param array $attributes
     * @param array $values
     * @return Model
     */
    public function updateOrCreate(array $attributes, array $values = []): Model;
}

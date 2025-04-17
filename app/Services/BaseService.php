<?php

namespace App\Services;

use App\Constants\ApplicationConstants;
use App\DTOs\BaseDTO;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

abstract class BaseService
{
  /**
   * Model class instance
   *
   * @var Model
   */
  protected $model;

  /**
   * Filter class name
   *
   * @var string
   */
  protected $filterClass;

  /**
   * Sort class name
   *
   * @var string
   */
  protected $sortClass;

  /**
   * Relations to eager load when retrieving a resource
   *
   * @var array
   */
  protected $with = [];

  /**
   * Get all resources with pagination, filtering, and sorting
   *
   * @param Request $request
   * @param int $perPage
   * @return LengthAwarePaginator
   */
  public function getAll(Request $request, int $perPage = ApplicationConstants::PER_PAGE): LengthAwarePaginator
  {
    $query = $this->model::query();

    // Apply filters if filter class is set
    if ($this->filterClass && class_exists($this->filterClass)) {
      $filter = new $this->filterClass($request);
      $query = $filter->apply($query);
    }

    // Apply sorting if sort class is set
    if ($this->sortClass && class_exists($this->sortClass)) {
      $sort = new $this->sortClass($request);
      $query = $sort->apply($query);
    }

    // Eager load relations
    if (! empty($this->with)) {
      $query->with($this->with);
    }

    // Paginate results
    return $query->paginate($perPage);
  }

  /**
   * Create a new resource
   *
   * @param BaseDTO $dto Data transfer object containing the resource data
   * @param array|null $relations Optional relations to sync
   * @return Model The created resource
   * @throws ValidationException
   * @throws Exception
   */
  public function create(BaseDTO $dto, ?array $relations = null): Model
  {
    try {
      DB::beginTransaction();

      // Check if the resource has soft deletes and is already trashed
      if (method_exists($this->model, 'withTrashed')) {
        $existingTrashed = $this->findTrashed($dto);

        if ($existingTrashed) {
          // Restore and update trashed resource
          $existingTrashed->restore();
          $existingTrashed->update($dto->toArray());
          $resource = $existingTrashed;

          // Handle any relationships if needed
          if ($relations) {
            $this->syncRelations($resource, $relations);
          }

          DB::commit();

          return $this->fresh($resource);
        }
      }

      // Create new resource
      $resource = $this->model::create($dto->toArray());

      // Handle any relationships if needed
      if ($relations) {
        $this->syncRelations($resource, $relations);
      }

      DB::commit();

      return $this->fresh($resource);
    } catch (Exception $e) {
      DB::rollBack();

      throw $e;
    }
  }

  /**
   * Get a specific resource by ID
   *
   * @param string|int $id
   * @return Model
   * @throws ModelNotFoundException
   */
  public function getById(string|int $id): Model
  {
    return $this->model::with($this->with)->findOrFail($id);
  }

  /**
   * Update an existing resource
   *
   * @param string|int $id
   * @param BaseDTO $dto
   * @param array|null $relations Optional relations to sync
   * @return Model The updated resource
   * @throws ModelNotFoundException
   * @throws ValidationException
   * @throws Exception
   */
  public function update(string|int $id, BaseDTO $dto, ?array $relations = null): Model
  {
    try {
      // Find the resource
      $resource = $this->model::findOrFail($id);

      DB::beginTransaction();

      // Update the resource
      $resource->update($dto->toArray());

      // Handle any relationships if needed
      if ($relations) {
        $this->syncRelations($resource, $relations);
      }

      DB::commit();

      return $this->fresh($resource);
    } catch (Exception $e) {
      DB::rollBack();

      throw $e;
    }
  }

  /**
   * Delete a resource
   *
   * @param string|int $id
   * @return Model|void The deleted resource or void
   * @throws ModelNotFoundException
   * @throws Exception
   */
  public function delete(string|int $id)
  {
    try {
      $resource = $this->model::findOrFail($id);

      DB::beginTransaction();

      // Handle any necessary cleanup before deletion
      $this->beforeDelete($resource);

      // Delete the resource
      $resource->delete();

      DB::commit();

      // Some services return the deleted resource, this makes it optional
      if (method_exists($this, 'returnDeletedResource') && $this->returnDeletedResource()) {
        return $resource;
      }
    } catch (Exception $e) {
      DB::rollBack();

      throw $e;
    }
  }

  /**
   * Restore a soft-deleted resource
   *
   * @param string|int $id
   * @return Model The restored resource
   * @throws ModelNotFoundException
   * @throws Exception
   */
  public function restore(string|int $id): Model
  {
    try {
      $resource = $this->model::withTrashed()->findOrFail($id);

      if (! $resource->trashed()) {
        throw new Exception($this->getResourceNotDeletedMessage());
      }

      DB::beginTransaction();

      // Restore the resource
      $resource->restore();

      DB::commit();

      return $this->fresh($resource);
    } catch (Exception $e) {
      DB::rollBack();

      throw $e;
    }
  }

  /**
   * Get the message when trying to restore a resource that is not deleted
   *
   * @return string
   */
  protected function getResourceNotDeletedMessage(): string
  {
    return "This resource is not deleted.";
  }

  /**
   * Refresh a model with its relations
   *
   * @param Model $resource
   * @return Model
   */
  protected function fresh(Model $resource): Model
  {
    return $resource->fresh($this->with);
  }

  /**
   * Find a trashed resource based on unique attributes
   *
   * @param BaseDTO $dto
   * @return Model|null
   */
  protected function findTrashed(BaseDTO $dto): ?Model
  {
    // This method should be overridden by child classes
    // to implement logic for finding trashed resources
    return null;
  }

  /**
   * Sync relations for a resource
   *
   * @param Model $resource
   * @param array $relations
   * @return void
   */
  protected function syncRelations(Model $resource, array $relations): void
  {
    foreach ($relations as $relation => $ids) {
      if (method_exists($resource, $relation)) {
        // Even if ids is an empty array, sync should be called
        // to clear the relationships
        $resource->$relation()->sync($ids);
      }
    }
  }

  /**
   * Actions to perform before deleting a resource
   *
   * @param Model $resource
   * @return void
   */
  protected function beforeDelete(Model $resource): void
  {
    // Override in child classes if needed
  }

  /**
   * Whether to return the deleted resource
   *
   * @return bool
   */
  protected function returnDeletedResource(): bool
  {
    return false;
  }
}

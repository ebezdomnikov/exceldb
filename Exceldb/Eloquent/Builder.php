<?php namespace App\Exceldb\Eloquent;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class Builder extends EloquentBuilder
{
    /**
     * The methods that should be returned from query builder.
     *
     * @var array
     */
    protected $passthru = [
        'toSql', 'insert', 'insertGetId', 'pluck',
        'count', 'min', 'max', 'avg', 'sum', 'exists', 'push', 'pull',
    ];

    /**
     * Update a record in the database.
     *
     * @param  array  $values
     * @param  array  $options
     * @return int
     */
    public function update(array $values, array $options = [])
    {
//        // Intercept operations on embedded models and delegate logic
//        // to the parent relation instance.
//        if ($relation = $this->model->getParentRelation()) {
//            $relation->performUpdate($this->model, $values);
//
//            return 1;
//        }
//
//        return $this->query->update($this->addUpdatedAtColumn($values), $options);
    }

    /**
     * Insert a new record into the database.
     *
     * @param  array  $values
     * @return bool
     */
    public function insert(array $values)
    {
//        // Intercept operations on embedded models and delegate logic
//        // to the parent relation instance.
//        if ($relation = $this->model->getParentRelation()) {
//            $relation->performInsert($this->model, $values);
//
//            return true;
//        }
//
//        return parent::insert($values);
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array   $values
     * @param  string  $sequence
     * @return int
     */
    public function insertGetId(array $values, $sequence = null)
    {
//        // Intercept operations on embedded models and delegate logic
//        // to the parent relation instance.
//        if ($relation = $this->model->getParentRelation()) {
//            $relation->performInsert($this->model, $values);
//
//            return $this->model->getKey();
//        }
//
//        return parent::insertGetId($values, $sequence);
    }

    /**
     * Delete a record from the database.
     *
     * @return mixed
     */
    public function delete()
    {
//        // Intercept operations on embedded models and delegate logic
//        // to the parent relation instance.
//        if ($relation = $this->model->getParentRelation()) {
//            $relation->performDelete($this->model);
//
//            return $this->model->getKey();
//        }
//
//        return parent::delete();
    }

	/**
	 * Execute the query as a "select" statement.
	 *
	 * @param  array  $columns
	 * @return \Illuminate\Database\Eloquent\Collection|static[]
	 */
	public function get($columns = ['*'])
	{
		$builder = $this->applyScopes();

		$models = $builder->getModels($columns);

		// If we actually found models we will also eager load any relationships that
		// have been specified as needing to be eager loaded, which will solve the
		// n+1 query issue for the developers to avoid running a lot of queries.
		if (count($models) > 0) {
			$models = $builder->eagerLoadRelations($models);
		}

		return $builder->getModel()->newCollection($models);
	}
}

<?php namespace App\Exceldb\Eloquent;

use Illuminate\Database\Eloquent\Model as BaseModel;
use App\Exceldb\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use ResolveMap\ColumnName;

abstract class Model extends BaseModel
{
    /**
     * The collection associated with the model.
     *
     * @var string
     */
    protected $collection;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ColumnName::PRIMARY_KEY;


    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->collection ?: parent::getTable();
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \App\Exceldb\Query\Builder $query
     * @return \App\Exceldb\Eloquent\Builder|static
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return Builder
     */
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder($connection, $connection->getPostProcessor());
    }

    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return parent::__call($method, $parameters);
    }
}

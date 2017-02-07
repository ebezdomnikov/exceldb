<?php namespace App\Exceldb\Query;

use Illuminate\Database\Query\Builder as BaseBuilder;
use Illuminate\Support\Collection;
use App\Exceldb\Connection;

class Builder extends BaseBuilder {

    /**
     * The cursor timeout value.
     *
     * @var int
     */
    public $timeout;
    /**
     * Custom options to add to the query.
     *
     * @var array
     */
    public $options = [];
    /**
     * Indicate if we are executing a pagination query.
     *
     * @var bool
     */
    public $paginating = false;
    /**
     * Excel Sheet
     * @var \PHPExcel_Worksheet
     */
    protected $worksheet;
    /**
     * Check if we need to return Collections instead of plain arrays (laravel >= 5.3 )
     *
     * @var boolean
     */
    protected $useCollections;
    /**
     * Первая строка содержит имена столбцов
     * @var bool
     */
    protected $firstRowAsColumnNames = true;
    /**
     * Вернуть результат с форматом
     * @var bool
     */
    protected $withFormat = false;
    /**
     * Без первой строки
     * @var bool
     */
    protected $withOutFirstRow = true;
    /**
     * Карта замены имен столбцов
     * @var array
     */
    protected $columnMap = [];
    /**
     * @var \PHPExcel
     */
    private $workbook;

    /**
     * Create a new query builder instance.
     *
     * @param Connection $connection
     * @param Processor $processor
     */
    public function __construct( Connection $connection, Processor $processor )
    {
        $this->grammar        = new Grammar;
        $this->connection     = $connection;
        $this->processor      = $processor;
        $this->useCollections = $this->shouldUseCollections();
        $this->workbook       = $this->connection->getWorkBook();
    }

    /**
     * Returns true if Laravel or Lumen >= 5.3
     *
     * @return bool
     */
    protected function shouldUseCollections()
    {
        if (function_exists('app'))
        {
            $version = app()->version();
            $version =
                filter_var(explode(')', $version)[0], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION); // lumen
            return version_compare($version, '5.3', '>=');
        }

        return false;
    }

    /**
     * Execute a query for a single record by ID.
     *
     * @param  mixed $id
     * @param  array $columns
     * @return mixed
     * @throws \Exception
     */
    public function find( $id, $columns = [] )
    {
        throw new \Exception("Not supported");
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array $columns
     * @return array|static[]|Collection
     */
    public function get( $columns = [] )
    {
        return $this->getFresh($columns);
    }

    /**
     * Execute the query as a fresh "select" statement.
     *
     * @param  array $columns
     * @return array|static[]|Collection
     */
    public function getFresh( $columns = [] )
    {
        if (is_null($this->columns))
        {
            $this->columns = $columns;
        }
        if (in_array('*', $this->columns))
        {
            $this->columns = [];
        }
        //withColumnMap
        $this->connection->withColumnMap($this->columnMap);

        //withFirstRow
        if ($this->isNeedFirstRow())
        {
            $this->connection->withFirstRow();
        }

        if ($this->withFormat)
        {
            $this->connection->withFormat();
        }
        else
        {
            $this->connection->withOutFormat();
        }

        if ($this->firstRowAsColumnNames)
        {
            $this->connection->withFirstRowColumnNames();
        }
        else
        {
            $this->connection->noFirstRowColumnNames();
        }

        if ($this->withOutFirstRow)
        {
            $this->connection->withOutFirstRow();
        }
        else
        {
            $this->connection->withFirstRow();
        }

        $results = $this->worksheet->namedRangeToArray($this->from);


        return $this->useCollections ? new Collection($results) : $results;
    }

    public function isNeedFirstRow()
    {
        return ($this->withOutFirstRow == false);
    }

    /**
     * Проверка является ли значение в режиме только чтение
     * @param $id
     * @param $field
     *
     * @return mixed
     */
    public function isReadOnly( $id, $field )
    {
        // Подгружаем карту для резолвинга имен
        $this->worksheet->getRangeMap($this->from);

        $columnLetter = $this->worksheet->resolveByMap($field);

        return ($this->worksheet->isCellProtected($columnLetter . $id));
    }


    /**
     * Set the limit and offset for a given page.
     *
     * @param  int $page
     * @param  int $perPage
     * @return BaseBuilder|static
     * @throws \Exception
     */
    public function forPage( $page, $perPage = 15 )
    {
        throw new \Exception("Not supported");
    }

    /**
     * Insert a new record into the database.
     *
     * @param  array $values
     * @return bool
     * @throws \Exception
     */
    public function insert( array $values )
    {
        throw new \Exception("Not supported");
    }

    /**
     * Insert a new record and get the value of the primary key.
     *
     * @param  array $values
     * @param  string $sequence
     * @return int
     * @throws \Exception
     */
    public function insertGetId( array $values, $sequence = null )
    {
        throw new \Exception("Not supported");
    }

    /**
     * Update a record in the database.
     *
     * @param  array $values
     * @param  array $options
     * @return boolean
     * @throws \Exception
     */
    public function update( array $values, array $options = [] )
    {
        if ( ! isset($this->wheres[0]['column']) || $this->wheres[0]['column'] != 'id')
        {
            throw new \Exception("Id not specified!");
        }

        if ( ! isset($this->wheres[0]['operator']) || $this->wheres[0]['operator'] != '=')
        {
            throw new \Exception("Operator not valid!");
        }
        // Подгружаем карту для резолвинга имен
        $this->worksheet->getRangeMap($this->from);

        $id = (int) $this->wheres[ count($this->wheres) - 1 ]["value"];

        foreach ($values as $column => $value)
        {
            $columnLetter = $this->worksheet->resolveByMap($column);

            if ($columnLetter)
            {

                $pColumn = \PHPExcel_Cell::columnIndexFromString($columnLetter) - 1;
                //$res = $this->worksheet->setCellValue($columnLetter.$id, $value);
                if ($res = $this->worksheet->setCellValueByColumnAndRow($pColumn, $id, $value))
                {
                    return true;
                }

            }
        }
        $this->wheres = [];

        return false;
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param  string $column
     * @param  string|null $key
     * @return array
     * @throws \Exception
     */
    public function pluck( $column, $key = null )
    {
        throw new \Exception("Not supported");
    }

    /**
     * Delete a record from the database.
     *
     * @param  mixed $id
     * @return int
     * @throws \Exception
     */
    public function delete( $id = null )
    {
        throw new \Exception("Not supported");
    }

    /**
     * @param string $namedRange
     *
     * @return Builder
     */
    public function from( $namedRange )
    {
        if ($namedRange)
        {
            $this->worksheet = $this->connection->getWorkSheetCollectionByRangeName($namedRange);
        }

        return parent::from($namedRange);
    }

    /**
     * Run a truncate statement on the table.
     */
    public function truncate()
    {
        throw new \Exception("Not supported");
    }

    /**
     * Get an array with the values of a given column.
     *
     * @deprecated
     * @param  string $column
     * @param  string $key
     * @return array
     * @throws \Exception
     */
    public function lists( $column, $key = null )
    {
        throw new \Exception("Not supported");
    }

    /**
     * Get a new instance of the query builder.
     *
     * @return Builder
     */
    public function newQuery()
    {
        return new Builder($this->connection, $this->processor);
    }

    /**
     * Результат должен содержать формат ячейки
     * @return $this
     */
    public function withFormat()
    {
        $this->withFormat = true;

        return $this;
    }

    /**
     * @param array $map
     *
     * @return $this
     */
    public function withColumnMap( array $map )
    {
        $this->columnMap = $map;

        return $this;
    }

    /**
     * On first column as names (header)
     * @return $this
     */
    public function noFirstRowColumnNames()
    {
        $this->firstRowAsColumnNames = false;

        return $this;
    }

    /**
     *
     * @return $this
     */
    public function withFirstRow()
    {
        $this->withOutFirstRow = false;

        return $this;
    }

    /**
     *
     * @return $this
     */
    public function withOutFirstRow()
    {
        $this->withOutFirstRow = true;

        return $this;
    }

    /**
     * Результат НЕ должен содержать формат ячейки
     * @return $this
     */
    public function withOutFormat()
    {
        $this->withFormat = false;

        return $this;
    }


    public function isWithFormat()
    {
        return $this->withFormat;
    }


    /**
     * Handle dynamic method calls into the method.
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     */
    public function __call( $method, $parameters )
    {
        //        if ($method == 'withColumnMap' || $method == 'withFormat' || $method == 'noFirstRowColumnNames' || $method == 'withFirstRow' || $method == 'withOutFormat' || $method == 'withOutFirstRow')
        //        {
        //            call_user_func_array([$this->connection, $method], $parameters);
        //
        //            return $this;
        //        }
        //        elseif ($method == 'isWithFormat')
        //        {
        //            return call_user_func_array([$this->connection, $method], $parameters);
        //        }

        return parent::__call($method, $parameters);
    }
}

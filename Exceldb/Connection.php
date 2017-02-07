<?php namespace App\Exceldb;

use ResolveMap\ColumnName;

/**
 * @package     app\Exceldb
 */
class Connection extends \Illuminate\Database\Connection
{

	/**
	 * Excel handler
	 * @var
	 */
	protected $workbook;

	/**
	 *
	 * @var
	 */
	protected $worksheet;

	/**
	 * @var bool
	 */
	protected $calculateFormulas = true;

	/**
	 * @var bool
	 */
	protected $firstRowAsColumnNames = true;

	/**
	 * @var bool
	 */
	protected $withFormat = false;
	/**
	 * @var bool
	 */
	protected $withOutFirstRow = true;

	/**
	 * Карта замены имен столбцов
	 * @var array
	 */
	protected $columnMap = [];
	/**
	 * Current filename
	 * @var null
	 */
	protected $filename = null;
	/**
	 * Create a new database connection instance.
	 *
	 * @param  array   $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;

		// open Excel file
		if (
			isset($config['filename']) &&
			! empty($config['filename'])
		)
			$this->selectFilename($config['filename'])
				->open();

		$this->useDefaultPostProcessor();

		$this->useDefaultSchemaGrammar();
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
	 * Get Primary Key column name
	 * @return string
	 */
	public function getPrimaryKeyColumnName()
	{
		return ColumnName::PRIMARY_KEY;
	}

	/**
	 * Is need to use first row as columns names
	 * @return bool
	 */
	public function isFirstRowAsColumnNames()
	{
		return $this->firstRowAsColumnNames;
	}


	public function isNeedFirstRow()
	{
		return ($this->withOutFirstRow == false);
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
        $this->setFirstRowAsColumnNames(false);

        return $this;
    }

    /**
     * On first column as names (header)
     * @return $this
     */
    public function withFirstRowColumnNames()
    {
        $this->setFirstRowAsColumnNames(true);

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
	 * Off/On using first column as names
	 * @param bool $firstRowAsColumnNames
	 * @return $this
	 */
	public function setFirstRowAsColumnNames( $firstRowAsColumnNames )
	{
		$this->firstRowAsColumnNames = $firstRowAsColumnNames;
		return $this;
	}

	/**
	 * Calcalate formulas where get data
	 * @return bool
	 */
	public function isCalculateFormulas()
	{
		return $this->calculateFormulas;
	}

	/**
	 * Off/On calculating data
	 * @param bool $calculateFormulas
	 */
	public function setCalculateFormulas( $calculateFormulas )
	{
		$this->calculateFormulas = $calculateFormulas;
	}
	/**
	 * Get instance of current Excel file
	 * @return \Maatwebsite\Excel\LaravelExcelReader
	 */
	public function getWorkBook()
	{
		return $this->workbook;
	}

	/**
	 * Select Active sheet
	 * @param $sheetIndex
	 * @return $this
	 */
	public function selectWorkSheet($sheetIndex)
	{
		$this->worksheet = $this->getWorkBook()
			->setActiveSheetIndex($sheetIndex);

		return $this;
	}

	/**
	 * Current Excel Sheet
	 * @return mixed
	 */
	public function getWorkSheet()
	{
		return $this->worksheet;
	}

	/**
	 * Get the default post processor instance.
	 *
	 * @return Query\Processor
	 */
	protected function getDefaultPostProcessor()
	{
		return new Query\Processor;
	}

	/**
	 * Begin a fluent query against a database collection.
	 *
	 * @param  string  $collection
	 * @return Query\Builder
	 */
	public function collection($collection)
	{
		$processor = $this->getPostProcessor();

		$query = new Query\Builder($this, $processor);

		return $query->from($collection);
	}

	/**
	 * Begin a fluent query against a database collection.
	 *
	 * @param  string  $table
	 * @return Query\Builder
	 */
	public function table($table)
	{
		return $this->collection($table);
	}

	/**
	 * @param $sheetIndex
	 *
	 * @return WorkSheetCollection
	 */
	public function getWorkSheetCollection($workSheetIndex)
	{
		return new WorkSheetCollection(
			$this,
			$this->selectWorkSheet($workSheetIndex)->getWorkSheet()  //PHPExcel_Worksheet
		);
	}

	/**
	 * Get Sheet Collection by given range name
	 * @param $rangeName
	 *
	 * @return WorkSheetCollection
	 * @throws \Exception
	 */
	public function getWorkSheetCollectionByRangeName($rangeName)
	{
		$doc = $this->getWorkBook();

		$isExists = $doc->isNamedRangeExists($rangeName);

		if (! $isExists)
			throw new \Exception("Range '" . $rangeName . "' not found!");

		$sheetIndex = $doc->getWorkSheetIndexByRangeName($rangeName);

		if (is_null($sheetIndex))
			throw new \Exception("Sheet not found!");

		return $this->getWorkSheetCollection($sheetIndex);
	}

	/**
	 * Get a schema builder instance for the connection.
	 *
	 * @return Schema\Builder
	 */
	public function getSchemaBuilder()
	{
		return new Schema\Builder($this);
	}

    /**
     * Открыть удаленное подключением
     * @return Connection
     */
	public function openLocal()
    {
        return $this->open(true);
    }

    /**
     * Открыть удаленное подключением
     * @return Connection
     */
    public function openRemote()
    {
        return $this->open(false);
    }
	/**
	 * Open Excel
	 *
	 * @param bool $local
	 *
	 * @return $this
	 */
	public function open($local = false)
	{
		$filename = $this->getFilename();

		if ($local)
		{
		    echo "Local";
			if (empty($this->workbook))
			{
				$this->workbook = new Workbook($filename, $this);
			}
		}
		else
		{
			if (empty($this->workbook))
			{
				$this->workbook = new RemoteWorkbook($filename, $this);
			}
		}
		return $this;
	}

	/**
	 * Set current filename from Excel
	 * @param $filename
	 *
	 * @return $this
	 */
	public function selectFilename($filename)
	{
		$this->filename = $filename;
		return $this;
	}

	/**
	 * Determine whether we're logging queries.
	 *
	 * @return bool
	 */
	public function logging()
	{
		return $this->loggingQueries || env('APP_DEBUG') || env('APP_ENV');
	}

	/**
	 * @return null
	 */
	public function getFilename()
	{
		return $this->filename;
	}

	/**
	 * Destroy Excel instance
	 */
	public function disconnect()
	{
		unset($this->workbook);
	}

	/**
	 * Get the elapsed time since a given starting point.
	 *
	 * @param  int    $start
	 * @return float
	 */
	public function getElapsedTime($start)
	{
		return parent::getElapsedTime($start);
	}

	/**
	 * Get the PDO driver name.
	 *
	 * @return string
	 */
	public function getDriverName()
	{
		return 'exceldb';
	}

	/**
	 * Get the default schema grammar instance.
	 *
	 * @return Schema\Grammar
	 */
	protected function getDefaultSchemaGrammar()
	{
		return new Schema\Grammar;
	}

	/**
	 * Dynamically pass methods to the connection.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([$this->workbook, $method], $parameters);
	}
}

<?php namespace App\Exceldb;


class WorkSheetCollection
{
    /**
     * The connection instance.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The WorksheetContract
     *
     * @var
     */
    protected $worksheet;

    /**
     * @var
     */
    protected $namedRangeCollection;

	/**
	 * @var
	 */
	protected $map;

    /**
     * SheetCollection constructor.
     * @param Connection $connection
     * @param WorksheetContract|PHPExcel_Worksheet $worksheet
     */
    public function __construct(Connection $connection, $worksheet)
    {
        $this->connection = $connection;
        $this->worksheet  = $worksheet;

        $this->namedRangeCollection = new NamedRangeCollection($connection, $worksheet);

	    $this->cell = new Cell($connection, $worksheet);
    }

    /**
     * Handle dynamic method calls.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $start = microtime(true);

	    if (
	    	$method == 'namedRangeToArray' ||
		    $method == 'namedRangeWithStylesToArray' ||
		    $method == 'getRangeMap' ||
		    $method == 'resolveByMap'
	    )
	    {
		    $result = call_user_func_array([$this->namedRangeCollection, $method], $parameters);
	    }
        elseif ($method == 'setCellValue' || $method == 'getCellValue' || $method =='setCellValueByColumnAndRow')
	        $result = call_user_func_array([$this->cell, $method], $parameters);
	    else
            $result = call_user_func_array([$this->worksheet, $method], $parameters);

        if ($this->connection->logging()) {
            // Once we have run the query we will calculate the time that it took to run and
            // then log the query, bindings, and execution time so we will report them on
            // the event that the developer needs them. We'll log time in milliseconds.
            $time = $this->connection->getElapsedTime($start);

            $query = [];

            // Convert the query parameters to a json string.
            array_walk_recursive($parameters, function (&$item, $key) {
                if ($item instanceof ObjectID) {
                    $item = (string) $item;
                }
            });

            // Convert the query parameters to a json string.
            foreach ($parameters as $parameter) {
                try {
                    $query[] = json_encode($parameter);
                } catch (\Exception $e) {
                    $query[] = '{...}';
                }
            }

            $queryString = __CLASS__ . '['. $this->connection->getFilename() .'] ' . $method . '(' . implode(',', $query) . ')';

            $this->connection->logQuery($queryString, [], $time);
        }

        return $result;
    }
}

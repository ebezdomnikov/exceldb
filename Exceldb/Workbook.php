<?php namespace App\Exceldb;

use Excel;

class Workbook implements WorksheetContract
{
	private $doc;

	private $connection;

	/**
	 * Workbook constructor.
	 *
	 * @param            $filename
	 * @param Connection $connection
	 */
	public function __construct($filename, Connection $connection)
	{
		$this->connection = $connection;

		$this->doc = Excel::load(
			$filename, //Need Full source to file
			null,
			null,
			true // Not use Base Path
		);
	}

	public function isNamedRangeExists($namedRange)
	{
		return true;//$this->client->isNamedRangeExists($namedRange);
	}

	public function getWorkSheetIndexByRangeName($namedRange)
	{
		$range = $this->doc->getNamedRange($namedRange);

		return $this->doc->getIndex($range->getWorksheet());
	}

	public function isCellProtected($pCoord)
	{
		return ($this->worksheet->getStyle($pCoord)->getProtection()->getLocked() !== 'unprotected');
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

//		if ($method == 'getNamedRange')
//		{
//			//$result = call_user_func_array([$this->doc, $method], $parameters);
//			$time = $this->connection->getElapsedTime($start);
//			dd($time);
//		}
//		elseif($method == 'getIndex')
//		{
//			$result = call_user_func_array([$this->doc, $method], $parameters);
//		}
//		elseif($method == 'setActiveSheetIndex')
//		{
//			$result = call_user_func_array([$this->doc, $method], $parameters);
//		}
//		else
			$result = call_user_func_array([$this->doc, $method], $parameters);

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

	/**
	 *
	 * @return mixed
	 */
	public function getHashCode()
	{
		// TODO: Implement getHashCode() method.
	}
}
<?php namespace App\Exceldb;

/**
 * Удаленный Excel
 * @package     App\Exceldb
 */
class RemoteWorkbook implements WorksheetContract
{
	/**
	 * Подключение  к экселю
	 * @var Connection
	 */
	private $connection;

    /**
     * Клиента Ratchet для коммуникации с сервером
     * @var Client
     */
	private $client;

    /**
     * Индексы книг
     * @var
     */
	private $workSheetIndex;

	/**
	 * Workbook constructor.
	 *
	 * @param            $filename
	 * @param Connection $connection
	 */
	public function __construct($filename, Connection $connection)
	{
		$this->client = new Client($filename);

		$this->connection = $connection;
	}

    /**
     * Установка значения ячейки
     * @param $colRow
     * @param $data
     *
     * @return array
     */
	public function setCellValue($colRow, $data)
	{
		return $this->client->cellWrite($colRow, $data);
	}

    /**
     * Получение значения ячейки вычисленного
     * @param $colRow
     *
     * @return array
     */
	public function getCellCalculatedValue($colRow)
	{
		return $this->client->cellRead($colRow);
	}

    /**
     * Установка значения ячейки по строке и столбцу
     * @param int $pColumn
     * @param int $pRow
     * @param null $pValue
     * @param bool $returnCell
     *
     * @return array
     */
	public function setCellValueByColumnAndRow($pColumn = 0, $pRow = 1, $pValue = null, $returnCell = false)
    {
        return $this->client->setCellValueByColumnAndRow($pColumn, $pRow, $pValue, $returnCell);
    }

    /**
     * Активация текущей книги
     * @param $index
     *
     * @return $this
     */
	public function setActiveSheetIndex($index)
	{
		$this->client->setActiveSheetIndex($index);
		return $this;
	}

    /**
     * Получение книги по именованному диапазону
     * @param $namedRange
     *
     * @return mixed
     */
	public function getWorkSheetIndexByRangeName($namedRange)
	{
		if (isset($this->workSheetIndex[$namedRange]))
			return $this->workSheetIndex[$namedRange];

        $this->workSheetIndex[$namedRange] = $this->client->getWorkSheetIndexByRangeName($namedRange);
		return $this->workSheetIndex[$namedRange];
	}

    /**
     * Проверка есть ли именованный диапазон
     * @param $namedRange
     *
     * @return bool
     */
	public function isNamedRangeExists($namedRange)
	{
		return true;//$this->client->isNamedRangeExists($namedRange);
	}

    /**
     * Проверка защищена ли ячейка
     * @param $pCoord
     *
     * @return array
     */
	public function isCellProtected($pCoord)
	{
		return $this->client->isCellProtected($pCoord);
	}

    /**
     * Управление динамическими методами
     *
     * @param  string $method
     * @param  array $parameters
     * @return mixed
     * @throws \Exception
     */
	public function __call($method, $parameters)
	{
		$start = microtime(true);

		$result = [];

		if ($method == 'namedRangeToArray')
		{
			$result = call_user_func_array([$this->client, $method], $parameters);
		}
		else
        {
            throw new \Exception("Unknown method [" . $method . "]");
        }

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
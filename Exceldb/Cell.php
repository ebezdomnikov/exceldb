<?php namespace App\Exceldb;

use App\Core\Traits\Cacheable;

class Cell
{
	use Cacheable;

	/**
	 * Connection
	 * @var Connection
	 */
	private $connection;

	/**
	 * @var \PHPExcel_Worksheet
	 */
	private $worksheet;

	private $client;
	/**
	 * Cell constructor.
	 *
	 * @param Connection          $connection
	 * @param WorksheetContract|PHPExcel_Worksheet $worksheet
	 */
	public function __construct(Connection $connection, $worksheet)
	{
		$this->connection = $connection;
		$this->worksheet  = $worksheet;
	}

	/**
	 * @param $key
	 *
	 * @return string
	 */
	public function cacheKey($key)
	{
		$info = pathinfo($this->connection->getFilename());
		$filename = $info['filename'];
		return $this->genCacheKey('CellValue_' . $filename . '_' . $key);
	}
	/**
	 * Сохраняем данные в ячейку
	 * @param $colRow
	 * @param $data
	 *
	 * @return \PHPExcel_Cell|\PHPExcel_Worksheet
	 */
	public function setCellValue($colRow, $data)
	{
		// сохраняем данные в кеше, по сути это данные которые пользователь ввел когда либо в этот файл
		// при загрузке из файла эти данные будут подставлятся прежде чем сосчитать новые данные
		// чтобы excel выполнил вычисления с учетом этих данных
		$this->cacheForever($this->cacheKey($colRow), $data);
		return $this->worksheet->setCellValue($colRow, $data);
	}

	public function setCellValueByColumnAndRow($pColumn = 0, $pRow = 1, $pValue = null, $returnCell = false)
    {
        return $this->worksheet->setCellValueByColumnAndRow($pColumn, $pRow, $pValue, $returnCell);
    }


	public function getUserCellValue($colRow)
	{
		if ($value = $this->getCache($this->cacheKey($colRow)))
		{
			return $value;
		}
		return null;
	}

	/**
	 * @param $colRow
	 *
	 * @return mixed|null
	 */
	public function getCellValue($colRow)
	{
		return $this->worksheet->getCellCalculatedValue($colRow);
	}


}
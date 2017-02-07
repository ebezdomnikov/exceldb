<?php namespace App\Exceldb;

use ResolveMap\Map;
use App\Core\Traits\Cacheable;
use Illuminate\Support\Collection;

class NamedRangeCollection
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
	/**
	 * @var string
	 */
	private $primaryKeyColumnName;

	/**
	 * @var
	 */
	private $map;

	/**
	 * @var Cell
	 */
	private $cell;
	/**
	 * NamedRangeCollection constructor.
	 * @param Connection $connection
	 * @param WorksheetContract|PHPExcel_Worksheet $worksheet
	 */
	public function __construct(Connection $connection, $worksheet)
	{
		$this->connection = $connection;
		$this->worksheet  = $worksheet;

		$this->cell = new Cell($connection, $worksheet);

		$this->primaryKeyColumnName = $this->connection->getPrimaryKeyColumnName();
	}

	/**
	 * @param string $pNamedRange
	 * @param null   $nullValue
	 * @param bool   $calculateFormulas
	 * @param bool   $formatData
	 * @param bool   $returnCellRef
	 *
	 * @return Collection
	 */
	private function _namedRangeToArray($pNamedRange = '', $nullValue = null, $calculateFormulas = true, $formatData = false, $returnCellRef = true)
	{
		if ($this->worksheet instanceof \PHPExcel_Worksheet)
		{
			\PHPExcel_Calculation::getInstance($this->worksheet->getParent())->clearCalculationCache();
		}

		$array = $this->worksheet->namedRangeToArray($pNamedRange, $nullValue, $calculateFormulas, $formatData, $returnCellRef);
		// Convert to Laravel collection
		$collection = $this->convertToCollection($array);
		// add primary key to collection and return as flat array
		return $this->addPrimaryKey($collection);
	}

	/**
	 * Custom namedRangeToArray
	 *
	 * @param string $pNamedRange
	 * @param null $nullValue
	 * @param bool $calculateFormulas
	 * @param bool $formatData
	 * @param bool $returnCellRef
	 *
	 * @return mixed
	 */
	public function namedRangeToArray($pNamedRange = '', $nullValue = null, $calculateFormulas = true, $formatData = false, $returnCellRef = true)
	{
		$collection = $this->_namedRangeToArray($pNamedRange, $nullValue, $calculateFormulas, $formatData, $returnCellRef);
        // Normalize data (filter, trim, replace etc..)
		$collection = $this->normalizeCollection($collection);
		// Make collection keys from first column
		$collection = $this->addFirstColumnAsKeys($collection);
		// return as Array
		return $collection->toArray();
	}

	/**
	 * @param $pNamedRange
	 * @internal Collection $collection
	 */
	public function getRangeMap($pNamedRange)
	{
        $collection = $this->_namedRangeToArray(
            $pNamedRange, null, false, false
            , true // нужно чтобы индексы столбцов были буквами!!!
        );
        $collection = $collection->slice(0, 1);
        // Normalize data (filter, trim, replace etc..)
        $collection = $this->normalizeCollection($collection);
        $collection = $collection->first();

        $collection = $collection->reject(function ($item, $key)
        {
            return $key == $this->connection->getPrimaryKeyColumnName();
        });
        //TODO: Исправить использование MAP в ExcelDB
        $this->map = new Map();
        $this->map->setExcelType($collection->toArray());

		return $this->map;
	}

	public function resolveByMap($name)
	{
		if ( empty($this->map))
			throw new \Exception("Map is empty");

		return $this->map->resolve($name);
	}

	private function getNamedRangeCoordinates($pNamedRange)
	{
		return $this->connection
			->getWorkBook()
			->getNamedRange($pNamedRange)
			->getRange();
	}

	/**
	 * Возвращает информацию о ячейках из диапазона данных
	 *
	 * @param string $pNamedRange
	 * @param null   $nullValue
	 * @param bool   $calculateFormulas
	 * @param bool   $formatData
	 * @param bool   $returnCellRef
	 *
	 * @return $this
	 */
	public function namedRangeWithStylesToArray($pNamedRange = '', $nullValue = null, $calculateFormulas = true, $formatData = true, $returnCellRef = false)
	{
		$collection = $this->_namedRangeToArray($pNamedRange, $nullValue, $calculateFormulas, $formatData, true);

		$colStartIndex = $this->getStartColumnIndex(
			$this->getNamedRangeCoordinates($pNamedRange)
		);

		$resultCollection = $collection->transform(function($item) use($colStartIndex)
		{
			$newItem = [];
			$colIndex = $colStartIndex;
			foreach ($item as $key => $value)
			{
				if ($key == $this->connection->getPrimaryKeyColumnName())
				{
					$newItem[$key] = $value;
				}
				else
				{
					$cellName = $this->stringFromColumnIndex($colIndex);
					$style    = $this->worksheet->getStyle($cellName);

					$newItem[$key] = [
						'style' => $style,
					    'value' => $value
					];
				}
				$colIndex++;
			}

			return $newItem;
		});

		return $resultCollection;
	}

	/**
	 * Возвращает букву столбца по его номеру
	 * @param int $pColumnIndex
	 *
	 * @return string
	 */
	private function stringFromColumnIndex($pColumnIndex = 0)
	{
		return \PHPExcel_Cell::stringFromColumnIndex($pColumnIndex);
	}
	/**
	 * @param $array
	 *
	 * @return Collection
	 */
	private function convertToCollection($array)
	{
		return collect($array);
	}

	/**
	 * @param Collection $collection
	 *
	 * @return Collection
	 */
	private function normalizeCollection(Collection $collection)
	{
		return $collection->map(function ($cellItems) {
			return collect($cellItems)->map(function ($cellValue) {
				$value = $cellValue;

				$value = trim($value);

				if (is_null($value)) {
					$value = '';
				}

				$value = preg_replace('/[\t\n]/', '', $value);

				return $value;
			});
		});
	}

	/**
	 * @param $pRange
	 *
	 * @return array
	 */
	private function getRangeBoundaries($pRange)
	{
		return \PHPExcel_Cell::rangeBoundaries(
			$pRange
		);
	}

	/**
	 * Start value for Primary key column
	 * @param $pRange
	 *
	 * @return int
	 * Output of getRangeBoundaries
	 * array:2 [
	0 => array:2 [
	0 => 1
	1 => "36"
	]
	1 => array:2 [
	0 => 10
	1 => "141"
	]
	]
	 */
	private function getMinRangePrimaryKey($pRange)
	{
		$result = 0;
		$rangeBoundaries = $this->getRangeBoundaries($pRange);
		if (
			count($rangeBoundaries) == 2
			&& count($rangeBoundaries[0]) == 2
			&& count($rangeBoundaries[1]) == 2
		) {
			$result = $rangeBoundaries[0][1];
		}

		return $result;
	}

	/**
	 * Индекс первого столбца в промежутке
	 * @param $pRange
	 *
	 * @return int
	 */
	private function getStartColumnIndex($pRange)
	{
		$result = 1;
		$rangeBoundaries = $this->getRangeBoundaries($pRange);
		if (
			count($rangeBoundaries) == 2
			&& count($rangeBoundaries[0]) == 2
			&& count($rangeBoundaries[1]) == 2
		) {
			$result = $rangeBoundaries[0][0];
		}

		return $result;
	}
	/**
	 * @param Collection $collection
	 *
	 * @return Collection
	 */
	private function addFirstColumnAsKeys(Collection $collection)
	{
		$collectionKeys = [];

		$firstColumnAsKeyName = $this->connection->isFirstRowAsColumnNames();
		$isNeedFirstRow       = $this->connection->isNeedFirstRow();

		if ($firstColumnAsKeyName) {
			$collectionKeys = $collection->first()->toArray();
			if ( ! $isNeedFirstRow)
				$collection = $collection->slice(1);
		} else {
			for ($i=0; $i< count($collection->first()->toArray()); $i++) {
				$collectionKeys[] = "COL" . $i;
			}
		}

		$collection = $collection->map(function ($flatItems) use ($collectionKeys) {
			$newArray = [];
			foreach ($collectionKeys as $index => $key) {
				if ($index == $this->connection->getPrimaryKeyColumnName())
				{
					$newArray[$index] = $flatItems[$index];
				}
				else
				{
					$newArray[$key] = $flatItems[$index];
				}
			}
			return $newArray;
		});

		return $collection;
	}

	/**
	 *
	 * @param Collection $collection
	 *
	 * @return Collection
	 */
	private function addPrimaryKey(Collection $collection)
	{
        $firstRow = collect($collection->first());
        // карта id < - > column letter
		$this->map = $firstRow->keys();

		$collection = $collection->map(function ($item, $key) {
			$item[$this->primaryKeyColumnName] = $key;
			return $item;
		});

		return $collection;
	}
}

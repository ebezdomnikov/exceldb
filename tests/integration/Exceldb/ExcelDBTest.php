<?php

use App\Exceldb\Connection;

class ExcelDBTest extends TestCase
{

    /**
     *
     * @return string
     */
    public function getTestFileFullPath()
    {
        return __DIR__ . '/26731-26734.xlsx';
    }

    /**
     *
     * @return array
     */
    public function getConfigWithDataBase()
    {
        return [
            'filename' => $this->getTestFileFullPath()
        ];
    }

    /**
     *
     * @return array
     */
    public function getConfigWithOutDataBase()
    {
        return [
            'filename' => ''
        ];
    }


    /**
     * @param $config
     *
     * @return Connection
     */
    public function connectToExcel($config)
    {
        return new Connection($config);
    }
}



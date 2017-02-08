<?php

class ConnectionTest extends ExcelDBTest
{
    /** @test */
    public function is_returns_phpexcel_instance_with_database()
    {
        $connection = $this->connectToExcel(
            $this->getConfigWithDataBase()
        );
        $this->assertTrue($connection instanceof App\Exceldb\Connection);
        $this->assertTrue($connection->getWorkBook() instanceof Maatwebsite\Excel\Readers\LaravelExcelReader);
    }
    /** @test */
    public function is_returns_phpexcel_instance_without_database()
    {
        $connection = $this->connectToExcel(
            $this->getConfigWithOutDataBase()
        );
        $this->assertTrue($connection instanceof App\Exceldb\Connection);
        $this->assertTrue($connection->getWorkBook() == null);
    }
    /** @test */
    public function is_select_filename()
    {
        $connection = $this->connectToExcel(
            $this->getConfigWithOutDataBase()
        );

        $filename = $connection->selectFilename($this->getTestFileFullPath())->getFileName();

        $this->assertTrue($filename == $this->getTestFileFullPath());
    }
    /** @test */
    public function it_can_select_sheet()
    {
        $connection = $this->connectToExcel(
            $this->getConfigWithOutDataBase()
        );

        $sheet = $connection->selectFilename($this->getTestFileFullPath())
            ->open()
            ->selectWorkSheet(0)
            ->getSheet();

        $this->assertTrue($sheet instanceof PHPExcel_Worksheet);
    }
    /** @test */
    public function is_sheet_containts_tovary_named_range()
    {
        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->open(true)
            ->table('товары')
            ->withOutFormat()// без формата
            ->withFirstRow() // с первой строкой
            ->get();

        $this->assertEquals(106, count($result));

    }

    /** @test */
    public function is_can_proccess_protected_cells()
    {
        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->open(false)
            ->table('товары')
            ->withOutFormat()// без формата
            ->withFirstRow() // с первой строкой
            ->isReadOnly('41','Заказ штук');

        $this->assertFalse($result);

    }

    /** @test */
    public function is_sheet_containts_bonus_named_range()
    {
        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->open(true)
            ->table('бонус')
            ->withOutFormat()// без формата
            ->withFirstRow() // с первой строкой
            ->get();

        $this->assertEquals(7, count($result));

    }

    /** @test */
    public function is_sheet_containts_packege_named_range()
    {
        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->open(true)
            ->table('пакеты')
            ->withOutFormat()// без формата
            ->withFirstRow() // с первой строкой
            ->get();

        $this->assertEquals(1, count($result));

    }

    /** @test */
    public function is_sheet_containts_calculated_named_range()
    {
        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->open(true)
            ->table('условия')
            ->withOutFormat()// без формата
            ->withFirstRow() // с первой строкой
            ->get();

        $this->assertEquals(7, count($result));
    }

    /** @test */
    public function is_sheet_containts_status_named_range()
    {
        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->open(true)
            ->table('статус')
            ->withOutFormat()// без формата
            ->withFirstRow() // с первой строкой
            ->get();

        $this->assertEquals(1, count($result));
    }
}



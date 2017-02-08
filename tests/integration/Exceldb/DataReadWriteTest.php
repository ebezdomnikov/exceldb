<?php

class DataReadWriteTest extends ExcelDBTest
{
    /** @test */
    public function can_write_to_remote_cell()
    {
        $value = rand(10,50);

        DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->openRemote() // удаленный эксель
            ->table('товары')
            ->where('id',141)
            ->update(["Заказ штук" => $value]);

        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->openRemote() // удаленный эксель
            ->table('товары')
            ->get();

        $this->assertEquals(105,count($result));
        $this->assertEquals($value,$result[141]["Заказ штук"]);
    }
    /** @test */
    public function is_keys_as_first_row_names()
    {
        $result = DB::connection('exceldb')
            ->selectFileName($this->getTestFileFullPath())
            ->openRemote() // удаленный эксель
            ->table('товары')
            ->get();

        $collection = collect($result)->first();
        $keysFromFile = collect($collection)->keys()->toArray();

        $checkKeys = [
                0 => "Артикул",
                1 => "Наименование",
                2 => "Заказ штук",
                3 => "Объем мл",
                4 => "Шт/уп",
                5 => "Цена в рублях без НДС",
                6 => "Цена в рублях c НДС",
                7 => "Сумма заказа без НДС",
                8 => "Сумма заказа с НДС",
                9 => "__ID"
        ];
        $this->assertArraySubset($checkKeys,$keysFromFile);
    }

}



<?php namespace App\Exceldb\Schema;

use app\Exceldb\Connection;

class Builder extends \Illuminate\Database\Schema\Builder
{
    /**
     * Builder constructor.
     * @param Connection $connection
     * @throws \Exception
     */
    public function __construct(Connection $connection)
    {
        throw new \Exception("Method " . __METHOD__ . " of class " . __CLASS__ . " not supplied.");
    }
}

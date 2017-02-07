<?php namespace App\Exceldb\Schema;

use Closure;
use Illuminate\Database\Connection;

class Blueprint extends \Illuminate\Database\Schema\Blueprint
{
    /**
     * Blueprint constructor.
     * @param Connection $connection
     * @param Closure|null $collection
     * @throws \Exception
     */
    public function __construct(Connection $connection, $collection)
    {
        throw new \Exception("Method " . __METHOD__ . " of class " . __CLASS__ . " not supplied.");
    }
}

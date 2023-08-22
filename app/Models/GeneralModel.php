<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralModel extends Model
{
    use HasFactory;

    public function __construct($table = "")
    {
        if (isValidValue($table)) {
            $this->table = $table;
        } else {
            $this->table = getCurrentStructure('table');
        }
    }
}

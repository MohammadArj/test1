<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class json_storage extends Model
{

    use HasFactory;
    public function __construct()
    {
        $this->table= "json_storage";
    }
}

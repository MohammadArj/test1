<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class file_storage extends Model
{

    use HasFactory;
    public function __construct()
    {
        $this->table= "file_storage";
    }
}

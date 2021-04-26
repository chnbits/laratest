<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Oper_record extends Model
{
    use HasFactory;

    public static function index($parms)
    {
        DB::table('oper_record')->insert($parms);
    }
}

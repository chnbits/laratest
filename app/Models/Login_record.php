<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Login_record extends Model
{
    use HasFactory;
    public static function index($parm)
    {
        DB::table('login_record')->insert($parm);
    }
}

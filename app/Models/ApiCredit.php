<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiCredit extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'api_credits';
}

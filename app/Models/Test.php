<?php

namespace app\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
	protected $connection = 'plurk';
    protected $table = 'test';
}
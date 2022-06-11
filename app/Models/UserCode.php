<?php

namespace App\Models;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCode extends Model
{
    use HasFactory;

    public $table = "user_codes";

    protected $fillable = [
        'phone',
        'code'
    ];

    public function isExpired($min = 5){
        $date1 = Carbon::createFromFormat('Y-m-d H:i:s', $this->created_at);
        $date2 = $date1->addMinutes($min);

        $now = Carbon::now()->format('Y-m-d H:i:s');
        $now = Carbon::createFromFormat('Y-m-d H:i:s',$now);

        $result = $now->gte($date2);
        return $result;
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuranAya extends Model
{

    protected $table = 'quran_ayat';
    public $timestamps = false;

    public function sora()
    {
        return $this->belongsTo(QuranSora::class, 'sora_id');
    }
}

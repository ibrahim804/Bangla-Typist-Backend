<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Paragraph;

class Game extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function paragraphs()
    {
    	return $this->hasMany(Paragraph::class, 'game_id'); // Own's Id
    }

    public function plays()
    {
        return $this->hasMany(Play::class, 'game_id'); 
    }
}
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Game;

class Paragraph extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function game()
    {
    	return $this->belongsTo(Game::class);
    }

    public function plays()
    {
        return $this->hasMany(Play::class, 'paragraph_id');
    }
}
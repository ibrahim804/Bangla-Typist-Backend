<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Game;
use App\Paragraph;

class Play extends Model
{
    protected $guarded = [];
    public $timestamps = false;

    public function user()
    {
    	return $this->belongsTo(User::class);
    }

    public function game()
    {
    	return $this->belongsTo(Game::class);
    }

    public function paragraph()
    {
    	return $this->belongsTo(Paragraph::class);
    }
}


/*
'user_id' => 'required',
            'game_id' => 'required',
            //'paragraph_id' => 'required',
            //'started_at' => 'required',
            //'finished_at' => 'required'
*/
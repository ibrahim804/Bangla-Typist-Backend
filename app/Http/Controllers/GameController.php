<?php

namespace App\Http\Controllers;

use App\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{

    use CustomsErrorsTrait;

    public function __construct()
    {
        $this->middleware('auth:api')->only(['index', 'store']);
        // $this->middleware('auth')->except(['update']);
    }

    public function index()
    {
        $games = Game::all();

        if(is_null($games))
        {
            return $this->getErrorMessage('Not found any game.');
        }
        
        return 
        [
            [
                'status' => 'OK',
                'game' => response()->json($games)
            ]
        ];
    }

    public function store(Request $request)
    {
        
        $validate_attributes = $this->validateGame();
        $game = Game::create($validate_attributes);

        return
        [
            [
                'status' => 'OK',
                'name' => $game->name,
                'description' => $game->description
            ]
        ];
    }

    private function validateGame()
    {
        return request()->validate([                           // For DATABASE Validation
            'name' => ['required', 'min:1', 'max:20'],
            'description'  => ['min:10'],
        ]);
    }
}
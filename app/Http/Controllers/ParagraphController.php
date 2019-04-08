<?php

namespace App\Http\Controllers;

use App\User;
use App\Paragraph;
use App\Game;
use Illuminate\Http\Request;
//use Carbon\Carbon;

class ParagraphController extends Controller
{
 
    use CustomsErrorsTrait;

    public function __construct()
    {
        $this->middleware('auth:api')->only(['show', 'store']);
        // $this->middleware('auth')->except(['index']);
    }

    public function store(Request $request)
    {

        //Carbon::createFromFormat('d-m-Y', $request->input('activation_date'))->format('Y-m-d') Converts first format to second format

        $user = auth()->user();

        if($user->isAdmin($user->id) == 'false')
        {
            return $this->getErrorMessage('ACCESS DENIED for normal users');
        }

        $validate_attributes = $this->validateParagraph();
        $paragraph = Paragraph::create($validate_attributes);
        
        return
        [
            [
                'status' => 'OK',
                'game_id' => $paragraph->game_id,
                'activation_date' => $paragraph->activation_date,
                'text' => $paragraph->text
            ]
        ];
    }

    public function show($game_id)               // game_id 
    {
        $single_paragraph = Paragraph::where([

            ['game_id', $game_id],
            ['activation_date' , date('Y-m-d')]  // '2019-02-21'
            
        ])->first();

        if(is_null($single_paragraph))
        {
            return $this->getErrorMessage('Can not find any text');
        }

        return
        [
            [
                'status' => 'OK',
                'text' => $single_paragraph->text
            ]
        ];
    }

    private function validateParagraph()
    {
        return request()->validate([                           // For DATABASE Validation
            'game_id' => 'required',
            'activation_date'  => 'required',
            'text' => 'required'
        ]);
    }

}



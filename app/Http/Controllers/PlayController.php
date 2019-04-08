<?php

namespace App\Http\Controllers;

use App\Play;
use App\Paragraph;
use App\Game;
use App\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
// use App\Quotation;

class PlayController extends Controller
{

    use CustomsErrorsTrait;

    public function __construct()
    {
        $this->middleware('auth:api')->only(['store', 'show']);
        // $this->middleware('auth')->except(['index']);
    }

    public function store(Request $request)
    {
        
        $validate_attributes = $this->validatePlay();

        $game_date_paragraph = Paragraph::where([

            ['game_id', $request->input('game_id')],
            ['activation_date', date('Y-m-d')]

        ])->first(); 
        
        if(is_null($game_date_paragraph))
        {
            return $this->getErrorMessage('game_id and activation_date combination is not correct');
        }

        // $validate_attributes['finished_at'] = date("Y-m-d H:i:s", strtotime('+6 hours +1 minutes')); hudai

        $validate_attributes['user_id'] = auth()->user()->id;
        $validate_attributes['paragraph_id'] = $game_date_paragraph->id; 
        $validate_attributes['started_at'] = $this->getFormattedTimeFromUnixTime($request->input('started_at'));

        $decision = $this->isInputGivenBasedOnGameType($request);
        $status = json_decode(json_encode($decision[0]))->status;

        if($status == 'FAILED') return $decision;

        $validate_attributes['finished_at'] = ($request->input('game_id') <= 3) ? 
            $this->getFinishedTimeFromStartedTime($validate_attributes['started_at'], $request->input('game_id')) :
            $this->getFormattedTimeFromUnixTime($request->input('finished_at'));

        $validate_attributes['score'] = ($request->input('game_id') <= 3) ? $request->input('score') : null;

        $play_record = Play::create($validate_attributes);

        return 
        [
            [
                'status' => 'OK',
                'play_record' => $play_record
            ] 
        ];
    }

    private function validatePlay()
    {
        return request()->validate([            

            'game_id' => 'required',
            'started_at' => 'required',

        ]);
    }

    private function isInputGivenBasedOnGameType($request)
    { 

        if($request->input('game_id') <= 3 and !$request->filled('score'))
        {
            return $this->getErrorMessage('score must be filled out.');
        }

        if($request->input('game_id') > 3 and !$request->filled('finished_at'))
        {
            return $this->getErrorMessage('finished_at must be filled out');
        }

        return 
        [
            [
                'status' => 'OK',
            ]
        ];
    }

    public function show($game_id, $query)
    { 

        if(!(
                $query == 'best'
            or  $query == 'latest'
            or  $query == 'top5'
        )){
            return $this->getErrorMessage('Route is not well defined.');
        }

        if($query == 'top5') return $this->top5($game_id);

        $record = $this->getIndivisualRecord($game_id, $query);

        if(is_null($record))
        {
            return $this->getErrorMessage('Can not find any record.');
        }

        $time = ($game_id <= 3) ? null : $this->getFormattedTimeDifference($record->started_at, $record->finished_at);

        return
        [
            [
                'status' => 'OK',
                'name' => auth()->user()->name,
                'score' => $record->score,
                'time' => $time,
                'position' => $this->getOverAllPosition($game_id, ($game_id <= 3) ? $record->score : $time),
            ]
        ];
    }

    private function getIndivisualRecord($game_id, $query)
    {

        return ($game_id <= 3) ? 

            Play::where([
        
                ['user_id', auth()->user()->id],
                ['game_id', $game_id]
        
            ])->orderBy( ($query=='best') ? 'score' : 'finished_at' , 'desc')->first()
        
            :

            Play::where([
        
                ['user_id', auth()->user()->id],
                ['game_id', $game_id]
        
            ])->orderByRaw( ($query == 'best') ? '(finished_at - started_at) asc' : '(finished_at) desc' )->first();
    }

    private function top5($game_id)
    {

        $query = ($game_id <= 3) ?
            "select user_id, score, users.name from (select user_id, max(score) as score from plays where game_id = $game_id group by user_id) as plays join users on users.id = plays.user_id order by score desc limit 5" :
            "select user_id, time, users.name from (select user_id, min(finished_at-started_at) as time from plays where game_id = $game_id group by user_id) as plays join users on users.id = plays.user_id order by time asc limit 5";

        $records = DB::select($query);

        if(!count($records))
        {
            return $this->getErrorMessage('Can not find any record, let alone top 5. lol.');
        }

        $json_formatted = [];

        for ($i=0; $i<count($records); $i++)
        {
            $json_formatted [] = [

                'status' => 'OK',
                'name' => $records[$i]->name,
                'score' => ($game_id <= 3) ? $records[$i]->score : null, 
                'time' => ($game_id <= 3) ? null : $records[$i]->time,
                'position' => $i+1,
            ];
        }

        return $json_formatted;
    }

    private function getOverAllPosition($game_id, $scoreOrTime)
    {
        $position = ($game_id <=3 ) ?

            Play::where([
            
                ['game_id', $game_id],
                ['score', '>', $scoreOrTime],
        
            ])->get()->count()

            :

            Play::where('game_id', $game_id)

                ->whereRaw("(finished_at - started_at) < '$scoreOrTime'")               // Finally works! Alhamdu-lillah :) 
                ->get()
                ->count();

        return 1+$position;
    }

    private function getFormattedResult($records)
    {
        $formatted_map = array();

        foreach($records as $record)
        {
            array_push($formatted_map, $record->score);
        }

        return $formatted_map;
    }

    private function getFormattedTimeFromUnixTime($time)
    {
        $time = Carbon::createFromTimestamp($time/1000)->toDateTimeString();
        $time = Carbon::parse($time);
        $time = $time->addHours(+6);

        return $time;
    }

    private function getFinishedTimeFromStartedTime($time, $minutes)
    {
        $finished_time = clone $time;
        return $finished_time->addMinutes(+$minutes);
    }    

    private function getFormattedTimeDifference($start_time, $finish_time)
    {                                                                                           
        $duration = $this->getTimeDifferenceInSeconds($start_time, $finish_time);
        return gmdate('H:i:s', $duration);
    }

    private function getTimeDifferenceInSeconds($start_time, $finish_time)
    {
        $start_time = Carbon::parse($start_time);
        $finish_time = Carbon::parse($finish_time);

        return $finish_time->diffInSeconds($start_time);
    }

}


// Hello People
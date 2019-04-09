<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\Controllers\CustomsErrorsTrait;

class Cors
{

    use CustomsErrorsTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {                                                                                                                  
        $allowed = array('https://bangla-typist.herokuapp.com');  

        if(isset($_SERVER['HTTP_ORIGIN']) && in_array($_SERVER['HTTP_ORIGIN'], $allowed)){
            return $next($request)
                ->header('Access-Control-Allow-Origin', $_SERVER['HTTP_ORIGIN']);
                // ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH')
                // // ->header('Access-Control-Allow-Credentials', 'true')
                // // ->header('Access-Control-Max-Age', '10000')
                // ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With');
        }

        // return response()->json($this->getErrorMessage('Request from an unexpected origin.'));

        // return $next($request);

        // It is final
        // return $next($request)->header('Access-Control-Allow-Origin', '*');

        // return $next($request)
        //     ->header('Access-Control-Allow-Origin', '*')
        //     ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH')
        //     ->header('Access-Control-Allow-Credentials', 'true')
        //     ->header('Access-Control-Max-Age', '10000')
        //     ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, Accept, X-Requested-With');

    }
}
// hello
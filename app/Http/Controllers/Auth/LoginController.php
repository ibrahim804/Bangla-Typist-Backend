<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use Socialite;
use App\User;
use App\Http\Controllers\CustomsErrorsTrait;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, CustomsErrorsTrait;
    private $client;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('guest')->except('logout');
        $this->client = new Client();
        $this->middleware('auth:api')->except(['fbSignUpOrLogIn']);
    }

    public function redirectToProvider($provider)
    {
        // return Socialite::driver($provider)->redirect(); // Generate Pop Up for login
    }

    public function fbSignUpOrLogIn()
    {
        // $user = Socialite::driver($provider)->user();

        $validate_attributes = $this->validateAttributes();

        if($this->validateUser($validate_attributes) == 'false')
        {
            return $this->getErrorMessage('Invalid Facebook User.');
        }

        $authUser = $this->findOrCreateUser($validate_attributes, 'facebook');

        Auth::login($authUser, true);
        $success['token'] = $authUser->createToken(config('app.name'))->accessToken;

        return
        [
            [
                'status' => 'OK',
                'name' => $authUser->name,
                'token' => $success['token'],
            ]
        ];
    }

    private function findOrCreateUser($validate_attributes, $provider)
    {
        $authUser = User::where('provider_id', $validate_attributes['id'])->first();

        if ($authUser) {
            return $authUser;
        }                                                      

        $validate_attributes['provider_id'] = $validate_attributes['id'];
        $validate_attributes['provider'] = $provider;

        $authUser = User::create($validate_attributes);
        
        $authUser->isFbUser = true;
        $authUser->save();

        return $authUser;
    }

    private function validateAttributes()
    {
        return request()->validate([
            'id' => 'required',
            'facebook_token' => 'required',
            'name' => 'required',
        ]);
    }

    private function validateUser($validate_attributes)
    {   
        $response = $this->client->get('https://graph.facebook.com/debug_token?input_token='.$validate_attributes['facebook_token'].'&access_token='.config('app.token'));
        $json_object = json_decode($response->getBody(), true)['data'];
        
        return ($json_object['is_valid'] 
            and $json_object['user_id'] == $validate_attributes['id']
            and $json_object['app_id'] == config('app.id'))
            
            ? 'true' : 'false'; 
    }
} 

// Hello People
<?php

namespace App\Http\Controllers\Auth;

use Auth;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
// use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

class RegisterController extends Controller
{

    // use RegistersUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        // $this->middleware('guest');
    }

        /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
            return Validator::make($data,[
                'username'  => ['required', 'string', 'max:255'],
                'email'     => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password'  => ['required', 'string', 'min:8','confirmed'],
            ]);
    }

        /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        return User::create([
            'name'      => $data['name'],
            'username'  => $data['username'],
            'password'  => Hash::make($data['password']),
            'user_type' => 'none',
            'state' => 0,
        ]);
       
    }
    //

    public function customRegisterUser(Request $request)
    {
        // $responseRecaptcha=GoogleReCaptchaV3::verifyResponse($request->input('g-recaptcha-response'),$request->getClientIp())->toArray();
        //dd($responseRecaptcha);
        // if($responseRecaptcha['success']==true && $responseRecaptcha['score']>= 0.6){
            $validate = \Validator::make($request->all(),[
                'firstname'  => ['required', 'string', 'max:50'],
                'lastname'  => ['required', 'string', 'max:50'],
                'username'  => ['required', 'string', 'max:50'],
                'email'     => ['required', 'string', 'email', 'max:50'],
                'password'  => ['required', 'string', 'min:8'],
            ]);
            if( $validate->fails()){
                return redirect()
                ->back()
                ->withErrors($validate);
            }
            $result = User::where("email", $request->email)->get()->count();
            if($result == 0){
                $user_create = User::create([
                    'first_name' => $request->firstname,
                    'last_name' => $request->lastname,
                    'username'  => $request->username,
                    'email'     => $request->email,
                    'password'   => Hash::make($request->password),
                    'user_type' => 'none',
                    'state' => 0,
                ]);
            }else{
                return redirect('/register')->with('error', 'This email is already existed.');
            }
            return redirect('/register')->with('success', 'Successfully registered');
        // }else{
            // return redirect()->route('register')->with('error','ReCaptcha Error');
        // }
    }
}

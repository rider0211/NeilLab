<?php

namespace App\Http\Controllers\Auth;

use Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;
    
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('guest')->except('logout');
    }
    public function login(Request $request)
    { 
        // $responseRecaptcha=GoogleReCaptchaV3::verifyResponse($request->input('g-recaptcha-response'),$request->getClientIp())->toArray();
        //dd($responseRecaptcha);
        // if($responseRecaptcha['success']==true && $responseRecaptcha['score']>= 0.6){
            $inputVal = $request->all();
            $validate = \Validator::make($request->all(),[
                'email'     => ['required', 'string', 'email', 'max:50'],
                'password'  => ['required', 'string', 'min:8'],
            ]);
            if( $validate->fails()){
                return redirect()
                ->back()
                ->withErrors($validate);
            }
            if(auth()->attempt(array('email' => $inputVal['email'], 'password' => $inputVal['password']))){
                if (auth()->user()->user_type == "admin")
                    return redirect('/admin/dashboard');
                else if (auth()->user()->user_type == "client" && auth()->user()->state == 1 ) 
                    return redirect()->route('doctor.home');
                else if (auth()->user()->user_type == "none" && auth()->user()->state == 1 ) 
                    return redirect('/dashboard');
                else if (auth()->user()->user_type == "reception" && auth()->user()->state == 1 ) 
                    return redirect()->route('reception.home');
                else if (auth()->user()->state == 0 ){
                    Auth::logout();
                    return redirect('/login')
                    ->with('error','Account Still suspensed.');
                }
            }else{
                return redirect('/login')->with('error', __('locale.wrong_email_and_password'));
            }
        // }else{
        //     return redirect()->route('login')->with('error','ReCaptcha Error');
        // }     
    }

    public function logout(Request $request) {
        Auth::logout();
        return redirect('/login');
    }

}

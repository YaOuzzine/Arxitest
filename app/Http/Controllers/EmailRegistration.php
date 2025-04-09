<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class EmailRegistration extends Controller
{
    public function registerEmail(Request $request){

        $request->validate([
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users']
        ]);

        $email = $request->email;

        $code = random_int(100000, 999999);

        $request->session()->put('email', $email);
        $request->sessions()->put('code', $code);

        Mail::to($email)->send(new EmailVerification($code));

        return redirect()->route('auth.email-verification');
    }

    public function showEmailVerification()
    {
        return view('auth.email-verification');
    }

    public function verifyEmail(Request $request)
    {
        // $request->validate(
        //     [
        //         'email' => ['required', 'string', 'email', 'max:255', 'unique:users']
        //     ]
        // );

        return redirect()->route('auth.registration-completion');
    }

    public function showRegistrationCompletion(){
        return view('auth.complete-registration');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Utils;
use App\Traits\ApiResponser;
use Carbon\Carbon;
use Encore\Admin\Auth\Database\Administrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Tymon\JWTAuth\Facades\JWTAuth;

class ApiAuthController extends Controller
{

    use ApiResponser;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('auth:api')->except([
            'login',
            'register',
            'send-code'
        ]);
    }


    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $query = auth('api')->user();
        return $this->success($query, $message = "Profile details", 200);
    }



    public function sendCode(Request $r)
    {


        if ($r->email == null) {
            return $this->error('Email or Phone number is required.');
        }

        $u = Administrator::where('email', $r->email)
            ->orWhere('username', $r->email)
            ->first();

        if ($u == null) {
            return $this->error('Account with  provided email not found.');
        }


        $success = false;
        $message = "";
        try {
            $u->sendPasswordResetCode();
            $message = "We have sent a secret code to your email address {$u->email}. Check your email  inbox or spam and use that code to reset your password.";
            $success = true;
        } catch (\Throwable $th) {
            $message = $th;
            $success = false;
        }

        if ($success) {
            return $this->success($message, $message);
        }

        return $this->error($message);
    }


    public function changePassword(Request $r)
    {


        if ($r->email == null) {
            return $this->error('Email is required.');
        }
        if ($r->code == null) {
            return $this->error('Code is required.');
        }
        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $u = Administrator::where('email', $r->email)
            ->orWhere('username', $r->email)
            ->first();

        if ($u == null) {
            return $this->error('Account with  provided email not found.');
        }

        if ($u->code != $r->code) {
            return $this->error('Verification code did not match. Copy the code we sent to your email correctly and try again.');
        }

        $u->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        $u->save();

        return $this->success($u, 'Password changed successfully. You can now login with your new password.');
    }
}

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

        /* $token = auth('api')->attempt([
            'username' => 'admin',
            'password' => 'admin',
        ]);
        die($token); */
        $this->middleware('auth:api', ['except' => [
            'login',
            'register',
            'change-password',
            'send-code'
        ]]);
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


    public function login(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Email or Phone number is required.');
        }
        $phone_number = Utils::prepare_phone_number($r->phone_number);


        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $u = Administrator::where('email', $r->phone_number)
            ->orWhere('username', $r->phone_number)
            ->first();

        if ($u == null) {
            $u = Administrator::where('phone_number_1', $phone_number)
                ->first();
        }

        if ($u == null) {
            return $this->error('User account not found.');
        }

        //auth('api')->factory()->setTTL(Carbon::now()->addMonth(12)->timestamp);

        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'username' => $r->phone_number,
            'password' => trim($r->password),
        ]);




        if ($token == null) {
            $token = auth('api')->attempt([
                'email' => $r->phone_number,
                'password' => trim($r->password),
            ]);
        }

        if ($token == null) {
            $token = auth('api')->attempt([
                'phone_number_1' => $phone_number,
                'password' => trim($r->password),
            ]);
        }


        if ($token == null) {
            return $this->error('Wrong credentials.');
        }
        $u->token = $token;

        return $this->success($u, 'Logged in successfully.');
    }

    public function register(Request $r)
    {
        if ($r->phone_number == null) {
            return $this->error('Phone number is required.');
        }

        $phone_number = Utils::prepare_phone_number(trim($r->phone_number));


        if (!Utils::phone_number_is_valid($phone_number)) {
            return $this->error('Invalid phone number. ' . $phone_number);
        }

        if ($r->first_name == null) {
            return $this->error('First name is required.');
        }

        if ($r->last_name == null) {
            return $this->error('Last name is required.');
        }

        if ($r->password == null) {
            return $this->error('Password is required.');
        }

        $u = Administrator::where('phone_number_1', $phone_number)
            ->orWhere('username', $phone_number)->first();
        if ($u != null) {
            return $this->error('User with same phone number already exists.');
        }
        $user = new Administrator();
        $user->phone_number_1 = $phone_number;
        $user->username = $phone_number;
        $user->username = $phone_number;
        $user->name = $r->first_name . " " . $user->last_name;
        $user->first_name = $r->first_name;
        $user->last_name = $r->last_name;
        $user->password = password_hash(trim($r->password), PASSWORD_DEFAULT);
        if (!$user->save()) {
            return $this->error('Failed to create account. Please try again.');
        }

        $new_user = Administrator::find($user->id);
        if ($new_user == null) {
            return $this->error('Account created successfully but failed to log you in.');
        }
        Config::set('jwt.ttl', 60 * 24 * 30 * 365);

        $token = auth('api')->attempt([
            'username' => $phone_number,
            'password' => trim($r->password),
        ]);

        $new_user->token = $token;
        return $this->success($new_user, 'Account created successfully.');
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

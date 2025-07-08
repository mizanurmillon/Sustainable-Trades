<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordOtp;
use App\Mail\RegistationOtp;
use App\Models\EmailOtp;
use App\Models\User;
use App\Traits\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends Controller {

    use ApiResponse;

    /**
     * Send a Forgot Password (OTP) to the user via email.
     *
     * @param  \App\Models\User  $user
     * @return void
     */

    private function sendOtp($user) {
        $code = rand(1000, 9999);

        // Store verification code in the database
        $verification = EmailOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'verification_code' => $code,
                'expires_at'        => Carbon::now()->addMinutes(1),
            ]
        );

        Mail::to($user->email)->send(new ForgotPasswordOtp($user, $code));
    }

    /**
     * Send a Register (OTP) to the user via email.
     *
     * @param  \App\Models\User  $user
     * @return void
     */

     private function verifyOTP($user) {
        $code = rand(1000, 9999);

        // Store verification code in the database
        $verification = EmailOtp::updateOrCreate(
            ['user_id' => $user->id],
            [
                'verification_code' => $code,
                'expires_at' => Carbon::now()->addMinutes(15)
            ]
        );

        Mail::to($user->email)->send(new RegistationOtp($user, $code));
    }

    /**
     * User Login
     *
     * @param  \Illuminate\Http\Request  $request  The HTTP request with the Login query.
     * @return \Illuminate\Http\JsonResponse  JSON response with success or error.
     */

     public function userLogin(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->error([], $validator->errors()->first(), 422);
        }

        $credentials = $request->only('email', 'password');

        $userData = User::where('email', $request->email)->first();

        if ($userData && Hash::check($request->password, $userData->password)) {
            if($userData->email_verified_at == null) {

                $this->verifyOTP($userData);

                $userData->setAttribute('token', null);

            } else {

                if (!$token = JWTAuth::attempt($credentials)) {
                    return $this->error([], 'Invalid credentials', 401);
                }

                $userData = auth()->user();

                $userData->setAttribute('token', $token);
            }
        } else {
            return $this->error([], 'Invalid credentials', 401);
        }

        return $this->success($userData, 'User authenticated successfully', 200);
    }

    /**
     * Verify Email to send otp
     *
     * @param  \Illuminate\Http\Request  $request .
     * @return \Illuminate\Http\JsonResponse  JSON response with success or error.
     */

    public function emailVerify(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), "Validation Error", 422);
        }

        try {
            // Retrieve the user by email
            $user = User::where('email', $request->input('email'))->first();

            $this->sendOtp($user);

            return $this->success($user, 'OTP has been sent successfully.', 200);
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Resend an OTP to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function otpResend(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), "Validation Error", 422);
        }

        try {
            // Retrieve the user by email
            $user = User::where('email', $request->input('email'))->first();

            $this->sendOtp($user);

            return $this->success($user, 'OTP has been sent successfully.', 200);
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Verify the OTP sent to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function otpVerify(Request $request) {

        // Validate the request
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|numeric|digits:4',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), "Validation Error", 422);
        }

        try {
            // Retrieve the user by email
            $user = User::where('email', $request->input('email'))->first();

            $verification = EmailOtp::where('user_id', $user->id)
                ->where('verification_code', $request->input('otp'))
                ->where('expires_at', '>', Carbon::now())
                ->first();

            if ($verification) {

                $user->email_verified_at = Carbon::now();
                $user->save();

                $verification->delete();

                return $this->success($user, 'OTP Verified Successfully', 200);
            } else {

                return $this->error([], 'Invalid or expired OTP', 400);
            }
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }

    /**
     * Password Reset to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email|exists:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
        ], [
            'password.min' => 'The password must be at least 8 characters long.',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), "Validation Error", 422);
        }

        try {
            // Retrieve the user by email
            $user = User::where('email', $request->input('email'))->first();

            $user->password = Hash::make($request->input('password'));
            $user->save();

            return $this->success($user, 'Password Reset successfully.', 200);
        } catch (\Exception $e) {
            return $this->error([], $e->getMessage(), 500);
        }
    }
}

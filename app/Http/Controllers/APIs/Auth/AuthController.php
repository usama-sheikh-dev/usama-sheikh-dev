<?php

namespace App\Http\Controllers\APIs\Auth;

use App\Http\Controllers\APIs\BaseController;
use App\Http\Controllers\APIs\Notifications\NotificationController;
use App\Http\Resources\AuthenticationResource;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends BaseController
{
    /**
     * Login api for Admin, VIP, Normal User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            Log::info('Login user validation error', ['Error' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            if($user->status != 'Active'){
                Log::info('Unable to login', ['Email' => $request->email]);
                return $this->sendError('This account is currently blocked. Please get in touch with support for further assistance.', ['error' => 'Account is currently blocked.'], 401);
            }
            if ($user->hasRole(['Admin', 'VIP', 'Normal'])) {

                $user['token'] = $user->createToken('auth-token')->accessToken;

                Log::info('User login successfully', ['Email' => $request->email]);
                return $this->sendResponse(new AuthenticationResource($user), 'User login successfully.');
            } else {
                Log::info('Unable to login', ['Email' => $request->email]);
                return $this->sendError('Unable to login the user, please check your credentials.', ['error' => 'Unauthorised'], 401);
            }
        } else {
            Log::info('Unable to login the user', ['Email' => $request->email]);
            return $this->sendError('Unable to login the user, please check your credentials.', ['error' => 'Unauthorised'], 401);
        }
    }

    /**
     * Register api for User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|min:8|same:password',
            'user_type' => ['required', 'in:VIP,Normal,Guest'],
        ]);

        if ($validator->fails()) {
            Log::info('Register user validation error', ['Error' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }
        try {
            $registerUser = new User();
            $registerUser->name = $request->name ?? null;
            $registerUser->email = $request->email ?? null;
            $registerUser->password = $request->password ? Hash::make($request->password) : null;
            $registerUser->status = 'Active';
            $registerUser->save();

            $registerUser['token'] = $registerUser->createToken('auth-token')->accessToken;

            $registerUser->assignRole($request->user_type);

            Log::info('User register successfully', ['Email' => $request->email]);
            return $this->sendResponse(new AuthenticationResource($registerUser), 'User register successfully.');
        } catch (Exception $e) {
            Log::info('Unable to register the user.', ['Error' => $e->getMessage()]);
            return $this->sendError('Unable to register the user. Please try again later.', $e->getMessage(), 422);
        }
    }

    /**
     * Forget Password api for Admin, VIP, Normal User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            Log::info('Forget Password validation error', ['Error' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        try {
            if ($user = User::where('email', $request->email)->first()) {
                $generated_token = $this->generateUniqueCode();
                if ($user->hasRole(['Admin', 'VIP', 'Normal'])) {

                    //Forget Password Token
                    $user->remember_token = $generated_token;
                    $user->save();

                    $email = $request->email ?? null;
                    $data = [
                        'name' => $user->name ?? '',
                        'token' => $generated_token,
                    ];

                    Mail::send('emailTemplate.forgetpassword', $data, function ($message) use ($email) {
                        $message->to($email)
                            ->subject("Password Reset Request for Your Contest Account");
                    });

                    Log::info('Forget password token generated successfully', ['Email' => $request->email]);
                    $response = [
                        'message' => 'Successfully send the email, please check your email and enter the 6 digit code.',
                    ];

                    return response()->json($response, 200);
                } else {
                    Log::info('Email not found', ['Email' => $request->email]);
                    $response = [
                        'message' => 'No account with that email.',
                    ];

                    return response()->json($response, 201);
                }
            } else {
                Log::info('Unable to send forget password email to user', ['Email' => $request->email]);
                $response = [
                    'message' => 'No account with that email.',
                ];
                return response()->json($response, 201);
            }
        } catch (Exception $e) {
            Log::info('Unable to send forget password email to user.', ['Error' => $e->getMessage()]);
            return $this->sendError('Unable to send forget password email to user. Please try again later.', $e->getMessage(), 422);
        }
    }

    /**
     * Token Verification api for Admin, VIP, Normal User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function tokenVerification(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            Log::info('Forget Password token validation error', ['Error' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        try {

            if ($user = User::where('remember_token', $request->token)->first()) {

                if ($user->hasRole(['Admin', 'VIP', 'Normal'])) {

                    Log::info('Forget password token verified successfully', ['Token' => $request->email]);
                    $response = [
                        'message' => 'Successfully verified the forget password token.',
                    ];

                    return response()->json($response, 200);
                } else {
                    Log::info('Unable to verify the forget password token', ['Token' => $request->email]);
                    $response = [
                        'message' => 'Unable to verify the forget password token.',
                    ];

                    return response()->json($response, 201);
                }
            } else {
                Log::info('Unable to verify the forget password token', ['Token' => $request->email]);
                $response = [
                    'message' => 'Unable to verify the forget password token.',
                ];

                return response()->json($response, 201);
            }
        } catch (Exception $e) {
            Log::info('Unable to verify the forget password token.', ['Error' => $e->getMessage()]);
            return $this->sendError('Unable to verify the forget password token. Please try again later.', $e->getMessage(), 422);
        }
    }

    /**
     * Reset Password api for Admin, VIP, Normal User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            Log::info('Rest password validation error', ['Error' => $validator->errors()]);
            return $this->sendError('Validation error.', $validator->errors(), 422);
        }

        try {
            if ($changePassword = User::where('remember_token', $request->token)->first()) {
                $changePassword->remember_token = null;
                $changePassword->password = bcrypt($request->get('password'));
                $changePassword->save();

                Log::info('Successfully update the password', ['Password' => 'updated']);
                $response = [
                    'message' => 'Successfully update the password.',
                ];

                return response()->json($response, 200);
            } else {
                Log::info('Unable to update the password because this token not found', ['Email' => $request->email]);
                $response = [
                    'message' => 'Unable to update the password because this token not found.',
                ];
                return response()->json($response, 201);
            }
        } catch (Exception $e) {
            Log::info('Unable to update the user password', ['Error' => $e->getMessage()]);
            return $this->sendError('Unable to update the user password. Please try again later.', $e->getMessage(), 404);
        }
    }

    /**
     * Logout api for Admin, VIP, Normal User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutUser(Request $request): \Illuminate\Http\JsonResponse
    {
        if (Auth::check()) {
            Log::info('User logout successfully', ['Email' => auth()->user()->email ?? '']);

            auth()->user()->token()->revoke();
            return $this->sendResponse([], 'User logout successfully.');
        } else {
            Log::info('Session already destroyed', ['Email' => 'Session destroyed']);
            return $this->sendResponse([], 'User session already destroyed.');
        }
    }

    function generateUniqueCode(): string
    {
        $maxAttempts = 10; // Set a maximum number of attempts to avoid potential infinite loops

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            $uniqueCode = str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT); // Generate a random 6-digit code

            // Check if the generated code already exists in the database
            $existsInDatabase = User::where('remember_token', $uniqueCode)->exists();

            if (!$existsInDatabase) {
                return $uniqueCode; // Return the unique code if it doesn't exist in the database
            }
        }

        // Handle the case where a unique code couldn't be generated after the maximum attempts
        throw new Exception('Unable to generate a unique code after maximum attempts.');
    }
}

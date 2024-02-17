<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $activity = '';
    protected $status = '';
    protected $user_id = '';

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Login",
     *     tags={"Login"},
     *     description="Login",
     *     operationId="Login",
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         example="user@yopmail.com",
     *         description="Enter User Email",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="password",
     *         in="query",
     *         example="123456",
     *         description="Enter Password",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */

    public function login(Request $request)
    {
        try {
            $rules = [
                'email' => ['required', 'email:rfc,dns', Rule::exists('users', 'email')->where(function ($query) {
                    $query->whereNull('deleted_at');
                })],
                'password' => 'required',
            ];

            $message = [
                'email.required' => "Email is required",
                'email.email' => "Email is not valid",
                'email.exists' => "Email does not exist",
                'password.required' => "Password is required",
            ];

            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                $data = [
                    'status_code' => 400,
                    'message' => $validator->errors()->first(),
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $password = $request->password;
            $user = User::where('email', $request->email)->first();
            $hashpassword = $user->password;

            if (!Hash::check($password, $hashpassword)) {
                $data = [
                    'status_code' => 400,
                    'message' => 'Invalid password',
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }

            $credentials = $request->only('email', 'password');

            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    $data = [
                        'status' => 'Failure',
                        'status_code' => 401,
                        'message' => 'Unauthorized',
                        'data' => ['error' => 'Invalid credentials']
                    ];
                    return $this->sendJsonResponse($data);
                }

                $user = User::where('email', $request->email)->first();

                if (!$user) {
                    $data = [
                        'status_code' => 400,
                        'message' => 'User does not exist in this institute',
                        'data' => ""
                    ];
                    return $this->sendJsonResponse($data);
                }

                if (@$user) {
                    $request->merge([
                        "activity" => 'Login',
                        "status" => 'Active',
                        "user_id" => $user->id,
                    ]);
                    $authData['userDetails'] = $user;
                    $authData['token'] = $token;
                    $authData['token_type'] = 'bearer';
                    $authData['expires_in'] = JWTAuth::factory()->getTTL() * 60 * 24;
                    $data = [
                        'status_code' => 200,
                        'message' => 'Login Successfully!',
                        'data' => $authData
                    ];
                    return $this->sendJsonResponse($data);
                }
            } catch (JWTException $e) {
                if ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException) {
                    $data = [
                        'status_code' => 401,
                        'message' => 'Token Expired',
                    ];
                } elseif ($e instanceof \PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException) {
                    $data = [
                        'status_code' => 400,
                        'message' => 'Invalid Token',
                    ];
                } else {
                    $data = [
                        'status_code' => 400,
                        'message' => 'Token Not found',
                    ];
                }
                return $this->sendJsonResponse($data);
            } catch (\Exception $e) {
                Log::error(
                    [
                        'method' => __METHOD__,
                        'error' => [
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'message' => $e->getMessage()
                        ],
                        'created_at' => date("Y-m-d H:i:s")
                    ]
                );
                return $this->sendJsonResponse(array('status_code' => 500, 'message' => 'Something went wrong'));
            }
        } catch (\Exception $e) {
            Log::error(
                [
                    'method' => __METHOD__,
                    'error' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'message' => $e->getMessage()
                    ],
                    'created_at' => date("Y-m-d H:i:s")
                ]
            );
            return $this->sendJsonResponse(array('status_code' => 500, 'message' => 'Something went wrong'));
        }
    }
    /**
     * @OA\Post(
     *     path="/api/v1/signup",
     *     summary="Signup",
     *     tags={"Signup"},
     *     description="User Signup",
     *     operationId="Signup",
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         example="Kartik",
     *         description="Enter User Name(Only alphabets, Special characters not allowed and Space allowed)",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="email",
     *         in="query",
     *         example="user@yopmail.com",
     *         description="Enter User Email",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *      @OA\Response(
     *         response=200,
     *         description="json schema",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invalid Request"
     *     ),
     * )
     */

    public function signUp(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
                'email' => 'required|email:rfc,dns|unique:users,email',
            ];

            $message = [
                'name.required' => 'Name is required',
                'name.string' => 'Name must be string',
                'name.max' => 'Name must be less than 255 characters',
                'name.regex' => 'Name must be alphabets',
                'email.required' => 'Email is required',
                'email.email' => 'Email is not valid',
                'email.unique' => 'Email already exists',
            ];

            $validator = Validator::make($request->all(), $rules, $message);
            if ($validator->fails()) {
                $data = [
                    'status_code' => 400,
                    'message' => $validator->errors()->first(),
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $companyId = Company::first();
            if(empty($companyId)){
                $data = [
                    'status_code' => 400,
                    'message' => 'Company does not exist, Please run php artisan db:seed --class=CompanySeeder command in terminal',
                    'data' => ""
                ];
                return $this->sendJsonResponse($data);
            }
            $randomPassword = Str::random(8);
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($randomPassword);
            $user->company_id = $companyId->id;
            $user->save();

            $data = [
                'status_code' => 200,
                'message' => 'User registered successfully',
                'data' => [
                    'TemporaryPassword' => $randomPassword,
                    'UserDetails' => $user
                ]
            ];
            return $this->sendJsonResponse($data);

        } catch (\Exception $e) {
            Log::error(
                [
                    'method' => __METHOD__,
                    'error' => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'message' => $e->getMessage()
                    ],
                    'created_at' => date("Y-m-d H:i:s")
                ]
            );
            return $this->sendJsonResponse(array('status_code' => 500, 'message' => 'Something went wrong'));
        }
    }
}

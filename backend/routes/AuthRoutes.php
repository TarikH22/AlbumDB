<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;


Flight::group('/auth', function () {
    /**
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     tags={"auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="securePassword"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="email", type="string"),
     *                 @OA\Property(property="name", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    Flight::route("POST /register", function () {
        $data = Flight::request()->data->getData();

        $response = Flight::auth_service()->register($data);

        if ($response['success']) {
            Flight::json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $response['data']
            ]);
        } else {
            Flight::json([
                'success' => false,
                'error' => $response['error']
            ], 400);
        }
    });

    /**
     * @OA\Post(
     *      path="/auth/login",
     *      tags={"auth"},
     *      summary="Login to system using email and password",
     *      @OA\Response(
     *           response=200,
     *           description="Student data and JWT"
     *      ),
     *      @OA\RequestBody(
     *          description="Credentials",
     *          @OA\JsonContent(
     *              required={"email","password"},
     *              @OA\Property(property="email", type="string", example="demo@gmail.com", description="Student email address"),
     *              @OA\Property(property="password", type="string", example="some_password", description="Student password")
     *          )
     *      )
     * )
     */
    Flight::route('POST /login', function () {
        $data = Flight::request()->data->getData();

        $response = Flight::auth_service()->login($data);

        if ($response['success']) {
            Flight::json([
                'success' => true,
                'message' => 'User logged in successfully',
                'data' => $response['data']
            ]);
        } else {
            Flight::json([
                'success' => false,
                'error' => $response['error']
            ], 401);
        }
    });
});

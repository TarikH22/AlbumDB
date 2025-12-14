<?php

require_once __DIR__ . '/../services/UserService.php';

$userService = new UserService();

/**
 * @OA\Post(
 *     path="/users/register",
 *     tags={"users"},
 *     summary="Register a new user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username", "email", "password", "first_name", "last_name"},
 *             @OA\Property(property="username", type="string", example="johndoe"),
 *             @OA\Property(property="email", type="string", example="john@example.com"),
 *             @OA\Property(property="password", type="string", example="password123"),
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User registered successfully"
 *     )
 * )
 */
Flight::route('POST /users/register', function () use ($userService) {
    $data = Flight::request()->data->getData();
    Flight::json($userService->registerUser($data));
});

/**
 * @OA\Post(
 *     path="/users/login",
 *     tags={"users"},
 *     summary="User login",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"username", "password"},
 *             @OA\Property(property="username", type="string", example="johndoe"),
 *             @OA\Property(property="password", type="string", example="password123")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Login successful"
 *     )
 * )
 */
Flight::route('POST /users/login', function () use ($userService) {
    $data = Flight::request()->data->getData();
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    $response = $userService->loginUser($username, $password);

    if ($response['success']) {
        session_start();
        $_SESSION['user_id'] = $response['data']['user_id'];
        $_SESSION['username'] = $response['data']['username'];
    }

    Flight::json($response);
});

Flight::route('POST /users/logout', function () {
    session_start();
    session_destroy();
    Flight::json(['success' => true, 'message' => 'Logged out successfully']);
});

/**
 * @OA\Get(
 *     path="/users",
 *     tags={"users"},
 *     summary="Get all users (Admin only)",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Response(
 *         response=200,
 *         description="List of users"
 *     )
 * )
 */
Flight::route('GET /users', function () use ($userService) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    Flight::json($userService->getAllUsers());
});

/**
 * @OA\Get(
 *     path="/users/stats/all",
 *     tags={"users"},
 *     summary="Get admin statistics",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Response(
 *         response=200,
 *         description="Admin stats"
 *     )
 * )
 */
Flight::route('GET /users/stats/all', function () use ($userService) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    Flight::json($userService->getStats());
});

/**
 * @OA\Get(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Get user by ID",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="User ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User profile"
 *     )
 * )
 */
Flight::route('GET /users/@id', function ($id) use ($userService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    Flight::json($userService->getUserProfile($id));
});

/**
 * @OA\Put(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Update user profile",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="first_name", type="string", example="John"),
 *             @OA\Property(property="last_name", type="string", example="Doe")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated"
 *     )
 * )
 */
Flight::route('PUT /users/@id', function ($id) use ($userService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    $data = Flight::request()->data->getData();
    Flight::json($userService->updateUserProfile($id, $data));
});

/**
 * @OA\Delete(
 *     path="/users/{id}",
 *     tags={"users"},
 *     summary="Deactivate user",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="User deactivated"
 *     )
 * )
 */
Flight::route('DELETE /users/@id', function ($id) use ($userService) {
    Flight::auth_middleware()->authorizeRoles([Roles::ADMIN]);
    Flight::json($userService->deactivateUser($id));
});

/**
 * @OA\Get(
 *     path="/users/session",
 *     tags={"users"},
 *     summary="Get current session user",
 *     security={
 *         {"bearerAuth": {}}
 *     },
 *     @OA\Response(
 *         response=200,
 *         description="Current user profile"
 *     )
 * )
 */
Flight::route('GET /users/session', function () use ($userService) {
    Flight::auth_middleware()->authorizeRoles([Roles::USER, Roles::ADMIN]);
    session_start();
    if (isset($_SESSION['user_id'])) {
        Flight::json($userService->getUserProfile($_SESSION['user_id']));
    } else {
        Flight::json(['success' => false, 'message' => 'No active session', 'code' => 401]);
    }
});

<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../dao/UserDAO.php';

class UserService extends BaseService
{
    private $userDAO;

    public function __construct()
    {
        $this->userDAO = new UserDAO();
    }

    public function registerUser($data)
    {
        $validation = $this->validateRequiredFields($data, [
            'username',
            'email',
            'password',
            'first_name',
            'last_name'
        ]);

        if (!$validation['valid']) {
            return $this->errorResponse($validation['errors']);
        }

        $errors = [];

        if (!$this->validateLength($data['username'], 3, 50)) {
            $errors[] = "Username must be between 3 and 50 characters";
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
            $errors[] = "Username can only contain letters, numbers, and underscores";
        }

        if (!$this->validateEmail($data['email'])) {
            $errors[] = "Invalid email format";
        }

        if (!$this->validateLength($data['password'], 6, 255)) {
            $errors[] = "Password must be at least 6 characters";
        }

        if (!$this->validateLength($data['first_name'], 1, 100)) {
            $errors[] = "First name must be between 1 and 100 characters";
        }

        if (!$this->validateLength($data['last_name'], 1, 100)) {
            $errors[] = "Last name must be between 1 and 100 characters";
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        if ($this->userDAO->usernameExists($data['username'])) {
            return $this->errorResponse('Username already exists');
        }

        if ($this->userDAO->emailExists($data['email'])) {
            return $this->errorResponse('Email already exists');
        }

        $data['username'] = $this->sanitizeString($data['username']);
        $data['email'] = $this->sanitizeString($data['email']);
        $data['first_name'] = $this->sanitizeString($data['first_name']);
        $data['last_name'] = $this->sanitizeString($data['last_name']);

        $data['password_hash'] = password_hash($data['password'], PASSWORD_DEFAULT);
        unset($data['password']);

        $userId = $this->userDAO->create($data);

        if (!$userId) {
            $this->logError('UserService', 'registerUser', 'Failed to create user');
            return $this->errorResponse('Failed to create user', 500);
        }

        $user = $this->userDAO->getById($userId);

        return $this->successResponse($user, 'User registered successfully');
    }

    public function loginUser($username, $password)
    {
        if (empty($username) || empty($password)) {
            return $this->errorResponse('Username and password are required');
        }

        $user = $this->userDAO->getByUsername($username);

        if (!$user) {
            $user = $this->userDAO->getByEmail($username);
        }

        if (!$user) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (!$user['is_active']) {
            return $this->errorResponse('Account is inactive', 403);
        }

        if (!password_verify($password, $user['password_hash'])) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        $this->userDAO->updateLastLogin($user['user_id']);

        unset($user['password_hash']);

        return $this->successResponse($user, 'Login successful');
    }

    public function getUserProfile($userId)
    {
        $user = $this->userDAO->getById($userId);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        return $this->successResponse($user);
    }

    public function updateUserProfile($userId, $data)
    {
        $user = $this->userDAO->getById($userId);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $errors = [];

        if (isset($data['username'])) {
            if (!$this->validateLength($data['username'], 3, 50)) {
                $errors[] = "Username must be between 3 and 50 characters";
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $data['username'])) {
                $errors[] = "Username can only contain letters, numbers, and underscores";
            }

            $existingUser = $this->userDAO->getByUsername($data['username']);
            if ($existingUser && $existingUser['user_id'] != $userId) {
                $errors[] = "Username already exists";
            }
        }

        if (isset($data['email'])) {
            if (!$this->validateEmail($data['email'])) {
                $errors[] = "Invalid email format";
            }

            $existingUser = $this->userDAO->getByEmail($data['email']);
            if ($existingUser && $existingUser['user_id'] != $userId) {
                $errors[] = "Email already exists";
            }
        }

        if (isset($data['first_name']) && !$this->validateLength($data['first_name'], 1, 100)) {
            $errors[] = "First name must be between 1 and 100 characters";
        }

        if (isset($data['last_name']) && !$this->validateLength($data['last_name'], 1, 100)) {
            $errors[] = "Last name must be between 1 and 100 characters";
        }

        if (!empty($errors)) {
            return $this->errorResponse($errors);
        }

        foreach (['username', 'email', 'first_name', 'last_name'] as $field) {
            if (isset($data[$field])) {
                $data[$field] = $this->sanitizeString($data[$field]);
            }
        }

        $result = $this->userDAO->update($userId, $data);

        if (!$result) {
            $this->logError('UserService', 'updateUserProfile', 'Failed to update user');
            return $this->errorResponse('Failed to update user profile', 500);
        }

        return $this->successResponse(null, 'Profile updated successfully');
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        $user = $this->userDAO->getByUsername(''); // Get with password hash
        $user = $this->userDAO->getById($userId);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $userWithPassword = $this->userDAO->getByUsername($user['username']);

        if (!password_verify($currentPassword, $userWithPassword['password_hash'])) {
            return $this->errorResponse('Current password is incorrect', 401);
        }

        if (!$this->validateLength($newPassword, 6, 255)) {
            return $this->errorResponse('New password must be at least 6 characters');
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $result = $this->userDAO->updatePassword($userId, $newPasswordHash);

        if (!$result) {
            $this->logError('UserService', 'changePassword', 'Failed to update password');
            return $this->errorResponse('Failed to change password', 500);
        }

        return $this->successResponse(null, 'Password changed successfully');
    }

    public function searchUsers($searchTerm)
    {
        if (empty($searchTerm) || strlen(trim($searchTerm)) < 2) {
            return $this->errorResponse('Search term must be at least 2 characters');
        }

        $users = $this->userDAO->search($this->sanitizeString($searchTerm));

        return $this->successResponse([
            'users' => $users,
            'count' => count($users)
        ]);
    }

    public function deactivateUser($userId)
    {
        $user = $this->userDAO->getById($userId);

        if (!$user) {
            return $this->errorResponse('User not found', 404);
        }

        $result = $this->userDAO->delete($userId);

        if (!$result) {
            $this->logError('UserService', 'deactivateUser', 'Failed to deactivate user');
            return $this->errorResponse('Failed to deactivate user', 500);
        }

        return $this->successResponse(null, 'User account deactivated successfully');
    }

    public function getAllUsers($limit = null, $offset = 0)
    {
        $users = $this->userDAO->getAll($limit, $offset);

        return $this->successResponse([
            'users' => $users,
            'count' => count($users)
        ]);
    }

    public function getStats()
    {
        // Get user count
        $users = $this->userDAO->getAll();
        $userCount = count($users);

        // Get total ratings count
        require_once __DIR__ . '/../dao/RatingDAO.php';
        $ratingDAO = new RatingDAO();
        $allRatings = $ratingDAO->getAll();
        $ratingCount = count($allRatings);

        // Get total reviews count
        require_once __DIR__ . '/../dao/ReviewDAO.php';
        $reviewDAO = new ReviewDAO();
        $allReviews = $reviewDAO->getAll();
        $reviewCount = count($allReviews);

        return $this->successResponse([
            'total_users' => $userCount,
            'total_ratings' => $ratingCount,
            'total_reviews' => $reviewCount
        ]);
    }
}

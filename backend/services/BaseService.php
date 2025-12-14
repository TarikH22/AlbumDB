<?php

abstract class BaseService
{
    protected $dao;

    public function add($entity)
    {
        return $this->dao->add($entity);
    }

    protected function validateRequiredFields($data, $requiredFields)
    {
        $errors = [];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || trim($data[$field]) === '') {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    protected function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    protected function validateYear($year)
    {
        return is_numeric($year) &&
            $year >= 1900 &&
            $year <= date('Y') + 1;
    }

    protected function validateRating($rating)
    {
        return is_numeric($rating) &&
            $rating >= 1 &&
            $rating <= 10;
    }

    protected function validateLength($str, $min, $max)
    {
        $length = strlen(trim($str));
        return $length >= $min && $length <= $max;
    }

    protected function sanitizeString($str)
    {
        return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
    }

    protected function successResponse($data = null, $message = 'Operation successful')
    {
        $response = [
            'success' => true,
            'message' => $message
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return $response;
    }

    protected function errorResponse($errors, $code = 400)
    {
        return [
            'success' => false,
            'errors' => is_array($errors) ? $errors : [$errors],
            'code' => $code
        ];
    }

    protected function logError($service, $method, $message)
    {
        error_log("$service::$method - $message");
    }
}

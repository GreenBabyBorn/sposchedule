<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TeacherExistsException extends Exception
{
    protected $teacherId;

    public function __construct($teacherId)
    {
        parent::__construct('Такой преподаватель уже существует.');
        $this->teacherId = $teacherId;
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        // Here you could log the exception if needed
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'teacher_id' => $this->teacherId,
        ], 422);
    }
}
<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class SubjectExistsException extends Exception
{
    protected $subjectId;

    public function __construct($subjectId)
    {
        parent::__construct('Такой предмет уже существует.');
        $this->subjectId = $subjectId;
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
            'subject_id' => $this->subjectId,
        ], 422);
    }
}
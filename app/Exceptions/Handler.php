<?php

namespace App\Exceptions;

use Spatie\Permission\Exceptions\UnauthorizedException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $levels = [
        //
    ];

    protected $dontReport = [
        //
    ];

    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Add custom logging here if needed
        });
    }

    public function render($request, Throwable $e)
    {
        // Check if this is an API request (api/* route or expects JSON)
        if ($request->is('api/*') || $request->expectsJson()) {

            // Handle validation errors
            if ($e instanceof ValidationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validation failed',
                    'errors'  => $e->errors(),
                ], 422);
            }

            // Handle permission/authorization errors (Spatie Permission)
            if ($e instanceof UnauthorizedException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'You do not have permission to access this resource.',
                ], 403);
            }

            // Handle duplicate key/unique constraint database errors
            if ($e instanceof UniqueConstraintViolationException) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Duplicate entry: the data already exists.',
                ], 422);
            }

            // Handle all other exceptions
            return response()->json([
                'status'  => 'error',
                'message' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }

        // Fallback to default for non-API requests (HTML)
        return parent::render($request, $e);
    }
}

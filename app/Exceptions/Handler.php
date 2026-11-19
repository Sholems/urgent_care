<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Provide a consistent JSON structure for validation errors.
     */
    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return response()->json([
            'status'  => 'error',
            'message' => $exception->getMessage(),
            'errors'  => $exception->errors(),
        ], $exception->status);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        if ($this->shouldReturnJson($request, $e)) {
            return $this->buildApiErrorResponse($request, $e);
        }

        return parent::render($request, $e);
    }

    /**
     * Build the JSON response for API exceptions.
     */
    private function buildApiErrorResponse($request, Throwable $e): JsonResponse
    {
        $status  = SymfonyResponse::HTTP_INTERNAL_SERVER_ERROR;
        $headers = [];

        if ($e instanceof HttpExceptionInterface) {
            $status  = $e->getStatusCode();
            $headers = $e->getHeaders();
        }

        $message = trim($e->getMessage());
        if ($message === '') {
            $message = SymfonyResponse::$statusTexts[$status] ?? 'Server Error';
        }

        $payload = [
            'status'  => 'error',
            'message' => $message,
            'code'    => $status,
        ];

        if (config('app.debug')) {
            $payload['exception'] = class_basename($e);
            $payload['location']  = sprintf('%s:%d', $e->getFile(), $e->getLine());
            $payload['trace']     = collect($e->getTrace())
                ->take(5)
                ->map(fn ($frame) => Arr::only($frame, ['file', 'line', 'function', 'class']));
        } else {
            $reference = (string) Str::uuid();
            $payload['reference'] = $reference;

            Log::error($message, [
                'reference' => $reference,
                'exception' => $e,
                'url'       => $request->fullUrl(),
                'input'     => $request->except($this->dontFlash),
            ]);
        }

        return response()->json($payload, $status, $headers);
    }
}

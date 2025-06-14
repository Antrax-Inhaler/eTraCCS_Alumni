<?php

namespace App\Exceptions;

use Inertia\Inertia;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof NotFoundHttpException && !$request->expectsJson()) {
            return Inertia::render('Errors/404')->toResponse($request)->setStatusCode(404);
        }

        return parent::render($request, $exception);
    }
}

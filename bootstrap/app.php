<?php

use App\Http\Middleware\RoleMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;





return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
  //  $middleware->statefulApi();  //new

    $middleware->alias([
        'role'            => RoleMiddleware::class,

    ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
          $exceptions->renderable(function (NotFoundHttpException $e, Request $request) {
            $previous = $e->getPrevious();

            if ($previous instanceof ModelNotFoundException) {
                $modelName = class_basename($previous->getModel());
                return response()->json([
                    'message' => "{$modelName} not found."
                ], 404);
            }

            return response()->json([
                'message' => 'Route not found.'
            ], 404);
        });
    })->create();
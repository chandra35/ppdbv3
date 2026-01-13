<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\OperatorMiddleware;
use App\Http\Middleware\PengujiMiddleware;
use App\Http\Middleware\LogVisitor;
use App\Http\Middleware\TrackUserActivity;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'operator' => OperatorMiddleware::class,
            'penguji' => PengujiMiddleware::class,
            'log.visitor' => LogVisitor::class,
        ]);
        
        // Add LogVisitor and TrackUserActivity middleware to web group
        $middleware->web(append: [
            LogVisitor::class,
            TrackUserActivity::class,
        ]);
        
        // Replace default authenticate middleware with custom one
        $middleware->web(replace: [
            \Illuminate\Auth\Middleware\Authenticate::class => \App\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

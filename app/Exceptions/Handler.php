<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            $msg = "ERROR 500\n";
            $msg.="message: ".$e->getMessage()."\n";
            $msg.="line: ".$e->getLine()."\n";
            $msg.="file: ".$e->getFile()."\n";
            $msg.="code: ".$e->getCode()."\n";
            reportToDev($msg);
        });
    }
}

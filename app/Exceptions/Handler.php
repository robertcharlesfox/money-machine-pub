<?php

namespace App\Exceptions;

use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Facebook\WebDriver\Exception\UnknownServerException;
use Facebook\WebDriver\Exception\NoSuchWindowException;
use Facebook\WebDriver\Exception\NoSuchElementException;
use Facebook\WebDriver\Exception\WebDriverCurlException;

use GuzzleHttp\Ring\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;

use Exception;
use PDOException;
// use ErrorException;
// ErrorException for file_put_contents is due to running as www in MAMP, instead of robertfox
// chown the storage directory for www, then run sudo php artisan queue:listen
// note: sudo!
use Illuminate\Session\TokenMismatchException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        ConnectException::class,
        ServerException::class,
        HttpException::class,
        TokenMismatchException::class,
        ModelNotFoundException::class,
        PDOException::class,
        // ProcessTimedOutException::class,
        // UnknownServerException::class,
        NoSuchWindowException::class,
        // NoSuchElementException::class,
        // WebDriverCurlException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $e
     * @return void
     */
    public function report(Exception $e)
    {
        return parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $e
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if ($e instanceof ModelNotFoundException) {
            $e = new NotFoundHttpException($e->getMessage(), $e);
        }

        return parent::render($request, $e);
    }
}

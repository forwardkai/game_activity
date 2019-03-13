<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);


/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->configureMonologUsing(function(Monolog\Logger $monoLog) use ($app){
    //获取controller@action
    $route = '';
    $routeInfo = $app->request->route();
    if(!empty($routeInfo) && is_array($routeInfo) && isset($routeInfo[1]) && isset($routeInfo[1]['uses'])){
        $route = $routeInfo[1]['uses'];
        $arr = explode('\\', $route);
        $route = array_pop($arr);
    }
    //获取请求来源
    $fromAppId = filter($app->request->input('app_id', ''), 's');
    $fromAppName = filter($app->request->input('app_name', ''), 's');
    return $monoLog->pushHandler(
        (new \Monolog\Handler\RotatingFileHandler(env('LOG_PATH').env('APP_NAME').'-log',$app->make('config')->get('app.log_max_files', 60)))->setFormatter(new Monolog\Formatter\LineFormatter(gethostname()." ".env("APP_NAME")." ".$fromAppId.",".$fromAppName." ".$route." [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n", null, true, true))
    );
});


/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;

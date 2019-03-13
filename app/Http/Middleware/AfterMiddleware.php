<?php

namespace App\Http\Middleware;

use Closure;

class AfterMiddleware
{
    public function handle($response, Closure $next)
    {
        $response = $next($response);
        if($response->status()!=200) return $response;
        $data = $response->getOriginalContent();
        $data = empty($data) ? [] : $data;
        $res = is_integer($data)?['code' => $data, 'message' => config('appstatus.'.$data)]:json_encode(['code'=> 2000, 'message'=>'请求成功', 'data'=>$data]);
        $response->setContent($res);
        return $response;
    }
}

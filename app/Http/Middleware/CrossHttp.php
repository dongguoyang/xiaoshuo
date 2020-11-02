<?php

namespace App\Http\Middleware;

use Illuminate\Http\Response as IllResponse;
use Symfony\Component\HttpFoundation\Response as SymResponse;

class CrossHttp {
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next) {
        $response = $next($request);
        // 所有不允许跨域的地址
        $noCrossHttp = [
            'payment_topay0',
            'payment_topay',
            'payment_notify',
            'payment_return',
            'payment_find',
        ];

        if (request()->route()) {
            $route_name = request()->route()->getName(); // 路由名称
            if (in_array($route_name, $noCrossHttp)) {
                return $response;
            }
        }

        // 需要设置的 header 列表
        $headers = [
            'Access-Control-Allow-Origin'       => isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*',
            'Access-Control-Allow-Headers'      => 'Origin, Content-Type, Cookie, Accept, X-Requested-With, Content-Type',
            'Access-Control-Allow-Methods'      => 'GET, POST, PATCH, PUT, OPTIONS',
            // 'Access-Control-Allow-Credentials'  => 'false',
            'Access-Control-Allow-Credentials'  => 'true', // 跨域时允许 cookie
        ];

        if ($response instanceof IllResponse) {
            foreach ($headers as $key => $value) {
                $response->header($key, $value);
            }
        } else if ($response instanceof SymResponse) {
            foreach ($headers as $key => $value) {
                $response->headers->set($key, $value);
            }
        }
        /*
        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '*';
        $response->header('Access-Control-Allow-Origin', $origin);
        $response->header('Access-Control-Allow-Headers', 'Origin, Content-Type, Cookie, Accept, X-Requested-With, Content-Type');
        $response->header('Access-Control-Allow-Methods', 'GET, POST, PATCH, PUT, OPTIONS');
        // $response->header('Access-Control-Allow-Credentials', 'false');
        $response->header('Access-Control-Allow-Credentials', 'true'); // 跨域时允许 cookie
        */
        return $response;
    }
}

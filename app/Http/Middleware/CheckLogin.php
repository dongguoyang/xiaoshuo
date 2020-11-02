<?php

namespace App\Http\Middleware;

use App\Logics\Traits\LoginTrait;
use App\Logics\Services\src\UserService;

class CheckLogin
{
    use LoginTrait;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $sess = $this->loginGetSession(true);

        if (!$sess['id'])
        {
            return response()->json($this->getResponse());
        }
        (new UserService())->PutTouInfo(1,$sess['id']);
        return $next($request);
    }

    private function getResponse()
    {
        return [
            'err_code'  => 800,
            'err_msg'   => '请登录',
            'data'      => null,
        ];
    }
}
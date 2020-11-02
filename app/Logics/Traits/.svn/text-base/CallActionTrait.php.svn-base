<?php
namespace App\Logics\Traits;

use Illuminate\Routing\RouteDependencyResolverTrait as DepResolver;

trait CallActionTrait {
    /**
     * 调用其他控制器方法，跳过路由级权限验证
     * @param string $action 控制器方法，形如：class_path\ExampleController@someAction
     * @param array $routeParameters 路由参数，选填
     * @return mixed 被调用的控制器方法的返回结果
     */
    private function callControllerMethod($action = '', $routeParameters = []){
        return with(new Resolver)->callControllerMethod($action, $routeParameters);
    }
}
class Resolver {
    use DepResolver;

    private $container;

    public function __construct(){
        $this->container = app();
    }

    public function callControllerMethod($action = '', $routeParameters = []){
        list($class, $method) = explode('@', $action);
        $instance = $this->container->make($class);
        $parameters = $this->resolveClassMethodDependencies($routeParameters, $instance, $method);
        return $instance->callAction($method, $parameters);
    }
}
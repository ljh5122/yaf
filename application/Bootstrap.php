<?php

/**
 * 全局自定的工作
 * 所有在Bootstrap类中, 以_init开头的方法, 都会被Yaf调用,
 * 这些方法, 都接受一个参数:Yaf_iDspatcher $dispatcher
 * 调用的次序, 和申明的次序相同
 */
class Bootstrap extends Yaf\Bootstrap_Abstract{
    //注册配置对象到config
    public function _initConfig(){
        $config = Yaf\Application::app()->getConfig();
        Yaf\Registry::set('config', $config);

        /*
        // thinkORM全局初始化
        $dbConfig = [
                'type' => $config->database->master->type,
                'hostname' => $config->database->master->hostname,
                'database' => $config->database->master->database,
                'username' => $config->database->master->username,
                'password' => $config->database->master->password,
                'hostport' => $config->database->master->hostport,
                'prefix' => $config->database->master->prefix,
                'break_reconnect' => $config->database->master->break_reconnect
            ];

        \think\Db::setConfig($dbConfig);
        */
    }

    //配置session
    public function _initSession(){
        $session = Yaf\Session::getInstance();
        $session->start();
    }

    //配置路由
    public function _initRoute(Yaf\Dispatcher $dispatcher){
        // $application_path = Yaf\Registry::get('application.directory')->application->directory;
        // $config = new Yaf\Config\Ini($application_path . 'conf/routes.ini', ini_get('yaf.environ'));
        // if ($config->routes) {
        //     $router = $dispatcher->getRouter();
        //     $router->addConfig($config->routes);
        // }
    }

    //注册自定义方法
    public function _initFunctions(){
        Yaf\Loader::import('functions.php');
    }

    /**
     * 注册一个插件
     * 插件的目录是在application/plugins
     */
    // public function _initPlugin(Yaf\Dispatcher $dispatcher) {
    // 	if(Yaf\loader::import(APP_PATH.'/application/plugins/UserPlugin.php') == false){
    // 		echo '插件类加载失败';exit;
    // 	}

    //     $user = new UserPlugin();
    //     $dispatcher->registerPlugin($user);
    // }
}
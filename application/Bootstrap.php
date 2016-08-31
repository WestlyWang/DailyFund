<?php
class Bootstrap extends Yaf_Bootstrap_Abstract{

    public function _initLibrary() {
	    $loader = Yaf_Loader::getInstance();
	}

	public function _initConfig() {
		$config = Yaf_Application::app()->getConfig();
		Yaf_Registry::set("config", $config);
		Yaf_Dispatcher::getInstance()->autoRender(FALSE);
	}


    /**
      * @param Yaf\Dispatcher $dispatcher 
      * @access public
      * @return void
      */
    public function _initRoute(Yaf_Dispatcher $dispatcher) {
	    $router = $dispatcher->getInstance()->getRouter();
	    $router->addRoute("myRoute", new MyRouter());
    }

	public function _initApp(){
		session_start();
	}
}
?>

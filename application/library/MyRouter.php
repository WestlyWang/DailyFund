<?php

class MyRouter extends Yaf_Request_Abstract implements Yaf_Route_Interface {

    /**
     * route 
     * 
     * @param mixed $req 
     * @access public
     * @return boolean
     */
    public function route($req) {
	    $request_uri = trim($_SERVER['REQUEST_URI']);
	    $tmp_uri = explode('?', $request_uri);
	    $uri = array_values(
		    array_filter(
			    explode('/',str_replace('\\', '/', $tmp_uri[0]))
				)
			);
		if(!empty($uri)){
			$req->setControllerName($uri[1]);
			$req->setActionName($uri[2]);
		}
	    if(!empty($tmp_uri[1])){
			$params = explode('&',$tmp_uri[1]);
			foreach($params as $param){
				$p = explode('=',$param);
				$key = $p[0]==''?"key":$p[0];
				$value = $p[1]==''?"value":$p[1];
				$req->setParam($key,$value);
			}
	    }
	    return true;
    }

    /**
     * assemble 
     * 
     * @param array $mvc 
     * @param array $query 
     * @access public
     * @return boolean
     */
    public function assemble (array $mvc, array $query = null){
        return true;
    }
}

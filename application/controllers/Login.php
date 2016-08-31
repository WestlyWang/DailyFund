<?php
	
	/**
	  *登录管理
	  *@author wangshuai
	  *@time 2016-08-08
	  */
	class LoginController extends Yaf_Controller_Abstract{
		public $actions = array(
				'login' => "actions/login/LoginAction.php",
				'register' => "actions/login/RegisterAction.php",
				);
	}
?>

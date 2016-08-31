<?php
	/**
	  *用户信息
	  *@author wangshuai
	  *@time 2016-08-26
	  */
	class UserController extends Yaf_Controller_Abstract{
		public $actions = array(
				'show' => "actions/user/ShowAction.php",
		        'change' => "actions/user/ChangeAction.php",
				);
	}
?>
<?php
	
	/**
	  *账目控制
	  *@author wangshuai
	  *@time 2016-08-08
	  */
	class AccountController extends Yaf_Controller_Abstract{
		public $actions = array(
				'add' => "actions/account/AddAction.php",
				'delete' => "actions/account/DeleteAction.php",
				'modify' => "actions/account/ModifyAction.php",
				'list' => "actions/account/ListAction.php",
				'tomodify' => "actions/account/ToModifyAction.php",
				'transform' => "actions/account/TransformAction.php",
				'md' => "actions/account/MDAction.php",
				);
	}
?>

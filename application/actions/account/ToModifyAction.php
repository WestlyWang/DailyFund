<?php
	
	/**
	  *错误更新Action
	  *账户信息变更Action
	  *@file ToModifyAction.php
	  *@author wangshuai
	  *@time 2016-08-19
	  */
	class ToModifyAction extends Yaf_Action_Abstract{
		public function execute(){
			$request_method = $_SERVER['REQUEST_METHOD'];
			$username = $_SESSION['username'];
			$request_method = strtolower($request_method);
			$params = array();
			if($request_method == "get"){
				$params = $this->getRequest()->getParams();
			}
			else{
				$params = $_POST;
			}
			$result = AccountModel::querySingle($username,$params);
			if(!empty($result)){
				$this->getView()->display("account/ModifyAccount.phtml",array("info" => $result));
			}else{
				$this->getView()->display("error.phtml",array("error" => "没有对应的数据信息!"));
			}
		}
	}
?>


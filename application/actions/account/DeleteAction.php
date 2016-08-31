<?php
	
	/**
	  *删除错误账目Action
	  *@file DeleteAction.php
	  *@author wangshuai
	  *@time 2016-08-09
	  */
	class DeleteAction extends Yaf_Action_Abstract{
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
			if(AccountModel::del($username,$params)){
				$this->getView()->display("homepage.phtml",array("method" => "del"));
			}
			else{
				$this->responseError("删除信息失败!");
			}
		}
		private function responseError($errorMsg){
				$this->getView()->display("error.phtml",array("error"=>$errorMsg));
		}
	}
?>


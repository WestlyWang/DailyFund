<?php
	
	/**
	  *转账Action
	  *@file TransformAction.php
	  *@author wangshuai
	  *@time 2016-08-24
	  */
	class TransformAction extends Yaf_Action_Abstract{
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
			if(!$this->validate($params,$errorMsg)){
				$this->responseError($errorMsg);
				return;
			}
			$sourcereflection = array(
								"card" => "银行卡",
								"cash" => "现金",
								"online" => "在线支付",
					);
			$outinfo = array(
					"user" => $username,
					"recordtime" => date("Y-m-d",time()),
					"briefreason" => "转出",
					"cost" => $params['cost'],
					"source" => $sourcereflection[$params['source']],
					"singlebalance" => $_SESSION[$params['source']] - $params['cost'],
					"type" => "1",
					"balance" => $_SESSION['balance'] - $params['cost'],
					"remarks" => "转出到".$sourcereflection[$params['dest']]
			);
			$ininfo = array(
					"user" => $username,
					"recordtime" => date("Y-m-d",time()),
					"briefreason" => "转入",
					"cost" => $params['cost'],
					"source" => $sourcereflection[$params['dest']],
					"singlebalance" => $_SESSION[$params['dest']] + $params['cost'],
					"type" => "0",
					"balance" => $_SESSION['balance'],
					"remarks" => "从".$sourcereflection[$params['source']]."转入"
			);
			if(AccountModel::add($username,$outinfo)&&AccountModel::add($username,$ininfo)){
				$_SESSION[$params['source']] -= $params['cost'];
				$_SESSION[$params['dest']] += $params['cost'];
				header("Location:md");
			}else{
				$this->responseError($errorMsg);
			}
		}

		private function validate($params,&$errorMsg){
			if($params['source'] == $params['dest'] || $params['cost']==0){
				$errorMsg = "错误的转账信息，本次操作被取消";
				return false;
			}
			if($params['source']==-1){
				$errorMsg = "请选择转出账户";
				return false;
			}
			if($params['dest']==-1){
				$errorMsg = "请选择转入账户";
				return false;
			}
			if(!is_numeric($params['cost']) && $params['cost'] <=0){
				$errorMsg = "请输入正确的转账金额";
				return false;
			}
			return true;
		}

		private function responseError($errorMsg){
				$this->getView()->display("error.phtml",array("error"=>$errorMsg));
		}
	}
?>


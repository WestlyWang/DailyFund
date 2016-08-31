<?php
	
	/**
	  *查询账目Action
	  *@file ListAction.php
	  *@author wangshuai
	  *@time 2016-08-09
	  */
	class MDAction extends Yaf_Action_Abstract{
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
			$sn = isset($params['sn']) && $params['sn']>0 ? $params['sn']:1;//当前页
			$rn = isset($params['rn']) && $params['rn']>0 ? $params['rn']:20;//每页记录数
			$tn = isset($params['tn']) && $params['tn']>0 ? $params['tn']:1;//总页数
			$sn = $sn>$tn ? $tn : $sn;
			unset($params['sn']);
			unset($params['rn']);
			unset($params['tn']);
			if($tn == 1){
				$tn = AccountModel::total($username,$params) / $rn;
				$tn = ceil($tn);
			}
			$query = "";
			foreach($params as $k=>$v){
				$query = $query."$k=$v&";
			}
			$results = AccountModel::query($username,$params,$sn-1,$rn);
			foreach($results as $index=>$r){
				foreach($r as $k=>$v){
					if($k=="singlebalance" || $k=="balance" || $k=="cost"){
						$r[$k] = number_format($v,2,'.','');
					}
				}
				$results[$index]=$r;
			}
			$this->getView()->display("account/MDAccount.phtml",
									array("result" => $results,
										  "query" => $query,
										  "sn" => $sn,
										  "rn" => $rn,
										  "tn" => $tn));
		}
	}
?>


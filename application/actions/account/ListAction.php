<?php
	
	/**
	  *查询账目Action
	  *@file ListAction.php
	  *@author wangshuai
	  *@time 2016-08-09
	  */
	class ListAction extends Yaf_Action_Abstract{
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
			$tn = isset($params['tn']) && $params['tn']>0 ? $params['tn']:0;//总页数
			unset($params['sn']);
			unset($params['rn']);
			unset($params['tn']);
			if(isset($params['type']) && $params['type'] == -1){
				unset($params['type']);
				unset($params['briefreason']);
			}
			if(isset($params['briefreason']) && $params['briefreason'] == -1){
				unset($params['briefreason']);
			}
			if(isset($params['source']) && $params['source'] == -1){
				unset($params['source']);
			}
			if(isset($params['recordtime']) && empty($params['recordtime'])){
				unset($params['recordtime']);
			}
			$query = "";
			foreach($params as $k=>$v){
				$query = $query."$k=$v&";
			}
			if($tn == 0){
				$tn = AccountModel::total($username,$params) / $rn;
				$tn = ceil($tn);
				$tn = $tn==0?1:$tn;
			}
			$sn = $sn > $tn ? $tn : $sn;
			if(isset($params['briefreason'])){
				$reflection =  array("income1"=>"工资",
								 "income2"=>"奖金",
								 "income3"=>"理财",
								 "income4"=>"其他",
								 "income5"=>"转入",
								 "consume1"=>"房租",
								 "consume2"=>"定存",
								 "consume3"=>"餐费",
								 "consume4"=>"水电",
								 "consume5"=>"购物",
								 "consume6"=>"充电",
								 "consume7"=>"孝敬父母",
								 "consume8"=>"其他",
								 "consume9"=>"转出",
								 );
				$params['briefreason'] = $reflection[$params['briefreason']];
			}
			if(isset($params['source'])){
				$sourcereflection = array(
								"card" => "银行卡",
								"cash" => "现金",
								"online" => "在线支付",
				);
				$params['source'] = $sourcereflection[$params['source']];
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
			$this->getView()->display("account/ListAccount.phtml",
									array("result" => $results,
										  "query" => $query,
										  "sn" => $sn,
										  "rn" => $rn,
										  "tn" => $tn));
		}
	}
?>


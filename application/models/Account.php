<?php
	class AccountModel{
		public static function add($username,$params){
			$tablename = md5($username)."_detail";
			$result = Utils_Database::getInstance()->add($tablename,$params);
			return $result;
		}
		public static function del($username,$params){
			$tablename = md5($username)."_detail";
			$result = Utils_Database::getInstance()->first($tablename,$params);
			$id = $result['id'];
			$flag = false;
			if($result['briefreason']=="转入"){
				$t_id = $id - 1;
				$t_result = Utils_Database::getInstance()->first($tablename,array("id"=>$t_id));
				if(!empty($t_result) && is_array($t_result)){
					$flag = self::delonce($tablename,$t_result);
				}
			}else if($result['briefreason']=="转出"){
				$t_id = $id + 1;
				$t_result = Utils_Database::getInstance()->first($tablename,array("id"=>$t_id));
				if(!empty($t_result) && is_array($t_result)){
					$flag = self::delonce($tablename,$t_result);
				}
			}else{
				$flag = true;
			}
			if($flag){
				$flag = self::delonce($tablename,$result);
			}
			return $flag;
		}
		private static function delonce($tablename,$result){
			$reflectionsource = array("在线支付"=>"online","现金"=>"cash","银行卡"=>"card");
			$id = $result['id'];
			$source = $result['source'];
			$type = $result['type'];
			$cost = $result['cost'];
			$cost = $type==0?-$cost:$cost;
			$sql = "update $tablename set balance=balance+$cost where id>$id";
			$res = Utils_Database::getInstance()->executeUpdate($sql);
			$sql = "update $tablename set singlebalance=singlebalance+$cost where id>$id and source='$source'";
			$res = $res && Utils_Database::getInstance()->executeUpdate($sql);
			if($res){
				$res = Utils_Database::getInstance()->del($tablename,array("id"=>$id));
				$_SESSION['balance'] += $cost;
				$_SESSION[$reflectionsource[$source]] +=$cost;
			}
			return $res;
		}
		public static function modify($username,$params,$conditions){
			$tablename = md5($username)."_detail";
			$result = Utils_Database::getInstance()->first($tablename,$conditions);
			if($params['type'] != $result['type']){
				return false;
			}
			$id = $result['id'];
			$flag = false;
			if($result['briefreason']=="转入"){
				return false;
			/*	$t_id = $id - 1;
				$t_result = Utils_Database::getInstance()->first($tablename,array("id"=>$t_id));
				if(!empty($t_result) && is_array($t_result)){
					$t_params = array();
					foreach($t_result as $k=>$v){
						if($k=="id"||$k=="balance"||$k=="singlebalance"){
							continue;
						}
						$t_params[$k] = $v;
						if($k=="cost"){
							$t_params[$k] = $params[$k];
						}
					}
					$flag = self::modifyonce($tablename,$t_result,$t_params);
				}*/
			}else if($result['briefreason']=="转出"){
				$t_id = $id + 1;
				$t_result = Utils_Database::getInstance()->first($tablename,array("id"=>$t_id));
				if(!empty($t_result) && is_array($t_result)){
					$t_params = array();
					foreach($t_result as $k=>$v){
						if($k=="id"||$k=="balance"||$k=="singlebalance"){
							continue;
						}
						$t_params[$k] = $v;
						if($k=="cost"){
							$t_params[$k] = $params[$k];
						}
					}
					$flag = self::modifyonce($tablename,$t_result,$t_params);
				}
			}else{
				$flag = true;
			}
			if($flag){
				$flag = self::modifyonce($tablename,$result,$params);
			}
			return $flag;
		}
		public static function modifyonce($tablename,$result,$params){
			$id = $result['id'];
			$type = $result['type'];
			$cost = $result['cost'];
			$source = $result['source'];
			if($type == $params['type']){
				unset($params['type']);
			}
			if($cost == $params['cost']){
				unset($params['cost']);
			}
			if($source == $params['source']){
				unset($params['source']);
			}
			if(isset($params['source'])){
				if(isset($params['cost'])&&isset($params['type'])){
					$o_cost = $type==0?-$cost:$cost;
					$n_cost = $params['type']==0?$params['cost']:-$params['cost'];
					$cost = $type==0?-$cost-$params['cost']:$cost+$params['cost'];
				}elseif(isset($params['cost'])){
					$o_cost = $type==0?-$cost:$cost;
					$n_cost = $type==0?$params['cost']:-$params['cost'];
					$cost = $type==0?$params['cost']-$cost:$cost-$params['cost'];
				}elseif(isset($params['type'])){
					$o_cost = $type==0?-$cost:$cost;
					$n_cost = $params['type']==0?$cost:-$cost;
					$cost = $type==0?-2*$cost:2*$cost;
				}else{
					$o_cost = $type==0?-$cost:$cost;
					$n_cost = $type==0?$cost:-$cost;
					$cost = 0;
				}
				$cost = number_format($cost,2,'.','');
				$n_cost = number_format($n_cost,2,'.','');
				$o_cost = number_format($o_cost,2,'.','');
				$sql = "update $tablename set balance=balance+$cost where id >= $id";
				$params['balance'] = $result['balance'] + $cost;
				Utils_Database::getInstance()->executeUpdate($sql);
				$sql = "update $tablename set singlebalance=singlebalance+$o_cost where id>=$id and source='$source'";
				Utils_Database::getInstance()->executeUpdate($sql);
				$sql = "update $tablename set singlebalance=singlebalance+$n_cost where id>=$id and source='".$params['source']."'";
				Utils_Database::getInstance()->executeUpdate($sql);
				$reflectionsource = array("在线支付"=>"online","现金"=>"cash","银行卡"=>"card");
				$_SESSION[$reflectionsource[$source]] +=$o_cost;
				$_SESSION[$reflectionsource[$params['source']]] +=$n_cost;
				$_SESSION['balance'] += $cost;
				$sql = "select * from $tablename where id<$id and source='".$params['source']."' order by id desc";
				$r = Utils_Database::getInstance()->executeQuery($sql);
				$r = $r[0];
				$params['singlebalance'] = $r['singlebalance'] + $n_cost;
			}else{
				if(isset($params['cost'])&&isset($params['type'])){
					$cost = $type==0?-$cost-$params['cost']:$cost+$params['cost'];
				}else if(isset($params['type'])){
					$cost = $type==0?-2*$cost:2*$cost;
				}else if(isset($params['cost'])){
					$cost = $type==0?$params['cost']-$cost:$cost-$params['cost'];
				}else{
					$cost = 0;
				}
				$cost = number_format($cost,2,'.','');
				$sql = "update $tablename set balance=balance+$cost where id >= $id";
				$params['balance'] = $result['balance'] + $cost;
				$params['singlebalance'] = $result['singlebalance'] + $cost;
				Utils_Database::getInstance()->executeUpdate($sql);
				$sql = "update $tablename set singlebalance=singlebalance+$cost where id>$id and source='$source'";
				Utils_Database::getInstance()->executeUpdate($sql);
				$_SESSION['balance'] += $cost;
				$reflectionsource = array("在线支付"=>"online","现金"=>"cash","银行卡"=>"card");
				$_SESSION[$reflectionsource[$source]] +=$cost;
				$_SESSION[$reflectionsource[$params['source']]] -=$cost;
			}
			$result = Utils_Database::getInstance()->modify($tablename,$params,array("id" => $id));
			return $result;
		}
		public static function query($username,$params,$sn,$rn){
			$tablename = md5($username)."_detail";
			$result = Utils_Database::getInstance()->page($tablename,$params,$sn,$rn);
			return $result;
		}
		public static function total($username,$params){
			$tablename = md5($username)."_detail";
			$total = Utils_Database::getInstance()->total($tablename,$params);
			return $total;
		}
		public static function querySingle($username,$params){
			$tablename = md5($username)."_detail";
			$result = Utils_Database::getInstance()->first($tablename,$params);
			return $result;
		}
	}
?>

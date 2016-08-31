<?php
class ShowAction extends Yaf_Action_Abstract{
    public function execute(){
        $username = $_SESSION['username'];
        $infos = Utils_Database::getInstance()->first("users",array("username" => $username));
        if(empty($infos)){
            $this->responseError("发生未知错误!");
            return;
        }
        $this->getView()->display("user/changeUser.phtml",array("info" => $infos));
    }
    private function responseError($errorMsg){
        $this->getView()->display("error.phtml",array("error" => $errorMsg));
    }
}
?>
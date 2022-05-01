<?php
class Problem_Controller {
    static $db; 
    static $contest_controller;
    static $error_controller;
    static $login_controller;
    static $user_controller;

    function __construct() {
        self::$db=new Database_Controller;
        self::$contest_controller=new Contest_Controller;
        self::$login_controller=new Login_Controller;
        self::$user_controller=new User_Controller;
    }

    /**
     * 以pid列举题目信息 ListProblemByPid
     * @param int $pid 题目id
     * @param bool $strong=false 是否强制执行
     * @return array|null
     */
    static function ListProblemByPid(int $pid,bool $strong=false):array|null {
        $array=self::$db->Query("SELECT * FROM problem WHERE id=$pid"); $res=array(); $array=$array[0]; 
        $uid=self::$login_controller->CheckLogin();
        if ($array["contest"]!=0) {
            $contest=self::$contest_controller->GetContest($array["contest"],$array["contest"]);
            $endtime=$contest[0]["starttime"]+$contest[0]["duration"];
            $tmp=count(self::$db->Query("SELECT id FROM status WHERE ".
            "uid=$uid AND pid=$pid AND time>=".$contest[0]["starttime"]." AND time<=$endtime"));
            if ($tmp>0) $array["accepted"]=1;
            else $array["accepted"]=0;
            if ($endtime<time()) return $array; 
        } if ($strong) return $array;
        if ($array["contest"]==0&&!$array["banned"]&&!$array["hidden"]) return $array; 
        $uid=self::$login_controller->CheckLogin();
        if (!$uid) Error_Controller::Common("Permission denied");
        $uinfo=self::$user_controller->GetWholeUserInfo($uid);
        if ($uinfo["permission"]>1) return $array;
        if ($array["banned"]||$array["hidden"]) Error_Controller::Common("Permission denied");
        if ($array["contest"]!=$_GET["contest"]) Error_Controller::Common("Permission denied");
        if (self::$contest_controller->JudgeSignup($array["contest"])==false) 
        Error_Controller::Common("Permission denied");
        $info=self::$contest_controller->GetContest($array["contest"]);
        if ($info["starttime"]>time()) Error_Controller::Common("Permission denied");
        $tmp=count(self::$db->Query("SELECT id FROM status WHERE uid=$uid AND pid=$pid"));
        if ($tmp>0) $array["accepted"]=1;
        else $array["accepted"]=0;
        return $array;
    }

    /**
     * 以现存题目列举题目信息 ListProblemByNumber
     * @param float $l=1 题目数量的左边界
     * @param float $r=1e18 题目数量的右边界
     * @return array|null
     */
    static function ListProblemByNumber(float $l=1,float $r=1e18,string $key="",array|null $tag,array|null $diff,float& $num):array|null {
        $diffs=" AND ("; for ($i=0;$i<count($diff);$i++) $diffs.=($i!=0?"OR ":"")."difficult=".$diff[$i]." "; $diffs.=")"; 
        $array=self::$db->Query("SELECT * FROM problem WHERE ".($key!=""?"name LIKE '%$key%' AND ":"")."banned=0 AND hidden=0".(count($diff)?$diffs:""));
        for ($i=0;$i<count($array);$i++) {
            if ($array[$i]["contest"]==0) continue;
            $contest=self::$contest_controller->GetContest($array[$i]["contest"],$array[$i]["contest"]);
            $endtime=$contest[0]["starttime"]+$contest[0]["duration"];
            if ($endtime>=time()){array_splice($array,$i,1);$i--;}
        }
        if ($tag==null) {
            $res2=array();
            for ($i=$l-1;$i<count($array)&&$i<$r;$i++) $res2[]=$array[$i];
            $num=count($array); 
            $login_controller=new Login_Controller;
            $uid=$login_controller->CheckLogin();
            for ($i=0;$i<count($res2);$i++) {
                $tmp=self::$db->Query("SELECT id FROM status WHERE pid=".$res2[$i]["id"]." AND uid=$uid AND status='Accepted'");
                if (count($tmp)>0) $res2[$i]["accepted"]=1;
                else $res2[$i]["accepted"]=0;
            } 
            return $res2;
        }
        $tags=" WHERE ("; for ($i=0;$i<count($tag);$i++) $tags.=($i!=0?"OR ":"")."tagname='".$tag[$i]."' "; $tags.=") AND type='problem'"; 
        $array2=self::$db->Query("SELECT id FROM tags".(count($tag)?$tags:""));
        $a=array(); for ($i=0;$i<count($array2);$i++) $a[]=$array2[$i]["id"];
        $res=array(); for ($i=0;$i<count($array);$i++) {
            if (array_search($array[$i]["id"],$a)===false) ;
            else $res[]=$array[$i];
        } $res2=array(); $num=count($res);
        for ($i=$l-1;$i<count($res)&&$i<$r;$i++) $res2[]=$res[$i];
        $login_controller=new Login_Controller;
        $uid=$login_controller->CheckLogin();
        for ($i=0;$i<count($res2);$i++) {
            $tmp=self::$db->Query("SELECT id FROM status WHERE pid=".$res2[$i]["id"]." AND uid=$uid AND status='Accepted'");
            if (count($tmp)>0) $res2[$i]["accepted"]=1;
            else $res2[$i]["accepted"]=0;
        } return $res2;
    }

    /**
     * 获取题目总数 GetProblemTotal
     * @return int
     */
    static function GetProblemTotal():int {
        $array=self::$db->Query("SELECT * FROM problem WHERE banned=0 AND contest=0 AND hidden=0");
        return $array==null?0:count($array);
    }

    /**
     * 输出api题目信息 OutputAPIInfo
     * @param int $pid 题目id
     * @return array|null
     */
    static function OutputAPIInfo(int $pid):array|null {
        $arr=self::$db->Query("SELECT * FROM problem WHERE id=$pid");
        $arr=$arr[0];
        return array("pid"=>$arr["id"],"name"=>$arr["name"],
        "hidden"=>$arr["hidden"],"banned"=>$arr["banned"],"difficult"=>$arr["difficult"]);
    }
}
?>
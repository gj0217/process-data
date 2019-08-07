<?php
/**
 * Created by PhpStorm.
 * User: guanjing@zuoyebang.com
 * Date: 2019/6/13
 * Time: 17:38
 */

Bd_Init::init();
set_time_limit(0);
ignore_user_abort(true);

class LianBao{
    private $file = '';
    static public $grade = array(
        1 => '小学',
        11 => '一年级',
        12 => '二年级',
        13 => '三年级',
        14 => '四年级',
        15 => '五年级',
        16 => '六年级',
        2 => '初一',
        3 => '初二',
        4 => '初三',
        5 => '高一',
        6 => '高二',
        7 => '高三',
    );
    static public $subject = array(
        1 => '语文',
        2 => '数学',
        3 => '英语',
        4 => '物理',
        5 => '化学',
        6 => '生物',
        7 => '政治',
        8 => '历史',
        9 => '地理',
    );
    static public $gradeSubjectMap = array(
        1   => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        11  => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        12  => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        13  => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        14  => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        15  => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        16  => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        2  => array(
            1=> "1",
            2=> "2",
            3=> "3",
        ),
        3  => array(
            1=> "1",
            2=> "2",
            3=> "3",
            4=> "4",
        ),
        4  => array(
            1=> "1",
            2=> "2",
            3=> "3",
            4=> "4",
            5=> "5",
        ),
        5  => array(
            1=> "1",
            2=> "2",
            3=> "3",
            4=> "4",
            5=> "5",
            6=> "6",
            7=> "7",
            8=> "8",
            9=> "9",
        ),
        6  => array(
            1=> "1",
            2=> "2",
            3=> "3",
            4=> "4",
            5=> "5",
            6=> "6",
            7=> "7",
            8=> "8",
            9=> "9",
        ),
        7  => array(
            1=> "1",
            2=> "2",
            3=> "3",
            4=> "4",
            5=> "5",
            6=> "6",
            7=> "7",
            8=> "8",
            9=> "9",
        ),
    );
    public function __Construct(){
        $this->_dbDar = Hk_Service_Db::getDB('zb/zb_dar');
        $this->_dbDak = Hk_Service_Db::getDB('zb/zb_dak');
        if(empty($this->_dbDar)||empty($this->_dbDak)){
            echo "数据库初始化失败\n";exit;
        }
        $this->file = '/home/homework/guanjing/kuokeUser.txt';
    }
    public function execute(){
        // 创建目标文件并写入表头
        $startStr = "uid\t年级\t已购科目\t扩科科目\r\n";
        $rs = file_put_contents($this->file,$startStr,FILE_APPEND);
        if(empty($rs)){
            echo "写入头文件失败\n";exit;
        }
        $resStr = '';
        // 查询符合条件的user_id

        for($i=0;$i<100;$i++){
            $tblNewTrade = "tblNewTrade$i";
            echo "开始查询第{$i}张表\r\n";
            $sql3 = "select user_id, biz_info_group from {$tblNewTrade} where status=1 and create_time>1553011200 and create_time<1560996000 and (biz_info_group like '%subBizType\":\"2\"%' or biz_info_group like '%subBizType\":\"1\"%')";
//        $sql3 = "select user_id, biz_info_group from {$tblNewTrade} where user_id = 2311303627 and status=1 and create_time>1553616000 and biz_info_group like '%subBizType\":\"2\"%' or biz_info_group like '%subBizType\":\"1\"'";
            $res2 = $this->_dbDar->query($sql3);
            foreach ($res2 as $value){
                $user_id = $value['user_id'];
                $biz_info_group = json_decode($value['biz_info_group'],true);
                $arr2 =$biz_info_group[0][0]["skuIdList"];
                if(isset($arr2)){
                    $skuIdList = $arr2;
                }
                $skuIdStr = implode(',',$skuIdList);
                $sql4 = "select grade, subject from tblSKUList where sku_id in ($skuIdStr)";
                $res3 = $this->_dbDak->query($sql4);
                $gradeCode = explode(',', $res3[0]['grade']);
                $subjectCode = $res3[0]['subject'];
                $infoArr[]= array('user_id'=>$user_id, 'grade'=>$gradeCode[1], 'subject'=>$subjectCode[1]);
            }
            $infoFinal = array();
            foreach($infoArr as $val){
                $key = $val['user_id'].'_'.$val['grade'];
                if(!isset($infoFinal[$key])){
                    $infoFinal[$key] = $val;
                }else{
                    $infoFinal[$key]['subject'] =$infoFinal[$key]['subject'].$val['subject'];
                }
            }
            unset($infoArr);
            $count = 0;
            foreach ($infoFinal as $value){
                $user_id = (int)$value["user_id"];
                $gradeArr = self::$grade[$value["grade"]];
                $subjectArr = str_split($value["subject"]);
                $allSubject = self::$gradeSubjectMap[$value["grade"]];
                foreach($subjectArr as $value2){
                    $subjectInfo[] = self::$subject[$value2];
                }
//                $subjectInfo = array_unique($subjectInfo);
                $subjectFinal = implode(",",$subjectInfo);
                $noSubjectCode = array_diff($allSubject,$subjectArr);
                if($noSubjectCode==null){
                    $noSubject = "无";
                }else{
                    foreach ($noSubjectCode as $value3){
                        $noSubjectArr[] = self::$subject[$value3];
                    }
                    $noSubject = implode(",",$noSubjectArr);
                }
                $resStr .= "$user_id\t$gradeArr\t$subjectFinal\t$noSubject\r\n";
                file_put_contents($this->file,$resStr,FILE_APPEND);
                $resStr = '';
                unset($subjectInfo);
                unset($noSubjectArr);
                $count++;
            }
            echo "已查询完第{$i}张表,写入{$count}条记录\r\n";
        }
    }
}
$obj = new LianBao();
$obj->execute();
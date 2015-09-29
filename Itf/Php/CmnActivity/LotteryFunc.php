<?php
/**
* @author  陈一平
* @version 1.0.0
* @description 中奖信息
***********************************************************/

class LotteryFunc {

	static public function GetLotteryCfg($key) {
        $_value = DB::getFieldValue("select cfg_value from lot_sys_lottery_cfg where cfg_key='" . $key . "'");
        if ($_value=="") {
            $_desc = "";
            $_memo = "";
            switch ($key) {
                case "LotteryPloy":
                    $_value = "NoLimit";
                    $_desc = "抽奖策略";
                    $_memo = "NoLimit:没有限制;OnlyWinOnceAllPool:所有奖池能中一次;OnlyWinOncePerPool:每个奖池只能中一次";
                    break;
                case "AutoForbidIPQtyLimit":
                    $_value = "500";
                    $_desc = "IP访问数量限制（自动禁用）";
                    $_memo = "IP访问数量限制,例如：500";
                    break;
                case "MaxWinQtyPerIP":
                    $_value = "1";
                    $_desc = "同一IP中奖最多中奖次数";
                    $_memo = "中奖最多中奖次数,例如：1";
                    break;
                case "MonitorMailList":
                    $_value = "";
                    $_desc = "抽奖监测Email";
                    $_memo = "用分号分隔";
                    break;
                case "MonitorInterval":
                    $_value = "10";
                    $_desc = "抽奖监测时间间隔";
                    $_memo = "单位分钟";
                    break;
                case "IsPause":
                    $_value = "0";
                    $_desc = "是否暂停抽奖";
                    $_memo = "0：否；1：是";
                    break;
                default:
                    $_value = $key;
                    $_desc = $key;
                    break;
            }

            DB::execSql("insert into lot_sys_lottery_cfg(cfg_key,cfg_value,cfg_desc,memo) values('" . $key . "','" . $_value . "','" . $_desc . "','" . $_memo . "')");
        }
        return $_value == $key ? "" : $_value;
    }

    /// <summary>
    /// 删除抽奖配置
    /// </summary>
    /// <param name="key"></param>
    /// <returns></returns>
    static public function DeleteLotteryCfg($key) {
        DB::execSql("delete from lot_sys_lottery_cfg where cfg_key='" . $key . "'");
    }        

    //--------------------------------------------------------------
    /// <summary>
    /// 设置系统配置
    /// </summary>
    /// <param name="key">配置主键</param>
    /// <param name="value">键值</param>
    /// <returns></returns>
    static public function SetLotterCfg($key, $value) {
        if (trim(DB::getFieldValue("select * from lot_sys_lottery_cfg  where cfg_key='" . $key . "'")) == "") {
            return DB::execSql("insert into lot_sys_lottery_cfg(cfg_key,cfg_value) values('" . $key . "','" . $value . "')");
        }
        else {
            return DB::execSql("update lot_sys_lottery_cfg set cfg_value='" . $value . "' where cfg_key='" . $key . "'");
        }
    }
}

?>
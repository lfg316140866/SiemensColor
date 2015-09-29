<?php
class MonitorThread {
	
	public function Execute() {
	$_dt = DB::getResultSet("
                select p.prize_id,prize_desc,prize_count,win_prize_probability,surplus_prize_count,max_qty_per_day,ifnull(l1.qty,0) total_win_qty,ifnull(l2.qty,0) normal_win_qty,DATE_FORMAT(day_start_time,'%H%i%s') day_start_time,DATE_FORMAT(day_end_time,'%H%i%s') day_end_time,lottery_type
                from  lot_bas_prize p 
                left join (
                    select prize_id,count(*) qty
                    from lot_prize_rec
                    where cmn_createdate>DATE_FORMAT(now(),'%Y-%m-%d %H:%i:%s')
                    group by prize_id
                ) l1 on p.prize_id=l1.prize_id
                left join (
                    select prize_id,count(*) qty
                    from lot_prize_rec
                    where cmn_createdate>DATE_FORMAT(now(),'%Y-%m-%d %H:%i:%s')
	                  and state=1
                    group by prize_id
                ) l2 on p.prize_id=l2.prize_id" );
	
	$_state = 0; // 0:正常；1：异常；2：警告
	$_content = "";
	$_allMaxQtyPerDay = 0;
	$_allTotalWinQty = 0;
	$_allSurplusPrizeCount = 0;
	if($_dt!=null && mysql_num_rows($_dt)>0) {
		while($_dr = mysql_fetch_array($_dt,MYSQL_ASSOC)){
	
	
		$_state = 0;
		$PrizeID = $_dr ["PrizeID"];
		$PrizeDesc = $_dr ["PrizeDesc"];
		$PrizeCount = $_dr ["PrizeCount"];
		$SurplusPrizeCount = $_dr ["SurplusPrizeCount"];
		$WinPrizeProbability = $_dr ["WinPrizeProbability"];
		$DayStartTime = $_dr ["DayStartTime"];
		$DayEndTime = $_dr ["DayEndTime"];
		$MaxQtyPerDay = $_dr["MaxQtyPerDay"];
        $TotalWinQty = $_dr["TotalWinQty"];
        $NormalWinQty = $_dr["NormalWinQty"];
        
		$AbNormalWinQty = $TotalWinQty - $NormalWinQty;
		$_allMaxQtyPerDay += MaxQtyPerDay;
		$_allTotalWinQty += TotalWinQty;
		$_allSurplusPrizeCount += SurplusPrizeCount;
		
		if ($_content == "") {
			$_content .= "<table style='text-align: center;'>";
			$_content .= "<tr>\n";
			$_content .= "<td>奖品代码</td><td>奖品名称</td><td>奖品总数</td><td>剩余数量</td><td>中奖概率</td><td>每天开始时间</td><td>每天结束时间</td><td>每天最大中奖数量</td><td>当天中奖总数</td><td>当天中奖数量（正常）</td><td>当天中奖数量（异常）</td><td>是否正常</td>\n";
			$_content .= "</tr>\n";
		}
		
		if ($AbNormalWinQty > $MaxQtyPerDay || $NormalWinQty > $MaxQtyPerDay) {
			$_state = 1;
		}
		
		$_content .= "<tr><td>" . $PrizeID . "</td><td>" . $PrizeDesc . "</td><td>" . $PrizeCount . "</td><td>" . $SurplusPrizeCount . "</td><td>" . $WinPrizeProbability . "</td><td>" . $DayStartTime . "</td><td>" . $DayEndTime . "</td><td>" . $MaxQtyPerDay . "</td><td>" . $TotalWinQty . "</td><td>" . $NormalWinQty . "</td><td>" . $AbNormalWinQty . "</td><td>" . GetStateDesc ( $_state ) . "</td></tr>\n";
	}
	$_content .= "</table>";
	
	if ($_state != 1 && (DateTime . Now . Hour * 1.00) / 24 > (_allTotalWinQty * 1.00) / (_allMaxQtyPerDay * 1.00)) {
		$_state = 2;
	}
	
	$_toMail = "519608365@qq.com;309846288@qq.com;471960765@qq.com;";
	$_toMail .= LotteryFunc::GetLotteryCfg ( "MonitorMailList" );
	
	$_title = "[抽奖程序" . $this->GetStateDesc ( _state ) + "]" + Func::GetSysCfg ( "SysName" ) + " 抽奖程序监测报告";
	
	if (_state == 1) {
		$_ret = LotteryFunc::SendeMail ( $_toMail, $_title, $_content );
		Log::WriteToFile ( "LotteryMonitorThread", "监测邮件发送" + (_ret ? "成功" : "失败") );
	} else if (_state == 2 && DateTime . Now . Minute < Interval / 1000 / 60) {
		$_ret = LotteryFunc::SendeMail ( $_toMail, $_title, $_content );
		Log::WriteToFile ( "LotteryMonitorThread", "监测邮件发送" + (_ret ? "成功" : "失败") );
	} else if (DateTime . Now . Hour % 6 == 0 && DateTime . Now . Minute < Interval / 1000 / 60) {
		$_ret = LotteryFunc::SendeMail ( $_toMail, $_title, $_content );
		Log::WriteToFile ( "LotteryMonitorThread", "监测邮件发送" + (_ret ? "成功" : "失败") );
	}
	
	$_mem_WinPrizeRecError = "";
	$_dt = DB::getResultSet ( "select WinPrizeRecID,UserID,PrizePoolID,PrizeID,SerialNoID,LotteryRecID,PrizeRecID,WinPrizeDate,Memo from lot_mem_WinPrizeRec where HasSendMail=0" );
	if($_dt!=null && mysql_num_rows($_dt)>0) {
		while($_dr = mysql_fetch_array($_dt,MYSQL_ASSOC)){
			$_winPrizeRecID = $_dr ["WinPrizeRecID"];
			$_userID = $_dr ["UserID"];
			$_prizePoolID = $_dr ["PrizePoolID"];
			$_prizeID = $_dr ["PrizeID"];
			$_serialNoID = $_dr ["SerialNoID"];
			$_lotteryRecID = $_dr ["LotteryRecID"];
			$_prizeRecID = $_dr ["PrizeRecID"];
			$_winPrizeDate = $_dr ["WinPrizeDate"];
			$_memo = $_dr ["Memo"];
			
			$_mem_WinPrizeRecError = "WinPrizeRecID:" + $_winPrizeRecID + "<br>";
			$_mem_WinPrizeRecError .= "UserID:" + $_userID + "<br>";
			$_mem_WinPrizeRecError .= "PrizePoolID:" + $_prizePoolID + "<br>";
			$_mem_WinPrizeRecError .= "PrizeID:" + $_prizeID + "<br>";
			$_mem_WinPrizeRecError .= "SerialNoID:" + $_serialNoID + "<br>";
			$_mem_WinPrizeRecError .= "LotteryRecID:" + $_lotteryRecID + "<br>";
			$_mem_WinPrizeRecError .= "PrizeRecID:" + $_prizeRecID + "<br>";
			$_mem_WinPrizeRecError .= "WinPrizeDate:" + $_winPrizeDate + "<br>";
			$_mem_WinPrizeRecError .= "Memo:" + $_memo + "<br><br><br><br>";
			
			DB::execSql ( "update lot_mem_win_prize_temp set has_send_mail=1 where WinPrizeRecID=" + $_winPrizeRecID );
		}
	}
	
	if (_mem_WinPrizeRecError != "") {
		$_mem_WinPrizeRecError = "抽奖异常记录缓存表[lot_mem_WinPrizeRec]出现如下异常：<br>" + $_mem_WinPrizeRecError;
		$_title = "[抽奖程序异常]" + Cmn . Mis . Func . GetSysCfg ( "SysName" ) + " 抽奖程序监测报告";
		$_ret = Func . SendeMail ( _toMail, $_title, $_mem_WinPrizeRecError );
		Log::WriteToFile ( "LotteryMonitorThread", "监测邮件发送" + (_ret ? "成功" : "失败") );
	}
}

        private function GetStateDesc($state) {
            if ($state == 0) {
                return "正常";
            }
            else if ($state == 1) {
                return "异常";
            }
            else {
                return "警告(中奖数量不足按小时平均数量)" ;
            } 
        }
}
?>
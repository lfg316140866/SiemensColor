<?php
/**
* @author 陈一平
* @version 1.0.0
* @description 抽奖逻辑
***********************************************************/
require_once('LotteryFunc.php');
require_once('PrizeInfo.php');

@session_start(); //启用session

class Lottery{

	const LotteryPloy_NoLimit="NoLimit";
	const LotteryPloy_OnlyWinOnceAllPool="OnlyWinOnceAllPool";
	const LotteryPloy_OnlyWinOncePerPool="OnlyWinOncePerPool";

	/// <summary>
    /// 用户代码
    /// </summary>
    public $UserID = "";

    /// <summary>
    /// 是否调试模式
    /// </summary>
    public $IsDebug = "1";

    /// <summary>
    /// 抽奖策略  NoLimit，OnlyWinOnceAllPool，OnlyWinOncePerPool
    /// </summary>
    public $LotteryPloy = "";//LotteryFunc::GetLotteryCfg("LotteryPloy");
    
    /// <summary>
    /// 同一IP访问次数限制
    /// </summary>
    public $AutoForbidIPQtyLimit = "";//LotteryFunc::GetLotteryCfg("AutoForbidIPQtyLimit");

    /// <summary>
    /// 同一IP最多中奖次数
    /// </summary>
    public $MaxWinQtyPerIP = "";//LotteryFunc::GetLotteryCfg("MaxWinQtyPerIP");

	/// <summary>
    /// 附加条件
    /// </summary>
    public $AdditionalCondition = "";

    /// <summary>
    /// 中奖信息
    /// </summary>
    private $WinPrizeInfo;

    /// <summary>
    /// 能抽奖奖品ID列表
    /// </summary>
    public $CanLotteryPrizeIDList = array();

    /// <summary>
    /// 是否暂停
    /// </summary>
    public $IsPause = "";//LotteryFunc::GetLotteryCfg("IsPause");

    /// <summary>
    /// 最大抽奖数量
    /// </summary>
    public static $MaxLotterCount = 50;

    /// <summary>
    /// 抽奖数量
    /// </summary>
    public static $LotteryCount = 0;

    /// <summary>
    /// 先到先得
    /// </summary>
    public $FirstComeFirstServed = "0";

    /// <summary>
    /// 自动生成SerialNo
    /// </summary>
    public $AutoCreateSerialNo = "0";

    /// <summary>
    /// 默认中奖的奖品代码
    /// </summary>
    private $DefaultPrizeID = "";

    /// <summary>
    /// 奖品池代码
    /// </summary>
    private $PrizePoolIDs = array();


    /// <summary>
    /// 构造函数
    /// </summary>
	public function __construct($userID, $listPrizePoolID) {

		$this->LotteryPloy = LotteryFunc::GetLotteryCfg("LotteryPloy");
		$this->AutoForbidIPQtyLimit = LotteryFunc::GetLotteryCfg("AutoForbidIPQtyLimit");
		$this->MaxWinQtyPerIP = LotteryFunc::GetLotteryCfg("MaxWinQtyPerIP");
		$this->IsPause = LotteryFunc::GetLotteryCfg("IsPause");
		$this->WinPrizeInfo = new PrizeInfo();

		foreach ($listPrizePoolID as $prizePoolID){
			array_push($this->PrizePoolIDs,$prizePoolID);
		}
        $this->DefaultPrizeID = DB::getFieldValue("select default_prize_id from lot_bas_prize_pool where prize_pool_id in (" . implode(",", $this->PrizePoolIDs) . ")");
        $this->UserID = $userID;

        // 设定能中奖奖品
        $_rs = DB::getResultSet("select prize_id from lot_bas_prize where prize_pool_id in (" . implode(",", $this->PrizePoolIDs) . ") order by prize_id");
        if ($_rs != null && mysql_num_rows($_rs)>0) {
            while($_row = mysql_fetch_array($_rs,MYSQL_ASSOC)){
                array_push($this->CanLotteryPrizeIDList,$_row["prize_id"]);
			}
        }
	}

	/// <summary>
    /// 执行抽奖逻辑
    /// </summary>
    /// <param name="json"></param>
    public function Execute() {

        if ($this->UserID == "") {
        	Log::WriteToFile("Lottery", "用户代码为空！" );
            return $this->WinPrizeInfo;
        }
        
        $_tmp = DB::getFieldValue("select prize_pool_id from lot_bas_prize group by prize_pool_id having sum(win_prize_probability) > 100");
        if ($_tmp != "") {
        	Log::WriteToFile("Lottery", "奖池（" . _tmp . "）的中奖概率总和超过100！");
        	return $this->WinPrizeInfo;
        }

        // 是否暂停
        if ($this->IsPause == "1") {
        	Log::WriteToFile("Lottery", "抽奖已暂停！" );
            return $this->WinPrizeInfo;
        }

        // IP访问数量限制
        $_ip = Safe::GetIp();

        /*
        $_sqlIP = "
            declare @LotteryIPID int
            declare @Qty int
            declare @LastAccessTime datetime
            declare @State int
            declare @WinQty int
            set @LotteryIPID = null
            set @Qty = null
            set @LastAccessTime = null
            set @State = null
            set @WinQty = null
            select @LotteryIPID=LotteryIPID,@Qty=Qty,@LastAccessTime=LastAccessTime,@State=State,@WinQty=WinQty from lot_sys_LotteryIP where IP='" . $_ip . "'
            if @LotteryIPID is null begin
                insert into lot_sys_LotteryIP(IP,Qty) values ('" . $_ip . "', 1)
                select @LotteryIPID=SCOPE_IDENTITY()
                select @LotteryIPID LotteryIPID,@WinQty WinQty,0 IsIpAbnormal
            end else begin
                if @State = 0 and @Qty>=" . $this->AutoForbidIPQtyLimit . " begin
                    update lot_sys_LotteryIP set Qty=Qty.1,LastAccessTime=getdate(),State=1 where LotteryIPID=@LotteryIPID
                    select @LotteryIPID LotteryIPID,@WinQty WinQty,1 IsIpAbnormal
                end else begin
                    update lot_sys_LotteryIP set Qty=Qty.1,LastAccessTime=getdate() where LotteryIPID=@LotteryIPID
                    select @LotteryIPID LotteryIPID,@WinQty WinQty,0 IsIpAbnormal
                end
            end";

            */
        //call lottery_check_ip(in p_ip varchar(50), in p_auto_forbid_ip_qty_limit int)
        $_sqlIP = "call lottery_check_ip('" . $_ip . "'," . $this->AutoForbidIPQtyLimit . ")";
        Log::WriteToFile("ss", $_sqlIP);

        $_lotteryIPID = "";
        $_winQty = "999";
        $_isIpAbnormal = "1";
        
        $_rs = DB::getResultSet($_sqlIP);

        if($_rs!=null && mysql_num_rows($_rs)>0) {
        	while($_row = mysql_fetch_array($_rs,MYSQL_ASSOC)){
        		$_lotteryIPID = $_row["lottery_ip_id"];
            	$_winQty = $_row["win_qty"];
            	$_isIpAbnormal = $_row["is_ip_abnormal"];
        	}
        }

        // IP异常
        if ($_isIpAbnormal == "1") {
            Log::WriteToFile("Lottery", "IP异常！" . $_ip);
            $this->WinPrizeInfo->PrizeID = $this->DefaultPrizeID;
            return $this->WinPrizeInfo;
        }

        // 
        if ($_winQty >= $this->MaxWinQtyPerIP) {
            Log::WriteToFile("Lottery", "同一IP超过中奖次数！" . $this->MaxWinQtyPerIP);
            $this->WinPrizeInfo->PrizeID = $this->DefaultPrizeID;
            return $this->WinPrizeInfo;
        }

        // 不能参与抽奖的PrizePoolID
        $_canNotLotteryPrizePoolIDs = array();
        array_push($_canNotLotteryPrizePoolIDs, "-1");

        // 抽奖策略
        if ($this->LotteryPloy != Lottery::LotteryPloy_NoLimit) {
            $_cnt = "";
            if ($this->LotteryPloy == Lottery::LotteryPloy_OnlyWinOnceAllPool) {
                $_cnt = DB::getFieldValue("select count(prize_rec_id) from lot_prize_rec where user_id=" . $this->UserID);
                if ($_cnt == "") { // 发生异常
                    $this->WinPrizeInfo->PrizeID = $this->DefaultPrizeID;
                    return $this->WinPrizeInfo;
                }
            }
            else if ($this->LotteryPloy == Lottery::LotteryPloy_OnlyWinOncePerPool) {
                $_rs = DB::getResultSet("select prize_pool_id, count(prize_rec_id) cnt from lot_prize_rec where user_id=" . $this->UserID . " and prize_pool_id in (" . implode(",", $this->PrizePoolIDs) . ") group by prize_pool_id");
                if($_rs!=null && mysql_num_rows($_rs)>0) {
                    while($_row = mysql_fetch_array($_rs,MYSQL_ASSOC)){
                        array_push($_canNotLotteryPrizePoolIDs,$_row["prize_pool_id"]);
                    }
                }
                if (count($this->PrizePoolIDs) == count($_canNotLotteryPrizePoolIDs)) {
                    return $this->WinPrizeInfo;
                }
            }
        }

        // 能抽奖奖品条件
        $_condition = " and p.prize_id = -1";
        for ($_i = 0; $_i < count($this->CanLotteryPrizeIDList); $_i++) {
            if ($_i == 0) {
                $_condition = " and p.prize_id in (";
            }
            if ($_i < count($this->CanLotteryPrizeIDList) - 1) {
                $_condition .= $this->CanLotteryPrizeIDList[$_i] . ",";
            }
            else {
                $_condition .= $this->CanLotteryPrizeIDList[$_i] . ")";
            }
        }

        // 附加条件
        $_additionalCondition = $this->AdditionalCondition;
        /*
        if ($_additionalCondition != "") {
            $_additionalCondition = (strtolower($this->AdditionalCondition).Contains("and ") ? AdditionalCondition : "and " . AdditionalCondition);
        }
        */

        //允许从中取值，用于奖品抽奖
        $_prizeSql = "
                    select p.prize_pool_id,p.prize_id,prize_desc,prize_image,win_prize_probability*10000 win_prize_probability,prize_count,surplus_prize_count,max_qty_per_day,ifnull(qty,0) qty,lottery_type
                    from lot_bas_prize p 
                    left join (
                        select prize_id,count(*) qty
                        from lot_prize_rec
                        where cmn_createdate>DATE_FORMAT(now(),'%Y-%m-%d %H:%i:%s')
                          and state = 1 
                        group by prize_id
                    ) l on p.prize_id=l.prize_id
                    where ifnull(qty,0) < p.max_qty_per_day
                      and DATE_FORMAT(now(),'%H%i%s') between DATE_FORMAT(p.day_start_time,'%H%i%s') and DATE_FORMAT(p.day_end_time,'%H%i%s')
                      and now() between p.start_date and DATE_ADD(p.end_date,INTERVAL 1 DAY)
                      and p.prize_pool_id in (" . implode(",", $this->PrizePoolIDs) . ")
                      and p.prize_pool_id not in (" . implode(",", $_canNotLotteryPrizePoolIDs) . ")
                      and p.surplus_prize_count > 0
                    " . $_condition . " 
                    " . $_additionalCondition . "
                    order by p.prize_pool_id,p.prize_type,p.win_prize_probability,p.prize_count";

        if ($this->IsDebug) {
            Log::WriteToFile("Lottery", "PrizeSql=" . $_prizeSql);
        }

        $_rs = DB::getResultSet($_prizeSql);

        if($_rs!=null && mysql_num_rows($_rs)>0) {
            $_res = 0;//mt_rand(1, 1000000);

            // 先到先得中奖概率按所有商品的剩余数量的权重来算
            if ($this->FirstComeFirstServed == "1") {
                $_totalSurplusPrizeCount = 0;
                while($_row = mysql_fetch_array($_rs,MYSQL_ASSOC)){
                    $_totalSurplusPrizeCount += $_row["surplus_prize_count"];
                }
                $_res = mt_rand(1, $_totalSurplusPrizeCount);
            }

            if ($this->IsDebug) {
                Log::WriteToFile("Lottery", "Rand=" . $_res);
            }

            $_prizePoolIDBak = "";
            // 中奖概率
            $_winPrizeProbability = 0;
           	// 结果内部的行指针移动到第一行
           	mysql_data_seek($_rs, 0);
            while($_row = mysql_fetch_array($_rs,MYSQL_ASSOC)){
                $_prizePoolID = $_row["prize_pool_id"];
                $_prizID = $_row["prize_id"];
                if ($this->FirstComeFirstServed) { //先到先得中奖概率按所有商品的剩余数量的权重来算
                    $_winPrizeProbability .= $_row["surplus_prize_count"];
                }
                else { //否则按实际设定的概率来算
                	if ($_prizePoolIDBak != $_prizePoolID) {
                		$_res = mt_rand(1, 1000000);
                	}
                    $_winPrizeProbability += $_row["win_prize_probability"];
                }

                if ($_res >= 1 && $_res <= $_winPrizeProbability) {
                    /*
                    $_sql = "declare @PrizeID int
                        declare @UserID int
                        declare @IP varchar(50) 
                        declare @Error int
                        declare @LotteryRecID int
                        declare @PrizeRecID int
                        declare @PrizePoolID int
                        declare @SerialNoID int
                        declare @SerialNo varchar(100)
                        declare @AutoCreateSerialNo  int
    
                        set @Error=0
                        set @PrizeID='" . $_row["PrizeID"] . "'
                        set @UserID='" . $this->UserID . @"'
                        set @IP='" . $_ip . @"'
                        set @PrizePoolID=" . $_prizePoolID . @"
                        set @AutoCreateSerialNo=" . ($this->AutoCreateSerialNo ? "1" : "0") . "
                        declare @errpos varchar(50);
                        set @errpos = '';

                        begin transaction 
                            declare @SurplusPrizeCount int
                            declare @IsGrantSerialNo bit
                            select @SurplusPrizeCount=SurplusPrizeCount,@IsGrantSerialNo=IsGrantSerialNo from lot_bas_Prize where PrizeID=@PrizeID;
                            if(@SurplusPrizeCount<=0) begin 
                                set @Error=@Error.1 
                                set @errpos = '1';
                            end

                            if(@IsGrantSerialNo = 1) begin
                                if (@AutoCreateSerialNo = 1) begin
                                    select @SerialNo=substring(replace(NEWID(), '-', ''),0,12)
                                    insert into lot_dat_SerialNo(SerialNo,PrizeID) values(@SerialNo,@PrizeID)
                                    select @SerialNoID=SCOPE_IDENTITY()
                                    set @Error=@Error.1 
                                    set @errpos = '2-1';
                                end else begin
                                    select top 1 @SerialNoID=SerialNoID,@SerialNo=SerialNo from lot_dat_SerialNo where PrizeID=@PrizeID and IsSend=0 order by SerialNoID
                                    if (@SerialNoID is null) begin
                                        set @Error=@Error.1 
                                        set @errpos = '2-2';
                                    end else begin
                                        update lot_dat_SerialNo set IsSend=1,SendTime=getdate() where SerialNoID=@SerialNoID
                                    end
                                end
                            end

                            update lot_bas_Prize set SurplusPrizeCount = SurplusPrizeCount - 1 where PrizeID = @PrizeID;
                            if (@@error>0) begin
	                            set @Error=@@error.@Error;
                                set @errpos = @errpos . '3';
                            end

                            insert into lot_LotteryRec(UserID,PrizePoolID,PrizeID,SerialNoID,LotteryPrizeDate,IP) 
                            values (@UserID,@PrizePoolID,@PrizeID,@SerialNoID,getdate(),@IP)
                            select @LotteryRecID= SCOPE_IDENTITY()
                            if (@@error>0) begin
	                            set @Error=@@error.@Error;
                                set @errpos = @errpos . '4';
                            end

                            insert into lot_PrizeRec(UserID,PrizePoolID,PrizeID,SerialNoID,LotteryRecID,WinPrizeDate,IP) 
                            values (@UserID,@PrizePoolID,@PrizeID,@SerialNoID,@LotteryRecID,getdate(),@IP)
                            select @PrizeRecID=SCOPE_IDENTITY()
                            if (@@error>0) begin
	                            set @Error=@@error.@Error;
                                set @errpos = @errpos . '5';
                            end

                            update lot_sys_LotteryIP set WinQty=1 where IP=@IP
                            if (@@error>0) begin
	                            set @Error=@@error.@Error;
                                set @errpos = @errpos . '6';
                            end

                            if @Error>0 begin
	                            rollback transaction
                                RAISERROR (50005,10,1,N'abcde');
                                insert into lot_mem_WinPrizeRec(UserID,PrizePoolID,PrizeID,SerialNoID,LotteryRecID,PrizeRecID,WinPrizeDate,memo)
                                values (@UserID,@PrizePoolID,@PrizeID,@SerialNoID,@LotteryRecID,@PrizeRecID,getdate(),'执行失败,error='.@errpos)
                                select '' as PrizeRecID, '' LotteryRecID, '' SerialNoID, '' SerialNo
                            end else begin                                            
                                commit transaction
                                select @PrizeRecID PrizeRecID, @LotteryRecID LotteryRecID, @SerialNoID SerialNoID,@SerialNo SerialNo
                            end";
                    */
                    // call lottery(in p_prize_pool_id int, in p_prize_id int, in p_user_id int, in p_ip varchar(50))
                    $_sql = "call lottery(" . $_prizePoolID . "," . $_prizID . ",". $this->UserID . ",'" . $_ip."')";
                    if ($this->IsDebug) {
                        Log::WriteToFile("Lottery", "Sql=" . $_sql);
                    }
                    
                    $_rsLottery= DB::getResultSet($_sql);
                    if($_rsLottery!=null && mysql_num_rows($_rsLottery)>0) {
                    	while($_rowLottery = mysql_fetch_array($_rsLottery,MYSQL_ASSOC)){
	                        $this->WinPrizeInfo->PrizeRecID = $_rowLottery["prize_rec_id"];
	                        $this->WinPrizeInfo->LotteryRecID = $_rowLottery["lottery_rec_id"];
	                        $this->WinPrizeInfo->SerialNoID = $_rowLottery["serial_no_id"];
	                        $this->WinPrizeInfo->SerialNo = $_rowLottery["serial_no"];
	                        $this->WinPrizeInfo->PrizePoolID = $_prizePoolID;
	                        if ($this->WinPrizeInfo->PrizeRecID != "") {
	                            $this->WinPrizeInfo->PrizeID = $_row["prize_id"];
	                            $this->WinPrizeInfo->PrizeDesc = $_row["prize_desc"];
	                            $this->WinPrizeInfo->PrizeImage = $_row["prize_image"];
	                        }
	                    }
                    }

                    if ($this->IsDebug) {
                        Log::WriteToFile("Lottery", "PrizeRecID=" . $this->WinPrizeInfo->PrizeRecID . "；LotteryRecID=" . $this->WinPrizeInfo->LotteryRecID . "；PrizeID=" . $this->WinPrizeInfo->PrizeID . ";PrizePoolID=" . $_prizePoolID);
                    }
                    return $this->WinPrizeInfo;
                }
            }
        }
        //没中奖
        $_sql1 = @"insert into lot_lottery_rec(user_id,prize_pool_id,prize_id,lottery_prize_date,ip,cmn_createdate,cmn_modifydate) values (" . $this->UserID . "," . $this->PrizePoolIDs[0] . ",null,now(),'" . $_ip . "',now(),now()); select LAST_INSERT_ID()";
        $this->WinPrizeInfo->LotteryRecID = DB::getFieldValue($_sql1);

        return $this->WinPrizeInfo;
    }
}

?>
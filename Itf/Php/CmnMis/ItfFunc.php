<?php
class ItfFunc {
    //判断是否有权限
    public static function IsHasRight(&$retArray,$userID=null) {
        if($userID==null) { $userID = ItfFunc::GetUserID(); }
    	
        if($userID==""){
        	$retArray["HasNoLogin"] = "1";
        	$retArray["IsSuccess"] = "0";
        	$retArray["ErrMsg"] = "用户未登录！";        	
        	$retArray["UserName"] = "";
        	
        	return false;
        }
        
    	return true;
    }

    
    //----------------------------------------------------------
    /// <summary>
    /// 获取字段信息记录
    /// </summary>
    /// <param name="userFormId">用户表单代码</param>
    /// <returns name="array">数组</returns>
    public static function GetColumnInfoByUserFormID($userFormId,&$itfArray) {
    	$_userID = ItfFunc::GetUserID();
    
    	if (!ItfFunc::IsHasRight($itfArray,$_userID)) {
    		return $itfArray;
    	}
    
    	$_userformid = $userFormId;// Cmn.Request.Get("CurUserFormID");
    
    	if ($_userformid == "") {
    		return $itfArray->Error("用户表单代码为空！");
    	}
    
    	$_sql = "select selectsql from cmn_usr_userform where userformid = '" . $_userformid . "'";
    	//通过formid拿到数据库存储的目标表 sql
    	$_sel_sql = DB::getFieldValue($_sql);
    	//拿到主键
    	$_key = SqlAnalyse::GetKeyField($_sel_sql);
    
    	$_sql = "
	    	select a.userformcolid,a.colname,a.coltitle,a.colwidth,a.colcontroltypeid,
	    		b.controltypedesc,a.controlcfg,a.is_required,
		    	a.default_value,c.regexcontent,a.isshowinedit,a.isshowingrid,
		    	case when a.colalign=1 then 'center' when a.colalign=2 then 'right' when a.colalign=3 then 'left' end as colalign 
		    	,a.colhint colhint,c.errormsg errormsg,colformat,isshowhtml
	    	from cmn_usr_userformcol as a
	    		left join cmn_usr_userformcontroltype as b on a.colcontroltypeid = b.controltypeid
	    		left join cmn_chk_regex as c on a.regexid=c.regexid
	    	where (isshowingrid = 1 or isshowinedit = 1) and userformid = '" . $_userformid . "' order by a.sortid ";
	    
    	$_rows = DB::getArray($_sql);

    	//Log::WriteToFile("tttt","记录条数：" . var_dump($_rows));
    	
    	
    	if (count($_rows) <= 0) {
    		return $itfArray->Error("字段信息为空！");
    	}
    
    	$itfArray["IsSuccess"] = "1";
    	$itfArray["ErrMsg"] = "";
    	$itfArray["KeyColName"]=  $_key;
    	$itfArray["RecCount"] = count($_rows);
    	
    	$itfArray["data"] = array();
    
    	foreach($_rows as $_row){
    		$_data = array();
    
    		$_data["UserFormColID"] = $_row["userformcolid"];
    		$_data["ColName"] = $_row["colname"];
    		$_data["ColTitle"] = $_row["coltitle"];
    		$_data["ColWidth"] = $_row["colwidth"];
    		$_data["ColControlName"] = $_row["controltypedesc"];
    		$_data["IsRequired"] = $_row["is_required"];
    		$_data["IsShowInGrid"] = $_row["isshowingrid"];
    		$_data["IsShowInEdit"] = $_row["isshowinedit"];
    		$_data["IsShowHtml"] =  $_row["isshowhtml"];
    		$_data["RegexContent"] = $_row["regexcontent"];
    		$_data["RegexErrorMsg"] = $_row["errormsg"];
    		$_data["ColAlign"] = $_row["colalign"];
    		$_data["ColHint"] = $_row["colhint"];
    		$_data["ColFormat"] = $_row["colformat"];
    		$_data["ColControlTypeID"] = $_row["colcontroltypeid"];
    		
    		
//     		$_controlcfg = ItfFunc::FormatControlCfg($_row["controlcfg"]);
 			
// 			if(count($_controlcfg)>=0) { $_data["ControlCfg"] =  $_controlcfg; }
//     		else { $_data["ControlCfg"] = ""; }

//     		//默认值
// 			$_default_value = $_row['default_value'];
			
// 			if($_default_value=="null" || $_default_value==""){
// 				$_data["DefaultValue"] ="";
// 			}
// 			else{
// 				$_default_value = str_replace("'","",$_default_value,$_qty);
				
// 				if($_qty>0){ $_data["DefaultValue"] =$_default_value; }
// 				else{
// 					$_default_value = DB::getFieldValue("select ".$_default_value);
// 					$_data["DefaultValue"] =$_default_value;
// 				}
// 			}
    
			$_joControlCfg = ItfFunc::FormatControlCfg($_row["controlcfg"]);
			$_default_value = null; //字段默认值
			
			//字段的默认值，只要以控件配置为主，如果控件配置中没有的话，把表单字段配置表中的默认值放到控件配置中
			
			 //有控件配置，如果控件配置中有默认值配置的话，就拿控件配置中的
			if (isset($_joControlCfg["Default"])) {
				$_default_value = $_joControlCfg["Default"];
			}
			
			
			if ($_default_value == null) {
				$_default_value = $_row["default_value"];
			}
			
			if ($_default_value == "null" || $_default_value == "") {
				$_data["DefaultValue"] = "";
				$_joControlCfg["Default"] = "";
			}
			else { //_joCol["DefaultValue"] 存放的是经过运算过的值， _joControlCfg["Default"]存放的是原始的值
				$_joControlCfg["Default"] = $_default_value;
				
				$_default_value = str_replace("'","",$_default_value,$_qty);
				
				if($_qty>0){ $_data["DefaultValue"] = $_default_value; }
				else{ $_data["DefaultValue"] = DB::getFieldValue("select ".$_default_value); }
			}
			
			$_data["ControlCfg"] = $_joControlCfg;
			
			
			$itfArray["data"][] = $_data;
    	}
    	
    	//获取用户权限
    	$itfArray["AllowAdd"] = "1";
    	$itfArray["AllowDell"] = "1";
    	$itfArray["AllowModify"] = "1";
    	$itfArray["AllowToExcel"] = "1";
    	
    	if (!ItfFunc::IsSysAdmin($_userID)) { //不是Admin才需要获取权限
    		$_dt = DB::getArray("
	    		select * from (
		    		select IsShow,AllowAdd,AllowDell,AllowModify,AllowToExcel ,1 FromType
		    		from cmn_usr_UserRight ur
		    		where  ur.UserID='" . $_userID . "' and  ur.UserFormID='" . $_userformid . "'
		    		union all
		    		select IsShow,AllowAdd,AllowDell,AllowModify,AllowToExcel ,2 FromType
		    		from cmn_usr_UserGroupRight ur,cmn_usr_Users u
		    		where ur.UserGroupID=u.UserGroupID and u.UserID='" . $_userID . "'
		    		and  ur.UserFormID='" . $_userformid . "'
	    		) t order by FromType");
    	
    		if (count($_dt) > 0) {
    			if( $_dt[0]["AllowAdd"]=="0") { $itfArray["AllowAdd"] = "0"; }   	
    			if ( $_dt[0]["AllowDell"] == "0") { $itfArray["AllowDell"] = "0"; }
    			if ($_dt[0]["AllowModify"] == "0") { $itfArray["AllowModify"] = "0"; }
    			if ($_dt[0]["AllowToExcel"] == "0") { $itfArray["AllowToExcel"] = "0"; }
    		}
    	}
    	
    	return $itfArray;
    }
    //----------------------------------------------------------
    /// <summary>
    /// 处理sql中带中括号的字段(字段加上化名前缀后替换原先的字段)
    /// </summary>
    /// <param name="sql">sql语句</param>
    /// <param name="sqlClause">sql子句</param>
    /// <returns>替换好字段的sql子句</returns>
    public static function FormatFieldName($sql, $sqlClause) {
    	$_pattern = "/\\[[\\s]*[\\S]+[\\s]*\\]/";
    	$_reStr = Str::RegexMatchIndexList($_pattern,$sqlClause);
    
    	
    	if(count($_reStr)>0){
    		foreach($_reStr as $_index){
    			$_org = trim($_index['val']);
    			$_new = str_replace("]", "",str_replace("[", "",$_org));
    			$_mar = SqlAnalyse::GetFullFieldName($sql, $_new);
    			
    			$sqlClause =str_replace($_org, $_mar,$sqlClause); 
    		}
    	}
    
    	return $sqlClause;
    }
    //----------------------------------------------------------
    /// <summary>
    /// autocomplete 格式化function
    /// </summary>
    /// <param name="fillSql">填充的sql语句</param>
    /// <param name="displayFieldCount">显示字段个数 正序</param>
    /// <param name="limit">显示的记录条数</param>
    /// <param name="where">sql条件</param>
    /// <returns>格式化好的sql语句</returns>
    private static function FormatAutoCompleteSql($fillSql,$displayFieldCount=2,$limit=10,$where=""){
    	//拿到配置信息
    	//$_controlcfg =Str::json_to_array($controlCfg);
    	//拿到执行加密的sql
    	$_fillsql = trim($fillSql);
    	//显示的数据条数
    	$_count=  trim($limit);
    	//显示的字段个数 从左到右依次计算
    	$_displayFieldCount = trim($displayFieldCount);
    	//拿到关键字段
    	$_keyField = SqlAnalyse::getKeyField($_fillsql);
    	//获取字段信息
    	$_fields = SqlAnalyse::GetSelectFieldCleanList($_fillsql);
 		//字段串
 		$_fieldsStr = "";
 		//子查询处理
    	$_selectLst =  array();
    	$_bracketLst = array();


    	$_fillsql = SqlAnalyse::wipeSubSelect($_fillsql, $_selectLst);
    	$_fillsql = SqlAnalyse::wipeBracket($_fillsql, $_bracketLst);
    	//去掉order by
    	$_fillsql = SqlAnalyse::deleteOrderBy($_fillsql);
    	
    	//处理完毕吧 子查询什么的还原
    	if ($_bracketLst != null) {
    		$_fillsql = SqlAnalyse::recoverBracket($_fillsql, $_bracketLst);
    	}
    	
    	if ($_selectLst != null) {
    		$_fillsql = SqlAnalyse::recoverSubSelect($_fillsql, $_selectLst);
    	}
    	
    	//查询条件
    	$_where = "";
    	//临时sql存储变量
    	$_tempSql="";
    	//如果传进来的条件不是空的话
    	if($where!=""){$where = ItfFunc::FormatFieldName($_fillsql,$where);}
    	//需要拼接的sql的集合
    	$_sqlArr=array();

     	//拼接显示字段查询sql
     	for($_j=0;$_j<count($_fields);$_j++){
     		
     		for($_k=0;$_k<count($_fields);$_k++){

     			//拼接条件和占位符

     			if($_k == count($_fields)-1){
     				$_where .=$_fields[$_j] ." like '%$".$_k."$%' ";
     			}
     			else{

     				$_where .=$_fields[$_j] ." like '%$".$_k."$%' and ";
     			}

     			//拼接查询字段
     			if($_j<$_displayFieldCount){
     				$_fieldsStr.=($_fields[$_j] . ($_j == count($_fields) - 1 ? " " : " ,"));
     			}
     			//追加条件
     			$_tempSql = SqlAnalyse::addWhere($_fillsql,$_where);

     			$_sqlArr[] = $_tempSql;

     			$_where = "";

     		}

     	}
    	 
    	$_fillsql = "";

    	for($_i=0;$_i<count($_sqlArr);$_i++){

    		 if ($_i == 0)
                {
                    $_fillsql .= $_sqlArr[$_i]." limit ".$_count;
                }
                else {
                    $_fillsql .= (" union  " . $_sqlArr[$_i]." limit ".$_count);
                }
    	}

    
    	return "select " . $_fieldsStr . " from (" + $_fillsql + ") autocompleteTemp order by " + $_keyField + " desc limit ".$_count;
	} 
	 //--------------------------------------------------
    /// <summary>
    /// 格式化控件配置
    /// </summary>
    /// <param name="controlCfg">空间配置条件</param>
    /// <returns>控件配置字符串</returns>
    public static function FormatControlCfg($controlCfg)
    {

    	//将配置转成字符串
    	$_controlcfg = array();

    	if($controlCfg!="" && $controlCfg!="null") {
    		
	    	try {$_controlcfg  = Str::json_to_array($controlCfg); }
	        catch (Exception $ex)
	        {
	            $_controlcfg["CfgError"] = $ex->getMessage();
	            Log::Error("在转控件配置到json的时候报错！错误信息：" . $_controlcfg["CfgError"] . "  控件配置：" + $controlCfg);
	        }


			if (array_key_exists("FillSql",$_controlcfg) && 
                $_controlcfg["FillSql"] != null && 
                $_controlcfg["FillSql"] != "" && 
                Str::RegexMatch("/\\s*select\\s+/",$_controlcfg["FillSql"])) {

				$_controlcfg["FillSql"] = Str::Authcode($_controlcfg["FillSql"],"incode");

			}
		}

        return $_controlcfg;
    }
	//----------------------------------------------------------
	//获取用户代码的CacheKey
	public static function GetUserIDCacheKey()
	{
		$_sessionID = Request::Get("CurSessionID");
	
		if($_sessionID=="") {
			return "";
		}
		else { return "Cmn_Login_UserID_".$_sessionID;
		}
	}
	//----------------------------------------------------------
	//获取当前登录的用户代码  add by sulgar
	public static  function GetUserID() {
		if(ItfFunc::GetUserIDCacheKey()=="") { //不是代理方式
			if(isset($_SESSION["Cmn_User_ID"])&&$_SESSION["Cmn_User_ID"]!=""){
				$userID =$_SESSION["Cmn_User_ID"]; 
				ItfFunc::SetUserID($userID);
					
				return $userID;
			}
			else { return ""; }
		}
		else { //代理方式
			if(!(Cache::get(ItfFunc::GetUserIDCacheKey())==null
					|| Cache::get(ItfFunc::GetUserIDCacheKey())=="")){
				$userID = Cache::get(ItfFunc::GetUserIDCacheKey());
				ItfFunc::SetUserID($userID);
					
				return $userID;
			}
			else { return ""; }
		}
	}
	//----------------------------------------------------------
	//设置当前用户代码 add by sulgar
	public static function SetUserID($userID) {
		if(ItfFunc::GetUserIDCacheKey()!="") { //代理方式
			Cache::set(ItfFunc::GetUserIDCacheKey(),$userID,1200);
		}
		else { //不是代理方式
			$_SESSION["Cmn_User_ID"] = $userID;
		}
	}
	//----------------------------------------------------------
	//退出登录
	public static function ExitLogin(){
		if(ItfFunc::GetUserIDCacheKey()=="") { //可能是代理方式
			$_SESSION["Cmn_User_ID"] = "";
			return true; 
		}
		else { //代理方式		
			Cache::delete(ItfFunc::GetUserIDCacheKey());
			return true;
		}
	}
	//----------------------------------------------------------
	//执行sql
	public static function ExeSql(){
		$_sql = Request::Get("sql",false,false);
		$_retArray = new ItfArray();
	
		if($_sql=="" || $_sql==null){ return $_retArray->Error("无参数无法执行")->ToString(); }
		
		$_sql = SqlAnalyse::ReplaceVarInSql($_sql);
	
		if(DB::exeSql($_sql)){ return $_retArray->Success()->ToString(); }
		else{ return $_retArray->Error(DB::$LastError)->ToString(); }
	}
	//----------------------------------------------------------
	//获取某个字段的值
	public static function GetFieldValue(){
		$_sql = Request::Get("sql",false,false);
		$_retArray = new ItfArray();
		
		if($_sql=="" || $_sql==null){ return $_retArray->Error("参数错误")->ToString(); }
	
		//if (get_magic_quotes_gpc()==1){ $_sql=stripcslashes($_sql); }//解决转义符问题
		
		$_sql = SqlAnalyse::ReplaceVarInSql($_sql);
		
		$_value = DB::getFieldValue($_sql);
		
		$_retArray["value"] = $_value;
	
		return $_retArray->Success()->ToString();
	}
	//----------------------------------------------------------
	//获取数据结果集
	public static function GetResultSet(){
		$_sql = Request::Get("sql",false,false);
		$_retArray = new ItfArray();
		$_top = Request::Get("Top");
	
		if($_sql=="" || $_sql==null){ return $_retArray->Error("参数错误")->ToString(); }
		
		//if (get_magic_quotes_gpc()==1){ $_sql=stripcslashes($_sql); } //解决转义符问题
		
		$_sql = SqlAnalyse::ReplaceVarInSql($_sql);
	
		if ($_top != "") {  //有top值
			$_sql = SqlAnalyse::AddLimitToSelect($_sql, " limit ".$_top." ");
		}
		
		$_result = DB::getResultSet($_sql);
	
		
		if($_result=="") { return $_retArray->Error(DB::$LastError)->ToString(); }
		else { return AjaxJson::ResultSetToJson($_result); }
	}
	//----------------------------------------------------------
	/// <summary>
	/// 检查排序列是否有空值，如果为空的话置成主键的值
	/// </summary>
	/// <param name="tableName">表名</param>
	/// <param name="keyField">主键字段名</param>
	/// <returns></returns>
	public static function CheckSortField( $tableName,$keyField) {
		$_sortFieldName = trim(Request::Get("Cg_SortFieldName"));
	
		if ($_sortFieldName == "") { return false; }
	
		return DB::exeSql(
			"update " . $tableName . " set  `" . $_sortFieldName . "` =`" .$keyField.
			"` where `" . $_sortFieldName ."` is null "
		);
	}
	//--------------------------------------------------
	/// <summary>
	/// 判断用户是不是系统管理员
	/// </summary>
	/// <param name="userID"></param>
	/// <returns></returns>
	public static function IsSysAdmin($userID) {
		if (DB::getFieldValue("select usergroupid from cmn_usr_users where userid='" . $userID . "'") == "1") {
			return true;
		}
		else { return false; }
	}
	//--------------------------------------------------
	//处理sql多语句
	public static function ProcessSqlMutiSentence($sql){
		
	}
	//--------------------------------------------------
    //加密字符串
    public static function EncryptStr(&$itfArray){

        $_str = Request::Get("Str");

        $itfArray["Str"] = "";

        if($_str !=""){ 
            $itfArray["Str"] = Str::Authcode($_str,"incode");
        }

        return $itfArray->ToString();
    }
    //--------------------------------------------------
    //解密字符串
    public static function DecryptionStr(&$itfArray){

        $_str = Request::Get("Str");

          $itfArray["Str"] = "";

        if($_str !=""){ 
            $itfArray["Str"] = Str::Authcode($_str);
        }

        return $itfArray->ToString();


    }
}

?>
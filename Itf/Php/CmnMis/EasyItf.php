<?php 
/**
 * 
 * 简易接口类
 *
 */
class EasyItf{
	/**
	 * 调用简易接口
	 * @param ItfArray $retArray
	 * @return string
	 */
	public static function Call(&$retArray){
		$_itfName = Request::Get("ItfName");
		$_where = Request::Get("Where",false,false);
		$_orderby = Request::Get("OrderBy");
		$_userFormId = "";
		$_cfgSql = "";
		
		if ($_itfName == "") {
			$retArray["data"] = array(); return $retArray->Error("参数有误！")->ToString();
		}
		
		$_userFormId = DB::getFieldValue("
				select userformid from cmn_itf_interface where itfname='" . $_itfName . "'");
		
		$_cfgSql = DB::getFieldValue("select `Sql` from cmn_itf_interface where itfname = '" .
				$_itfName . "'");
		
		
		if ($_cfgSql == "") { //找不到对应的sql
			$retArray["data"] = array();
				
			if(DB::getFieldValue("select interfaceid from cmn_itf_interface where itfname = '" .
					$_itfName . "'")=="") {
				return $retArray->Error("接口名为'".$_itfName."'的接口不存在！")->ToString();
			}
			else { return $retArray->Error("接口对应的sql为空,无法执行！")->ToString();
			}
		}
		
		//替换sql中的变量
		$_cfgSql = SqlAnalyse::ReplaceVarInSql($_cfgSql);
		
		//获取参数列表，替换sql语句中的占位符
		if (strpos($_cfgSql,"#") !== false) {
			//替换当前语言
			//$_cfgSql = $_cfgSql.Replace("#CurLanguageID#",((int)Cmn.Language.GetCurLanguage()).ToString());
		
			foreach ($_REQUEST as $_key=>$_value) {
				$_cfgSql = str_replace("#" . trim($_key) . "#",Request::Get($_value),$_cfgSql);
			}
		}
		
		if ($_where != "") {
			$_where = ItfFunc::FormatFieldName($_cfgSql, $_where);
			$_cfgSql = SqlAnalyse::AddWhere($_cfgSql,$_where);
		}
		
		if ($_orderby != "") {
			$_orderby = ItfFunc::FormatFieldName($_cfgSql, $_orderby);
			$_cfgSql = SqlAnalyse::AddSortSentence($_cfgSql,$_orderby);
		}
		
		return AjaxJson::SqlToJson($_cfgSql,"","","",false,30,$retArray);
	} 
}

?>
<?php


class FileOpt{

	public static function Upload(){

		 //获取fileinput的名称
		$_inputName=Request::Get("InputName");

		//文件最大大小默认3m
		$_limitSize =Request::Get("LimitSize")!=""?Request::Get("LimitSize")*10485760:3*10485760;
 
		//上传文件根目录
		$_savePath = Request::Get("SavePath")!=""?Request::Get("SavePath"):"/Upload";

		 //是否保存真实文件名
		$_isSaveRealFileName =Request::Get("IsSaveRealFileName")!=""?true:false;

		//真实文件名称
		$_realFileName = Request::Get("IsSaveRealFileName");

		//文件名称
		$_fileName = "";

 		//上传文件后缀
		$_suffix=Request::Get("LimitSuffixName")!=""?Request::Get("LimitSuffixName"):'txt,rar,zip,jpg,jpeg,gif,png,swf,wmv,avi,wma,mp3,mid';

		//是否是通过表单上传
		$_isFormUpload = true;

		//回执数据
		$_receiptData = array(
				"State"=>"0",
				"Msg"=>"未捕获的错误！保存失败",
				"Path"=>"",
				"name"=>"",
				"err"=>"未捕获的错误！保存失败",
				"msg"=>array( "url"=>"", "localname"=>"", "id"=>"1" )
				);
 
		//FILES里面是否存在文件对象 如果存在的话那么取这个里面的文件数据
		if(isset($_FILES[$_inputName])){

			//文件
			$_fileData=@$_FILES[$_inputName];
				
			if(!empty($_fileData['error'])){
				switch($_fileData['error'])
				{
					case '1':
						$_receiptData["err"]=$_receiptData["Msg"] = '文件大小超过了php.ini定义的upload_max_filesize值';
						break;
					case '2':
						$_receiptData["err"]=$_receiptData["Msg"] = '文件大小超过了HTML定义的MAX_FILE_SIZE值';
						break;
					case '3':
						$_receiptData["err"]=$_receiptData["Msg"] = '文件上传不完全';
						break;
					case '4':
						$_receiptData["err"]=$_receiptData["Msg"] = '无文件上传';
						break;
					case '6':
						$_receiptData["err"]=$_receiptData["Msg"] = '缺少临时文件夹';
						break;
					case '7':
						$_receiptData["err"]=$_receiptData["Msg"] = '写文件失败';
						break;
					case '8':
						$_receiptData["err"]=$_receiptData["Msg"] = '上传被其它扩展中断';
						break;
					case '999':
					default:
						$_receiptData["err"]=$_receiptData["Msg"] = '无有效错误代码';
				}
			
				return FileOpt::jsonEncode($_receiptData);
			
			}
			else if(empty($_fileData['tmp_name']) || $_fileData['tmp_name'] == 'none'){
				$_receiptData["err"] = $_receiptData["Msg"] = "未监测到有文件上传！";
				return FileOpt::jsonEncode($_receiptData);
			}
			else{
				 
				if($_realFileName==""){
					$_realFileName=$_fileData['name'];
				}
			}
		}
		//其次 要么是藏在请求头里面的文件数据 或者是base64
		else{	 
			//不是通过表单上传的
			$_isFormUpload = false;
			 //先看看有没有直接扔过来的数据流
			$_fileData = file_get_contents("php://input");

			//没有咯
			if(!isset($_fileData) || $_fileData==null){
				//将字节流写入临时文件
				//file_put_contents($_tempPath,$_fileData);
				//监测是否存在文件信息
				if(isset($_SERVER['HTTP_CONTENT_DISPOSITION']) && 
				preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info)){
				
					//获取文件名称
					if($_realFileName==""){
						$_realFileName=urldecode($info[2]);
					}

				}
				else{
					//没办法了 实在检测不到了 只能随便搞个图片了
					if($_realFileName==""){
						$_realFileName=date("YmdHis").mt_rand(10000,99999).'_tmp.png';
					}
				}

			}
			else{

				$_fileData = Request::Get($_inputName);
				//监测是否是base64位字符串
				if(strpos($_fileData,"base64")!=false){
					
					$_base64Data = substr($_fileData,21);
					  
					//设置真实名称 因为是base64位串所以无法知道真实名称 除非客户端有传过来
					if($_realFileName==""){
						$_realFileName=date("YmdHis").mt_rand(10000,99999).'_tmp.'
						.substr($_fileData,11, strpos($_fileData,";")-11);
					}
					
					$_fileData = base64_decode(str_replace(" ","+",$_base64Data));
				}
				else{

					$_receiptData["err"] = $_receiptData["Msg"] = "未监测到有文件上传！";
					return FileOpt::jsonEncode($_receiptData);
				}
			}
		}

		//要求保存文件真实名称
		if($_isSaveRealFileName && $_realFileName!=""){
			 $_fileName = $_realFileName;
		}
		else{$_fileName = date("YmdHis").mt_rand(10000,99999).'_tmp.png';}

		//获取文件的信息 比如后缀什么的
		$_fileInfo = pathinfo($_fileName);
		//文件扩展名
		$_fileSuffix = isset($_fileInfo["extension"])?$_fileInfo["extension"]:$_fileName;
		//文件扩展名分类
		$_fileSuffixSort = array(
				"jpg"=>"image",
				"jpeg"=>"image",
				"png"=>"image",
				"gif"=>"image",
				"bmp"=>"image",
				"mp4"=>"video",
				"avi"=>"video",
				"swf"=>"video",
				"wmv"=>"video",
				"wma"=>"video",
				"mid"=>"video",
				"mp3"=>"audio"
				);

		//监测后缀名称
		if(!preg_match('/^('.str_replace(',','|',$_suffix).')$/i',$_fileSuffix)) {
			$_receiptData["err"] = $_receiptData["Msg"] = "上传文件不合法，非白名单内的文件后缀！";
			return FileOpt::jsonEncode($_receiptData);
		}


		//按照目录区分上传的文件好了
		$_savePath .= "/".(isset($_fileSuffixSort[strtolower($_fileSuffix)])?$_fileSuffixSort[strtolower($_fileSuffix)]:"file").
					  "/".date('ymd')."/";

		//创建最终保存的目录 此处乃绝对路径比较烦
		$_saveRootPath = dirname(dirname(dirname(dirname(__FILE__)))).$_savePath;

		//页面访问的路径
		$_requestRootPath = $_savePath;


		//创建目录
		if(FileOpt::CreateFolder($_saveRootPath) == false){
			$_receiptData["err"] = $_receiptData["Msg"] = "目录创建失败！可能是目录名称不对或者是没有权限！path:".$_saveRootPath;
			return FileOpt::jsonEncode($_receiptData);
		}

		//临时文件扩展名
		$_tempFileName = $_fileName."temp";

		//先保存为临时文件到时候再改
		if(!FileOpt::SaveFile($_fileData,$_saveRootPath."/".$_tempFileName,$_isFormUpload)){
			$_receiptData["err"] = $_receiptData["Msg"] = "临时文件保存失败！可能文件夹没有写入权限！path:".$_saveRootPath;
			return FileOpt::jsonEncode($_receiptData);
		}
		
		if(filesize($_saveRootPath."/".$_tempFileName) > $_limitSize){
			$_receiptData["err"]=$_receiptData["Msg"]='请不要上传大小超过'.($_limitSize/10485760).'M 的文件';
			return FileOpt::jsonEncode($_receiptData);
		}
		else {
 
			PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);

			//确认无误之后修改文件名成
			if(rename($_saveRootPath."/".$_tempFileName,$_saveRootPath."/".$_fileName)){
				$_receiptData["err"]=$_receiptData["Msg"]='文件上传失败！';
				return FileOpt::jsonEncode($_receiptData);
			}
			//给权限
			chmod($_saveRootPath."/".$_fileName,0755);
			 
			$_receiptData["State"] = '1';
			$_receiptData["Msg"] = '上传成功！';
			$_receiptData["Path"] =FileOpt::GetRequestUri(). $_requestRootPath."/".$_fileName;
			$_receiptData["err"]="";
			$_receiptData["msg"]["localname"]=$fileName;
			$_receiptData["msg"]["url"]=FileOpt::GetRequestUri().$_RequestRootUri."/".$_fileName;
			$_receiptData["msg"]["id"]="1";
		}

		return FileOpt::jsonEncode($_receiptData);
		
	}
	
	//根据路径删除服务器端的文件
 	public static function DelFile(){

 		$_filePath = Request::Get("FilePath");
 	 
 		$_filePath = dirname(dirname(dirname(dirname(__FILE__)))).$_filePath;

		if(@unlink ($_filePath) == false){
			FileOpt::jsonEncode(array("IsSuccess"=> "1","ErrMsg"=> "删除成功！"));
		}
		else{
			FileOpt::jsonEncode(array("IsSuccess"=> "0","ErrMsg"=> "删除失败！"));
		}
 	}

 	//图片代理函数
 	public static function ImgAgent(){

 		 //图片路径
         $_url = Request::Get("Path");

         $im = @imagecreatefromjpeg('bogus.image');
		 header('Content-Type: image/jpeg');
		 imagejpeg($img);
		 imagedestroy($img);

		 echo "a";


 		//echo file_get_contents($_url);

 		return null;
 	}

	
	/**
	 * 
	 * @param  $fileData 文件数据源
	 * @param  $fileSaveName 保存的路径
	 * @param  $isInputFile 是否是通过file input 提交过来的数据
	 */
	private static function SaveFile($fileData,$fileSaveName,$isInputFile){
 
		 if($isInputFile){
		 	return move_uploaded_file($fileData['tmp_name'],$fileSaveName);
		 }
		 else{
		 	//保存文件
		 	return count($fileData) === file_put_contents($fileSaveName, $fileData);
		 }
	}
	
	
	 /**
	 * 吧数组变成json字符串
	 * @param Array $arr
	 * @return string
	 */
	private static function jsonEncode($arr){
		
		return json_encode($arr);
	}

	/**
	 * 递归创建目录
	 * @param String $path
	 */
	private static function CreateFolder($path){
	
		if (!$path) return false;  
        if(!file_exists($path)) {  
            mkdir($path,0777,true);  
            return chmod($path,0777);  
        }
         else {  
            return true;  
        }  
	}

	/**
	 * 获取当前文件的uri
	 * @param String $uri
	 */
	private static function GetRequestUri() {
	    if (isset($_SERVER['REQUEST_URI'])) {  $_uri = $_SERVER['REQUEST_URI'];   }
	    else {
	        if (isset($_SERVER['argv'])) {
	            $_uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['argv'][0];
	        }
	        else {
	            $_uri = $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
	        }
	    }

	    $_tmparr=parse_url($_uri);
	    $_uri = $_tmparr["path"];
	    $_uri = substr($_uri, 0,strripos($_uri,'/'));
	    $_uri = substr($_uri, 0,strripos($_uri,'/'));
	    $_uri = substr($_uri, 0,strripos($_uri,'/')+1);
	    //"http://".$_SERVER['SERVER_NAME'].
	    if($_uri == "/"){$_uri="";}
	    return $_uri;
	}



}
 
?>

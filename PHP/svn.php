<?php

require_once "SQLite_common.php";

/*
class SVN
{
	// コンストラクタ
	function __construct($url, $work, $user);
	// デストラクタ
	function __destruct();
	// 存在確認
	function isExist($file);
	// ファイル追加
	function addFile($file, $data, $comments);
	// ファイル追加(FORMから)
	function addUploadFile($file, $uploadFile, $comments);
	// ファイル更新
	function updateFile($file, $data, $comments);
	// ファイル更新(FORMから)
	function updateUploadFile($file, $uploadFile, $comments);
	// ファイル削除
	function delFile($file, $comments);
	// フォルダ内ファイルリスト取得
	function getDir($dir);
	// ファイル更新履歴取得
	function getFileRev($file, $limit=100);
	// ファイル最終更新日取得
	function getLastUpdate($file);
	// ファイルデータ取得
	function getData($file);
}
*/
class SVN
{
	private $m_url;
	private $m_user;
	private $m_work;
	private $m_cacheDB;
	
	
	// コンストラクタ
	function __construct($url, $work, $user) {
		$this->m_url  = $url;
		$this->m_user = $user;
		$this->m_work = $work;
		
		$dbName = preg_replace("/file:\/\/\//", "", $url);
		$this->m_cacheDB = new PDO("sqlite:$dbName.db");
		$this->m_cacheDB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		if(!SQLiteCom::isExist($this->m_cacheDB, "sqlite_master", "name='file_list'", "name")) {
			$this->m_cacheDB->exec("CREATE TABLE file_list(file TEXT, lastUpdate TEXT)");
		}
	}
	
	// デストラクタ
	function __destruct() {
		unset($this->m_cacheDB);
	}

	// 存在確認
	function isExist($file) {
		$file = str_replace("\\", "/", $file);
		$pipe = popen("svn info $this->m_url/$file", "r");
		$ret = 0;
		while(!feof($pipe)) {
			$output = explode(":", trim(fgets($pipe)));
			if($output[0] == "Node Kind") {
				if(trim($output[1]) == "file") {
					$ret = 1;
				}
				else {
					$ret = 2;
				}
			}
		}
		pclose($pipe);
		return $ret;
	}

	// ファイル追加
	function addFile($file, $data, $comments) {
		$file = str_replace("\\", "/", $file);
		if($this->isExist($file) != 0) {
			return false;
		}
		$work = $this->mkWorkDir($this->m_work);
		$endPos = strrpos($file, "/");
		$filename = substr($file, ($endPos == 0 ? $endPos : $endPos+1));
		$fp = fopen("$work/$filename", "wb");
		fwrite($fp, $data);
		fclose($fp);
		
		$comments = mb_convert_encoding($comments, "SJIS", "EUC-JP");
		exec("svn import $work/$filename $this->m_url/$file -m \"$comments\" --username $this->m_user");
		$this->m_cacheDB->exec("DELETE FROM file_list WHERE file='$file'");
		$this->rmWorkDir($work);
		return true;
	}

	// ファイル追加(FORMから)
	function addUploadFile($file, $uploadFile, $comments) {
		$file = str_replace("\\", "/", $file);
		if($this->isExist($file) != 0) {
			return false;
		}
		$work = $this->mkWorkDir($this->m_work);
		$endPos = strrpos($file, "/");
		$filename = substr($file, ($endPos == 0 ? $endPos : $endPos+1));
		$ret = move_uploaded_file($uploadFile, "$work/$filename");
		if($ret) {
			$comments = mb_convert_encoding($comments, "SJIS", "EUC-JP");
			exec("svn import $work/$filename $this->m_url/$file -m \"$comments\" --username $this->m_user");
			$this->m_cacheDB->exec("DELETE FROM file_list WHERE file='$file'");
		}
		$this->rmWorkDir($work);
		return $ret;
	}

	// ファイル更新
	function updateFile($file, $data, $comments) {
		$file = str_replace("\\", "/", $file);
		if($this->isExist($file) != 1) {
			return false;
		}
		$work = $this->mkWorkDir($this->m_work);
		$filename = $this->checkout($file, $work);
		
		$fp = fopen("$work/$filename", "wb");
		fwrite($fp, $data);
		fclose($fp);
		
		$this->commit($file, $work, $comments);
		$this->rmWorkDir($work);
	}

	// ファイル更新(FORMから)
	function updateUploadFile($file, $uploadFile, $comments) {
		$file = str_replace("\\", "/", $file);
		if($this->isExist($file) != 1) {
			return false;
		}
		$work = $this->mkWorkDir($this->m_work);
		$filename = $this->checkout($file, $work);
		$ret = move_uploaded_file($uploadFile, "$work/$filename");
		if($ret) {
			$this->commit($file, $work, $comments);
		}
		$this->rmWorkDir($work);
		return $ret;
	}

	// ファイル削除
	function delFile($file, $comments) {
		$file = str_replace("\\", "/", $file);
		if(!$this->isExist($file)) {
			return false;
		}
		$comments = mb_convert_encoding($comments, "SJIS", "EUC-JP");
		exec("svn delete $this->m_url/$file -m \"$comments\" --username $this->m_user");
		$this->m_cacheDB->exec("DELETE FROM file_list WHERE file='$file'");
		return true;
	}

	// フォルダ内ファイルリスト取得
	function getDir($dir) {
		$files = array();
		$dirs  = array();
		$pipe = popen("svn list $this->m_url/$dir -v", "r");
		while(!feof($pipe)) {
			$line = preg_split("/\s+/", trim(fgets($pipe)));
			if((count($line) < 6) || ($line[count($line)-1] == "./")) {
				continue;
			}
			if($line[count($line)-4] == "non-existent") {
				break;
			}
			$data['file']   = $line[count($line)-1];
			$data['size']   = $line[2];
			$data['author'] = $line[1];
			$data['month']  = $line[count($line)-4];
			$data['day']    = $line[count($line)-3];
			if(!preg_match("/:/", $line[count($line)-2])) {
				$data['year'] = $line[count($line)-2];
			}
			else {
				if($data['month'] <= date("n")) {
					$data['year'] = date("Y");
				}
				else {
					$data['year'] = date("Y")-1;
				}
				$data['time'] = $line[count($line)-2];
			}
			
			if(preg_match("/\/$/", $data['file'])) {
				$data['file'] = preg_replace("/\/$/", "", $data['file']);
				$data['size'] = "";
				$data['kind'] = "dir";
				array_push($dirs, $data);
			}
			else {
				$data['kind'] = "file";
				array_push($files, $data);
			}
		}
		pclose($pipe);
		$files = array_merge($dirs, $files);
		
		return $files;
	}

	// ファイル更新履歴取得
	function getFileRev($file, $limit=100) {
		$dataArray = array();
		$pipe = popen("svn log $this->m_url/$file -l $limit", "r");
		while(!feof($pipe)) {
			$line = trim(fgets($pipe));
			if(preg_match("/^-+$/", $line)) {
				$tmpArray = explode("|", trim(fgets($pipe)));
				if(count($tmpArray) != 4) {
					break;
				}
				$data['rev']    = substr(trim($tmpArray[0]), 1);
				$data['author'] = trim($tmpArray[1]);
				$data['date']   = substr(trim($tmpArray[2]), 0, 19);
				$mCnt           = (int)substr(trim($tmpArray[3]), 0, strpos(trim($tmpArray[3]), " "));
				fgets($pipe);
				$data['comment'] = "";
				for($i = 0 ; $i < $mCnt ; $i++) {
					if($i != 0) {
						$data['comment'] = "\n";
					}
					$data['comment'] = $data['comment'] . trim(fgets($pipe));
				}
				$data['comment'] = mb_convert_encoding($data['comment'], "EUC-JP", "SJIS");
				
				array_push($dataArray, $data);
			}
		}
		pclose($pipe);
		
		return $dataArray;
	}

	// ファイル最終更新日取得[YYYYmmdd]
	function getLastUpdate($file)
	{
		$rs = $this->m_cacheDB->query("SELECT * FROM file_list WHERE file='$file'");
		$data = $rs->fetchAll(PDO::FETCH_ASSOC);
		if(count($data)) {
			$date = $data[0]['lastUpdate'];
		}
		else {
			$rev = $this->getFileRev($file, 1);
			$date = substr($rev[0]['date'], 0, 4) . substr($rev[0]['date'], 5, 2) . substr($rev[0]['date'], 8, 2);
			if(strlen($date)) {
				$this->m_cacheDB->exec("INSERT INTO file_list(file, lastUpdate) VALUES('$file', '$date')");
			}
		}
		return $date;
	}

	// ファイルデータ取得
	function getData($file, &$fileSize)
	{
		$pipe = popen("svn list $this->m_url/$file -v", "r");
		$line = preg_split("/\s+/", trim(fgets($pipe)));
		$fileSize = (int)trim($line[2]);
		pclose($pipe);

		$pipe = popen("svn cat $this->m_url/$file", "rb");
		unset($data);
		while (!feof($pipe)) {
			if(!isset($data)) {
				$data = fread($pipe, 8192);
			}
			else {
				$data .= fread($pipe, 8192);
			}
		}
		pclose($pipe);
		return $data;
	}


	// private functions

	// SVNをCheckout
	private function checkout($path, $work) {
		$endPos = strrpos($path, "/");
		$dirname  = substr($path, 0, $endPos);
		$filename = substr($path, ($endPos == 0 ? $endPos : $endPos+1));
		exec("svn checkout $this->m_url/$dirname $work -N --force");
		return $filename;
	}

	// SVNにCommit
	private function commit($path, $work, $comments) {
		$endPos = strrpos($path, "/");
		$filename = substr($path, ($endPos == 0 ? $endPos : $endPos+1));
		$comments = mb_convert_encoding($comments, "SJIS", "EUC-JP");
		exec("svn commit $work/$filename -m \"$comments\" --username $this->m_user");
		$this->m_cacheDB->exec("DELETE FROM file_list WHERE file='$path'");
	}

	// 作業用フォルダ作成
	private function mkWorkDir($root) {
		$dir = "$root/svn" . date("ymd") . mt_rand(10000, 30000);
		$err = mkdir($dir);
		if(!$err) {
			return false;
		}
		return $dir;
	}

	// 作業用フォルダ削除
	private function rmWorkDir($work) {
		$work = str_replace("/", "\\", $work);
		exec("rmdir /S /Q $work");
	}
}

?>

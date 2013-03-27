<?php
/**
 *
 * @author Sam,sam@ozchamp.net
 *
 */
require_once DIR_EXTENSION . 'PHPExcel.php';
class Excel
{
	public $currentSheet;

	public $filePath;

	public $fileType;

	public $sheetIndex=0;

	public $allColumn;

	public $allRow;

	public function initialized($filePath) {
		if (file_exists($filePath)) {
			$this->filePath=$filePath;
		}else{
			return array();
		}
		//以硬盤方式緩存
		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_discISAM;

		$cacheSettings = array();

		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

		//文件類型
		$file_ext=strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION));

		switch ($file_ext) {
			case 'csv':
				$this->fileType='csv';
				break;

			case 'xlsx':
				$this->fileType='excel';
				break;

			case 'xls':
				$this->fileType='excel';
				break;

			default:
				;
				break;
		}

		if ($this->fileType=='csv') {
			$PHPReader = new PHPExcel_Reader_CSV();

			//默認的分隔符

			//默認的輸出字符集

			if(!$PHPReader->canRead($this->filePath)){
				return array();
			}
		}elseif ($this->fileType=='excel'){
			$PHPReader = new PHPExcel_Reader_Excel2007();

			if(!$PHPReader->canRead($this->filePath)){
				$PHPReader = new PHPExcel_Reader_Excel5();

				if(!$PHPReader->canRead($this->filePath)){
					return array();
				}
			}
		}else{
			return array();
		}

		$PHPReader->setReadDataOnly(true);

		$PHPExcel = $PHPReader->load($this->filePath);

		$this->currentSheet = $PHPExcel->getSheet((int)$this->sheetIndex);

		//$this->currentSheet = $PHPExcel->getActiveSheet();

		$this->allColumn=$this->currentSheet->getHighestColumn();

		$this->allRow=$this->currentSheet->getHighestRow();
	}

	public function fetch($beginRow=NULL, $endRow=NULL){
		$currentSheet=$this->currentSheet;

		$allColumn=$this->allColumn;

		$allRow=$this->allRow;

		$dataSrc=$data=array();

		//獲取列標題
		for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){
			$val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65, 1)->getValue();//ord()將字符轉爲十進制數

			$dataSrc[ord($currentColumn) - 65]=strtolower(trim($val));
		}

		//echo implode("\t", $dataSrc);

		$beginRow=$beginRow ? $beginRow : 2;

		$endRow=$endRow ? $endRow : $allRow;

		for($currentRow = $beginRow ;$currentRow <= $endRow ;$currentRow++){
			$dataRow=array();

			for($currentColumn= 'A';$currentColumn<= $allColumn; $currentColumn++){
				$val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();//ord()將字符轉爲十進制數

				$dataRow[$dataSrc[ord($currentColumn) - 65]]=$val;

				//單元級數據處理 ... 格式化日期等
			}

			//行級數據處理 ...

			if($dataRow){
				$data[]=$dataRow;
			}
		}
		//echo '<pre>';print_r($data);echo '</pre>';
		//echo "\n";

		return $data;
	}
}

//測試
/*
$import=new Excel();
$import->initialized(dirname(__FILE__) . '/test.xlsx');
echo '<pre>';print_r($import->fetch());echo '</pre>';
*/
?>
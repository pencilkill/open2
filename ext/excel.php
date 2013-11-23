<?php
/**
 * Excel import class
 * Read data from xls, xlsx, csv. It can get file type automatically.
 * Read data by section, fetching data from specified section which is between begin row and end row
 *
 * @author Sam,sam@ozchamp.net
 *
 */
class ExcelI
{
	public $currentSheet;

	public $filePath;

	public $fileType;

	public $sheetIndex=0;

	public $highestColumn;

	public $highestColumnIndex;

	public $highestRow;

	public function __construct(){
		require_once __DIR__ . '/Excel/PHPExcel.php';
	}
	/**
	 * Initialize file
	 * @param $filePath, String, the file path
	 */
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

			//預設的分隔符

			//預設的輸出字符集

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

		$this->highestColumn=$this->currentSheet->getHighestColumn();	// e.g. F

		$this->highestColumnIndex = PHPExcel_Cell::columnIndexFromString($this->highestColumn);	// e.g. 5

		$this->highestRow=$this->currentSheet->getHighestRow();	// e.g. 10
	}
	/**
	 * Fetching data from specified section which from begin row to end row
	 * @param $beginRow, Integer, the begin row, default as 2 if it is null
	 * @param $endRow, Integer, the begin row, default as the highest row in current data file if it is null
	 */
	public function fetch($beginRow=NULL, $endRow=NULL){
		$currentSheet=$this->currentSheet;

		$highestColumnIndex=$this->highestColumnIndex;

		$highestRow=$this->highestRow;

		$dataSrc=$data=array();

		//獲取列標題
		for($currentColumnIndex=0; $currentColumnIndex<= $highestColumnIndex; $currentColumnIndex++){
			$val = $currentSheet->getCellByColumnAndRow($currentColumnIndex, 1)->getValue();

			$dataSrc[$currentColumnIndex]=strtolower(trim($val));
		}

		//echo implode("\t", $dataSrc);

		$beginRow=$beginRow ? $beginRow : 2;

		$endRow=$endRow ? $endRow : $highestRow;

		for($currentRow = $beginRow ;$currentRow <= $endRow ;$currentRow++){
			$dataRow=array();

			for($currentColumnIndex=0; $currentColumnIndex<= $highestColumnIndex; $currentColumnIndex++){
				$val = $currentSheet->getCellByColumnAndRow($currentColumnIndex,$currentRow)->getValue();

				$dataRow[$dataSrc[$currentColumnIndex]]=$val;

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
class ExcelE
{

	public $version;
	public $phpexcel;

	public function __construct($version = 'Excel5'){
        $this->version = in_array($version, array('Excel5', 'Excel2007')) ? $version : 'Excel5';

		require_once __DIR__ . '/Excel/PHPExcel/Writer/IWriter.php';
        require_once __DIR__ . '/Excel/PHPExcel/Writer/' . $this->version . '.php';
        require_once __DIR__ . '/Excel/PHPExcel.php';
        require_once __DIR__ . '/Excel/PHPExcel/IOFactory.php';

        $this->phpexcel = new PHPExcel();
	}
	/**
	 * Setting excel data, the data should be convertted to target charset before setted as data
	 * @param $header, Array, header should be an one dimension array, it can be a zero size array
	 * @param $data, Array, data should be a two dimension array.
	 * @param $col_start, Integer, the column start index where header and data writer from, default as 1
	 * @param $row_start, Integer, the row start index where header writer from, default as 1
	 */
	public function setData($header = array(), $data = array(array()), $col_start =0, $row_start= 1){
		// Header
		$col_header_index = $col_start;
		$row_header_index = $row_start;
		foreach($header as $val){
			$this->phpexcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex($col_header_index).$row_header_index, $val);
			$col_header_index++;
		}
		// Data
		$row_index = $row_start + (int)(!empty($header));
		foreach($data as $row){
			$col_index = $col_start;
			foreach ($row as $val){
				$this->phpexcel->getActiveSheet()->setCellValue(PHPExcel_Cell::stringFromColumnIndex($col_index).$row_index, $val);
				$col_index++;
			}
			$row_index++;
		}
	}
	/**
	 * Download current excel data file
	 * It will fix the file extension dynamic
	 * @param $filename, String, dafault setting time() as name if it is empty
	 */
	public function download($filename = ''){
		$ext = $this->version == 'Excel5' ? 'xls' : 'xlsx';

		$filename = $filename ? (strpos($filename, '.') ? $filename : $filename.'.'.$ext) : (time().'.'.$ext);

		$fa = explode('.', $filename);
		if(end($fa) != $ext){
			array_pop($fa);
			$filename = implode('.', $fa).'.'.$ext;
		}

		$ua = $_SERVER["HTTP_USER_AGENT"];
       	$name = strtr(urlencode($filename), array('+'=>'%20'));

       	$writer = "PHPExcel_Writer_{$this->version}";
       	$obj_writer = new $writer($this->phpexcel);

       	header("Content-type:application/vnd.ms-excel");
    	if (preg_match("/MSIE/", $ua)) {
    		header('Content-Disposition: attachment; filename="' . $name . '"');
		} else if (preg_match("/Firefox/", $ua)) {
			header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		} else {
			header('Content-Disposition: attachment; filename="' . $filename . '"');
		}

		$obj_writer->save('php://output');
	}
}
?>
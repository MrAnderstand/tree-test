<?php
namespace common\models;

use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportMaster {
    
    private $data;//массив данных для вывода
    private $xls;//объект класса PHPExcel
    private $defaultFileName;//название входного файла по умолчанию
    private $noRead;//массив строк и столбцов, которые не надо читать
    
    function __construct($inputFileName){
        if(file_exists($inputFileName)) {
            $inputFileType = IOFactory::identify($inputFileName);
            $objReader = IOFactory::createReader($inputFileType);
            $objReader->setReadDataOnly(true);
            $this->xls = $objReader->load($inputFileName);
            
            $this->defaultFileName = 'importFile';
            $this->noRead['rows'] = array();
            $this->noRead['cols'] = array();
        }
    }
    
    //удаляем из прочитанных данных указанную строку
    function noReadRow($index){
        array_push($this->noRead['rows'],$index);
    }
    
    function parse($sheetNumber=0){
        $this->xls->setActiveSheetIndex($sheetNumber);
        $sheet = $this->xls->getActiveSheet();
        $data = $sheet->rangeToArray('A1:'.$sheet->getHighestDataColumn().$sheet->getHighestDataRow());
        
        if(count($this->noRead['rows']))
            foreach($this->noRead['rows'] as $row)unset($data[$row+1]);
        
        return $data;
    }
    
    /**
     * Возвращает преобразованное к числу значение
     * @param $cell ячейка excel
     * @return float
     */
    public static function getFloatFromCell($cell) {
        return str_replace(array(',',chr(194).chr(160)), array('.',''), $cell);
    }
}

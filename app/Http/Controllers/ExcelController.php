<?php

namespace App\Http\Controllers;

use Excel;
/**
 * 此文件只做参考使用,参考完成立即删除
 * @param int $flag
 * @return array|bool|mixed
 */
class ExcelController extends Controller
{
    public function export() {
        $cellData = [
            ['学号', '姓名', '成绩'],
            ['10001', 'AAAAA', '99'],
            ['10002', 'BBBBB', '92'],
            ['10003', 'CCCCC', '95'],
            ['10004', 'DDDDD', '89'],
            ['10005', 'EEEEE', '96'],
        ];
        Excel::create('学生成绩', function ($excel) use ($cellData) {
            $excel->sheet('score', function ($sheet) use ($cellData) {
                $sheet->rows($cellData);
            });
        })->export('xls');
    }

    public function import() {
        $filePath = 'storage/exports/' . iconv('UTF-8', 'GBK', '123') . '.xls';
        Excel::load($filePath, function ($reader) {
            $data = $reader->all();
            dd($data);
        });
    }
}
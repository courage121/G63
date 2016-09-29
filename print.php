<?php
//if($isdebug==false)
{
	$time_start = microtime_float();
$n=0;
$sheetcount=0;
$objPHPExcel->setActiveSheetIndex($sheetcount);
$sheetcount++;
$objPHPExcel->getActiveSheet()->setTitle(iconv("gb2312","utf-8",'成交明细'));
$profit1 = array();
$profit2 = array();
$profit3 = 0;
$data1 = array();
$data2 = array();
//var_dump($chengjiao);
foreach($chengjiao as $k=>$v)
{
	$data1["".$v['p'].""] = (float)$v['p'];
}
$data1 = array_values($data1);
rsort($data1);
$n2 = count($chengjiao);
$data3 = array_slice($colindex,1,$n2);
$lastrow = array_slice($colindex,$n2,1);
$data4 = array();
$n = 1;
$data2 = array();
$objPHPExcel->getActiveSheet()->setCellValue('B1', $w);
if(isset($argv) && $argv[6]==1)
{
print "需要循环:".(max($data1)+$minmove*$w-(min($data1)-$minmove*$w)).",".key($lastrow)."\n";
}
$ceil = max($data1)+$minmove*$w;
for($i = 0;$i<=(max($data1)-min($data1))/($minmove*$w)+1;$i++)
//foreach($data1 as $k=>$v)
{
	//print "循环:".$i."\n";
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $ceil);
	$data2[$ceil."s"] = $n;
	//$data4[$i."s"] = 0;
	$objPHPExcel->getActiveSheet()->getRowDimension( $n )->setRowHeight(12);
	$n++;
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $ceil-$minmove*$w);
	$data2[($ceil-$minmove*$w)."b"] = $n;
	
	//$data4[$i-$minmove*$w."b"] = 0;
	
	//$time_start1 = microtime_float();
	$objPHPExcel->getActiveSheet()->getStyle('A'.$n)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	$objPHPExcel->getActiveSheet()->getRowDimension( $n )->setRowHeight(12);
	if(isset($argv) && $argv[6]==1)
	{
		//$time_end1 = microtime_float();
		//$time1 = $time_end1 - $time_start1;
		//print "生成EXCEL-1-1耗时:".$time1."\n";
	}
	
	//$time_start2 = microtime_float();
	$objPHPExcel->getActiveSheet()->getStyle('A'.$n.':'.key($lastrow).$n)->applyFromArray(
		array(
			
			'borders' => array(
				'bottom'     => array(
 					'style' => PHPExcel_Style_Border::BORDER_THIN
 				)
			)
		)
	);/**/
	$ceil = $ceil-$minmove*$w;
	$n++;
	/*
	if($argv[6]==1)
	{
		$time_end2 = microtime_float();
		$time2 = $time_end2- $time_start2;
		print "生成EXCEL-1-2耗时:".$time2."\n";
	}
	*/
}
unset($data1);
if(isset($argv) && $argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-1耗时:".$time."\n";
}
$time_start = microtime_float();
//var_dump($chengjiao);
//var_dump($data2);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
$objPHPExcel->getActiveSheet()->freezePane('B1');
//var_dump($data3);
$z=0;
foreach($data3 as $k=>$v)
{
	//print $k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]."=>".$chengjiao[$v-2]['d']."\n";
	$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
	$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

	$objPHPExcel->getActiveSheet()->setCellValue($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']], strtoupper($chengjiao[$v-2]['d']));
	
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->setAuthor('PHPExcel');
	$objCommentRichText = $objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun(iconv("gb2312","utf-8",'委托时间'));
	$objCommentRichText->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun("\r\n");
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun($chengjiao[$v-2]['t2']);
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun("\r\n");
	$objCommentRichText = $objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun(iconv("gb2312","utf-8",'成交时间'));
	$objCommentRichText->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun("\r\n");
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun($chengjiao[$v-2]['t']);
	$objCommentRichText = $objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun(iconv("gb2312","utf-8",'类型'));
	$objCommentRichText->getFont()->setBold(true);
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun("\r\n");
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->getText()->createTextRun($chengjiao[$v-2]['d1'].",".$chengjiao[$v-2]['profit']);
	$objPHPExcel->getActiveSheet()->getComment($k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']])->setHeight("150");

	if($chengjiao[$v-2]['d']=="b")
	{
		$objPHPExcel->getActiveSheet()->setCellValue($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]-1), $chengjiao[$v-2]['nt']);
		$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]-1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]-1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	}
	else
	{
		$objPHPExcel->getActiveSheet()->setCellValue($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]+1), $chengjiao[$v-2]['nt']);
		$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]+1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]+1))->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
	}
	$objPHPExcel->getActiveSheet()->getColumnDimension($k)->setWidth(3);
	if($chengjiao[$v-2]['d']=="b")
	{
		$data4[$chengjiao[$v-2]['p']][$z][0] = $k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']];
		$data4[$chengjiao[$v-2]['p']][$z][1] = "b";
		$z++;
	}
	else if($chengjiao[$v-2]['d']=="s")
	{
		$data4[$chengjiao[$v-2]['p']-$minmove*$w][$z][0] = $k.$data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']];
		$data4[$chengjiao[$v-2]['p']-$minmove*$w][$z][1] = "s";
		$z++;
	}
	if($chengjiao[$v-2]['d1']=="a-0")
	{
		$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]))->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
		$objPHPExcel->getActiveSheet()->getStyle($k.($data2[$chengjiao[$v-2]['p'].$chengjiao[$v-2]['d']]))->getFill()->getStartColor()->setARGB('FF006699');
	}
}
unset($data2);
/*
if(isset($argv) && $argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-2耗时:".$time."\n";
}
$time_start = microtime_float();
foreach($data4 as $k=>$v)
{
	$v = array_values($v);
	$pred=0;
	$n=0;
	//var_dump($v);
	for($i=1;$i<count($v);$i++)
	{
		if($v[$i][1]=="b" && $v[$i-1][1]=="s" && $n%2==0)
		{
			$objPHPExcel->getActiveSheet()->getStyle($v[$i][0].':'.$v[$i][0])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($v[$i][0].':'.$v[$i][0])->getFill()->getStartColor()->setARGB('FF006699');
			$objPHPExcel->getActiveSheet()->getStyle($v[$i-1][0].':'.$v[$i-1][0])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($v[$i-1][0].':'.$v[$i-1][0])->getFill()->getStartColor()->setARGB('FF006699');
			$pred = "b";
		}
		else if($v[$i][1]=="s" && $v[$i-1][1]=="b" && $n%2==0)
		{
			$objPHPExcel->getActiveSheet()->getStyle($v[$i][0].':'.$v[$i][0])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($v[$i][0].':'.$v[$i][0])->getFill()->getStartColor()->setARGB('FF006699');
			$objPHPExcel->getActiveSheet()->getStyle($v[$i-1][0].':'.$v[$i-1][0])->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
			$objPHPExcel->getActiveSheet()->getStyle($v[$i-1][0].':'.$v[$i-1][0])->getFill()->getStartColor()->setARGB('FF006699');
			$pred = "s";
		}
		$n++;
	}
}
unset($data4);
if(isset($argv) && $argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-3耗时:".$time."\n";
}
*/
$time_start = microtime_float();
$objPHPExcel->createSheet();
$objPHPExcel->setActiveSheetIndex($sheetcount);
$sheetcount++;

$objPHPExcel->getActiveSheet()->setTitle(iconv("gb2312","utf-8",'总结'));
$objPHPExcel->getActiveSheet()->setCellValue('A1',iconv("gb2312","utf-8",'序号'));
$objPHPExcel->getActiveSheet()->setCellValue('B1',iconv("gb2312","utf-8",'委托时间'));
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$objPHPExcel->getActiveSheet()->setCellValue('C1', iconv("gb2312","utf-8",'成交时间'));
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
$objPHPExcel->getActiveSheet()->setCellValue('D1', iconv("gb2312","utf-8",'方向'));
$objPHPExcel->getActiveSheet()->setCellValue('E1', iconv("gb2312","utf-8",'价格'));
$objPHPExcel->getActiveSheet()->setCellValue('F1', iconv("gb2312","utf-8",'净持仓'));
$objPHPExcel->getActiveSheet()->setCellValue('G1', iconv("gb2312","utf-8",'盈亏'));
$objPHPExcel->getActiveSheet()->setCellValue('H1', iconv("gb2312","utf-8",'手续费'));
$objPHPExcel->getActiveSheet()->setCellValue('I1', iconv("gb2312","utf-8",'累计盈亏'));
$n=2;
$z=0;

foreach($chengjiao as $k=>$v)
{
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$n,$z);
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$n,$v['t2']);
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$n,$v['t']);
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$n,strtoupper($v['d']));
	$objPHPExcel->getActiveSheet()->setCellValue('E'.$n,$v['p']);
	$objPHPExcel->getActiveSheet()->setCellValue('F'.$n,$v['nt']);
	if($v['d']=="b" )//|| $v['d']=="bk"
	{
		//$profit = $profit + $v['profit'];
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$n,$v['profit']);
	}
	else if($v['d']=="s")// || $v['d']=="sk"
	{
		//$profit = $profit + $v['profit'];
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$n,$v['profit']);
	}
	if($fee_type=="+")
		$profit3 = $profit3-$fee;
	else
		$profit3 = $profit3-$v['p']*$fee*$unit;
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$n,$profit3);
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$n,$v['profit']);
	$n++;
	$z++;
}
/*
$objPHPExcel->getActiveSheet()->setAutoFilter($objPHPExcel->getActiveSheet()->calculateWorksheetDimension());
$autoFilter = $objPHPExcel->getActiveSheet()->getAutoFilter();
$autoFilter->getColumn('F')
	->setFilterType(PHPExcel_Worksheet_AutoFilter_Column::AUTOFILTER_FILTERTYPE_CUSTOMFILTER)
    ->createRule()
		->setRule(
			PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_COLUMN_RULE_EQUAL,
			'0'
		)
		->setRuleType(PHPExcel_Worksheet_AutoFilter_Column_Rule::AUTOFILTER_RULETYPE_CUSTOMFILTER);
*/
$startindex;
$endindex;	
$z=0;

$valuesE = array();
$valuesI = array();
foreach ($objPHPExcel->getActiveSheet()->getRowIterator() as $row) {
    if ($objPHPExcel->getActiveSheet()->getRowDimension($row->getRowIndex())->getVisible()) {
     	if($z==0 && $row->getRowIndex()!=1)
		{
			$startindex = $row->getRowIndex();
			array_push($valuesE,$objPHPExcel->getActiveSheet()->getCell('E'.$row->getRowIndex())->getValue());
			array_push($valuesI,$objPHPExcel->getActiveSheet()->getCell('I'.$row->getRowIndex())->getValue());

			$z++;
		}
		else
			$endindex = $row->getRowIndex();  
		
    }
}
if(isset($argv) && $argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-4耗时:".$time."\n";
}
$time_start = microtime_float();
$objWorksheet = $objPHPExcel->getActiveSheet();

$dataseriesLabels = array(
	new PHPExcel_Chart_DataSeriesValues('String', '$E$1', NULL, 1),	
	new PHPExcel_Chart_DataSeriesValues('String', '$I$1', NULL, 1)
);
//	Set the X-Axis Labels
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
$xAxisTickValues = array(
	new PHPExcel_Chart_DataSeriesValues('String', '\''.iconv("gb2312","utf-8",'总结').'\'!$C$'.$startindex.':$C$'.$endindex, "", ($endindex-$startindex+1)),
	new PHPExcel_Chart_DataSeriesValues('String', '\''.iconv("gb2312","utf-8",'总结').'\'!$C$'.$startindex.':$C$'.$endindex, "", ($endindex-$startindex+1))	
);
//	Set the Data values for each data series we want to plot
//		Datatype
//		Cell reference for data
//		Format Code
//		Number of datapoints in series
//		Data values
//		Data Marker
$dataSeriesValues = array(
    new PHPExcel_Chart_DataSeriesValues('Number', '\''.iconv("gb2312","utf-8",'总结').'\'!$E$'.$startindex.':$E$'.$endindex, NULL, count($valuesE),array_values($valuesE),"none"),
	new PHPExcel_Chart_DataSeriesValues('Number', '\''.iconv("gb2312","utf-8",'总结').'\'!$I$'.$startindex.':$I$'.$endindex, NULL, count($valuesI),array_values($valuesI),"none")
);
$series = new PHPExcel_Chart_DataSeries(
	PHPExcel_Chart_DataSeries::TYPE_LINECHART,		// plotType
	PHPExcel_Chart_DataSeries::GROUPING_STACKED,	// plotGrouping
	range(0, count($dataSeriesValues)-1),			// plotOrder
	$dataseriesLabels,								// plotLabel
	$xAxisTickValues,								// plotCategory
	$dataSeriesValues,								// plotValues
	false,
	PHPExcel_Chart_DataSeries::STYLE_FILLED
);

//	Set the series in the plot area
$plotarea = new PHPExcel_Chart_PlotArea(NULL, array($series));
//	Set the chart legend
$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_TOPRIGHT, NULL, false);

$title = new PHPExcel_Chart_Title($_REQUEST['contracts']."-".iconv("gb2312","utf-8",'行情与收益曲线('.$_REQUEST['startdate'].'-'.$_REQUEST['enddate'].')'));
$yAxisLabel = new PHPExcel_Chart_Title(iconv("gb2312","utf-8",'行情'));


//	Create the chart
$chart = new PHPExcel_Chart(
	'chart1',		// name
	$title,			// title
	$legend,		// legend
	$plotarea,		// plotArea
	false,			// plotVisibleOnly
	0,				// displayBlanksAs
	NULL,			// xAxisLabel
	$yAxisLabel		// yAxisLabel
);

//	Set the position where the chart should appear in the worksheet
$chart->setTopLeftPosition('J1');
$chart->setBottomRightPosition('Z120');

//	Add the chart to the worksheet
$objWorksheet->addChart($chart);
if(isset($argv) && $argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-5耗时:".$time."\n";
}
$time_start = microtime_float();
$objPHPExcel->createSheet();
$objPHPExcel->setActiveSheetIndex($sheetcount);
$sheetcount++;

$objPHPExcel->getActiveSheet()->setTitle(iconv("gb2312","utf-8",'委托'));
$n=1;
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
foreach($weituo as $k=>$v)
{
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$n,$k);
	$objPHPExcel->getActiveSheet()->setCellValue('B'.$n,$v['t']);
	$objPHPExcel->getActiveSheet()->setCellValue('C'.$n,$v['p']);
	$objPHPExcel->getActiveSheet()->setCellValue('D'.$n,strtoupper($v['d1']));
	$n++;
}
//$objPHPExcel->getActiveSheet()->setCellValue('H'.$n,"=SUM(H2:H".($n-1).")");
//$objPHPExcel->getActiveSheet()->setCellValue('I'.$n,"=SUM(I2:I".($n-1).")");
$objPHPExcel->setActiveSheetIndex(1);
if(isset($argv) && $argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-6耗时:".$time."\n";
}
$time_start = microtime_float();
$filename = iconv("gb2312","utf-8","成交明细");
if(!isset($argv) && $isdebug==false)
{
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$_REQUEST['contracts'].'('.$_REQUEST['startdate'].'-'.$_REQUEST['enddate'].')-'.$blance.'-'.$w.'('.time().').xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');
}
/*
// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0
*/
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->setIncludeCharts(true);
if(!isset($argv)  && $isdebug==false)
	$objWriter->save('php://output');
if(isset($argv) && $argv[6]==1  && $isdebug==false)
{
	$objWriter->save($_REQUEST['contracts'].'('.$_REQUEST['startdate'].'-'.$_REQUEST['enddate'].')-'.$blance.'-'.$w.'('.time().').xlsx');
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-7耗时:".$time."\n";
}
/**/
}
?>
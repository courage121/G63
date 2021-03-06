<?php

/** Error reporting */
error_reporting(E_ALL);

/** PHPExcel */
require_once '../Classes/PHPExcel.php';
require_once '../colindex.php';
require_once '../fees.php';
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip;
// Create new PHPExcel object
$objPHPExcel = new PHPExcel();

// Set properties

$objPHPExcel->getProperties()->setCreator(iconv("gb2312","utf-8",'合算'))
							 ->setLastModifiedBy("XH")
							 ->setTitle("Office 2007 XLSX Test Document")
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
							 ->setKeywords("office 2007 openxml php")
							 ->setCategory("Test result file");
// Database user
define("OCC_DB_USER","root");

// Database password
define("OCC_DB_PASSWORD","");

// Database hostname
define("OCC_DB_HOST","localhost");

// Database name
define("OCC_DB_NAME","level2");

mysql_connect(OCC_DB_HOST,OCC_DB_USER,OCC_DB_PASSWORD);

mysql_select_db(OCC_DB_NAME);
mysql_query("set names gb2312");

$isdebug = 0;
//初始化数组
$n = 800;
//格宽
if(empty($_REQUEST))
{
	$_REQUEST['contracts'] = $argv[1];
	$_REQUEST['startdate'] = $argv[2];
	if($argv[3]==0)
		$_REQUEST['enddate'] = "2016-12-31";
	else
		$_REQUEST['enddate'] = $argv[3];
	$blance = $_REQUEST['blance'] = $argv[4];
	$w = $_REQUEST['w'] = $argv[5];
	$_REQUEST['closetoday'] = 1;
//$unit = $_REQUEST['u'];
//$fee = $
$minmove = $fees[substr($_REQUEST['contracts'],0,-4)]['m'];
//初始化平衡价
$blance = $_REQUEST['blance'];
$unit = $fees[substr($_REQUEST['contracts'],0,-4)]['u'];
if($_REQUEST['closetoday']!=1)
	$fee = $fees[substr($_REQUEST['contracts'],0,-4)]['f']/2;
else
	$fee = $fees[substr($_REQUEST['contracts'],0,-4)]['f'];
$fee_type = $fees[substr($_REQUEST['contracts'],0,-4)]['t'];
}else
{
$w = $_REQUEST['w'];
//$unit = $_REQUEST['u'];
//$fee = $
$minmove = $fees[substr($_REQUEST['contracts'],0,-4)]['m'];
//初始化平衡价
$blance = $_REQUEST['blance'];
$unit = $fees[substr($_REQUEST['contracts'],0,-4)]['u'];
if($_REQUEST['closetoday']!=1)
	$fee = $fees[substr($_REQUEST['contracts'],0,-4)]['f']/2;
else
	$fee = $fees[substr($_REQUEST['contracts'],0,-4)]['f'];
$fee_type = $fees[substr($_REQUEST['contracts'],0,-4)]['t'];
}
$maxloss = 1;
$highestB = 0;
$highestS = 0;
$lowestS = 9999999;
$lowestB = 9999999;
$isH = 0;
$isL = 0;

$profit = 0;
for($i=0;$i<=$n;$i++)
{
	$up["".($blance+$i*$minmove*$w).""]['b'] = 0;
	$up["".($blance+$i*$minmove*$w).""]['s'] = 0;
}

//初始化成交队列
$chengjiao = array();
$weituo = array();
$wt = array();
$data5 = array();
$chicang = 0;
//委托索引z1
//成交索引z2
$z1 = 0;
$z2 = 0;
$prebidprice = 0;
$tick = array();
$time_start = microtime_float();
$q = 'select date,askprice1,bidprice1 from '.$_REQUEST['contracts'].' where date>="'.$_REQUEST['startdate'].'" and date<="'.$_REQUEST['enddate'].'"';//and date<"2016/08/25 11:05:54.492"
$r = mysql_query($q);
$z4 = 0;

while($l = mysql_fetch_array($r))
{
	if($l['bidprice1']!=$prebidprice)
	{
		$tick[$z4]['date'] = $l['date'];
		$tick[$z4]['bidprice1'] = $l['bidprice1'];
		$tick[$z4]['askprice1'] = $l['askprice1'];
		$prebidprice = $l['bidprice1'];
		$z4++;
	}
}
mysql_free_result($r);
if($argv[6]==1)
{
$time_end = microtime_float();
$time = ($time_end - $time_start);
print "数据库查询耗时:".$time."\n";
}
$time_start = microtime_float();
foreach($tick as $k=>$l)
{
	

	//if($l['bidprice1']>=$blance)
	{
		//print "检查委托开始(".$l['date'].")\n";
		//var_dump($weituo);
		foreach($weituo as $k => $v)
		{
			if($v['d'] != "0" && $l['bidprice1']<$v['p'] && substr($v['d'],0,1) == "b")
			{
				if($v['p']>$highestB)
				{
					$highestB = $v['p'];
					$isH = 1;
					$isL = 0;
				}
				if($v['p']<$lowestB)
					$lowestB = $v['p'];
				$weituo[$k]['d'] = "0";
				
				$chicang++;
				
				$chengjiao[$z2]['t'] = $l['date'];
				$chengjiao[$z2]['t2'] = $v['t'];
				$chengjiao[$z2]['d'] = $v['d'];
				$chengjiao[$z2]['p'] = $v['p'];
				$chengjiao[$z2]['nt'] = $chicang;
				$chengjiao[$z2]['d1'] = $v['d1'];
				$chengjiao[$z2]['profit'] = calprofit($chengjiao,$v['p']);
				
				$z2++;
				$up["".($v['p']).""][$v['d']] = 1;
				$up["".($v['p']).""] = checkinit($up["".($v['p']).""],&$data5);
				debugout($z1."		".($v['p'])."deal1 ".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p'])."deal1,".$v['d']."\n",$isdebug);
				unset($weituo[$k]);
				
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']>=$v['p'] && substr($v1['d'],0,1) == "s")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				if(!($chicang==2*$maxloss && $isL==1 && $up["".($v['p']).""]['b']==0))
				{
					debugout($z1."	".($v['p']+$w*$minmove)."	s	".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p']+$w*$minmove)." A1,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $v['p']+$w*$minmove;
					$wt['d'] = $weituo[$z1]['d'] = "s";
					$weituo[$z1]['d1'] = "s-5";
					$weituo[$z1]['t'] = $l['date'];	
					$z1++;
				}
			}
			else if($v['d'] != "0" && $l['askprice1']>$v['p'] && substr($v['d'],0,1) == "s")
			{	
				if($v['p']<$lowestS)
				{
					$lowestS = $v['p'];	
					$isH = 0;
					$isL = 1;
				}
				if($v['p']>$highestS)
					$highestS = $v['p'];		
				$weituo[$k]['d'] = "0";
				$chicang--;
				$chengjiao[$z2]['t'] = $l['date'];
				$chengjiao[$z2]['t2'] = $v['t'];
				$chengjiao[$z2]['d'] = $v['d'];
				$chengjiao[$z2]['p'] = $v['p'];
				$chengjiao[$z2]['nt'] = $chicang;
				$chengjiao[$z2]['d1'] = $v['d1'];
				$chengjiao[$z2]['profit'] = calprofit($chengjiao,$v['p']);
				$z2++;
				$up["".($v['p']-$w*$minmove).""][$v['d']] = 1;	
				$up["".($v['p']-$w*$minmove).""] = checkinit($up["".($v['p']-$w*$minmove).""],&$data5);
				debugout($z1."		".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p'])."deal2,".$v['d']."\n",$isdebug);
				unset($weituo[$k]);
				
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				//if(!($chicang==-$minmove*$w && $isH==1 && $up["".($v['p']-$w*$minmove]['s']==0 && $up["".($v['p']-$w*$minmove]['b']==0))
				if(!($chicang==-2*$maxloss && $isH==1))
				{
					debugout($z1."	".($v['p']-$w*$minmove)."	b	".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p']-$w*$minmove)." B1,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $v['p']-$w*$minmove;
					$wt['d'] = $weituo[$z1]['d'] = "b";
					$weituo[$z1]['d1'] = "b-5";
					$weituo[$z1]['t'] = $l['date'];
					$z1++;
				}
			}				
		}
		//print "检查委托结束\n";
		//if($up["".($l['bidprice1']]['b']==0 && $up["".($l['bidprice1']]['s']==0)
		if($l['date']=="2016-08-15 11:26:31" && $isdebug)
		{
			//var_dump($up["".($l['askprice1']-$minmove*$w]);
			//var_dump($up["".($l['askprice1']]['s']);
			print substr($chengjiao[$z2-1]['d'],0,1)."\n";
			print $up[$v1['p']-$minmove*$w]['b']."\n";
			//var_dump($weituo);
			print "净持仓:".$chicang."\n";
			print $l['bidprice1']."\n";
			print $isH."\n";
			print $isL."\n";
			//print max($chengjiao[$z2-1]['p'],$l['bidprice1']+$minmove*$w)."\n";
			var_dump($up["".($l['bidprice1']).""]);
			$chicang <= $maxloss && $chicang>=-2*$maxloss && !empty($chengjiao) == true && substr($chengjiao[$z2-1]['d'],0,1) == "s" 	&& isset($up["".($l['bidprice1']).""]) && checkweituo($weituo,$l['bidprice1'],"b",$minmove,$w) && $up["".($l['bidprice1']).""]['b']==0 && ($l['bidprice1']>$highestB || $l['bidprice1']<$highestS) && ($isH == 0 || $l['bidprice1']>$highestB )?print "1\n":print "-1\n";
			
			$chicang <= $maxloss && $chicang>=-2*$maxloss?print "1\n":print "-1\n";
			substr($chengjiao[$z2-1]['d'],0,1) == "s"?print "1\n":print "-1\n";
			checkweituo($weituo,$l['bidprice1'],"b",$minmove,$w)?print "1\n":print "-1\n";
			$up["".($l['bidprice1']).""]['b']==0?print "1\n":print "-1\n";
			$up["".($l['askprice1']-$minmove*$w).""]['s']==0?print "1\n":print "-1\n";
			($l['bidprice1']>$highestB || $l['bidprice1']<$highestS)?print "1\n":print "-1\n";
			($isH == 0 || $l['bidprice1']>$highestB )?print "1\n":print "-1\n";
		}
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		{

			if(empty($chengjiao) && isset($up["".($l['bidprice1']).""]) )
			{
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				debugout($z1."	".$l['bidprice1']."	b	".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." C1,".$z1."	\n",$isdebug);
				$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1'];
				$wt['d'] = $weituo[$z1]['d'] = "b";
				$weituo[$z1]['d1'] = "a-0";
				$weituo[$z1]['t'] = $l['date'];
				$z1++;
			}
			//持仓[-2,1],净持仓为负或没有达到最大正持仓2
			//前一个成交为卖
			//新高或者新低还没有新高的过程中
			//交易的区间没有多单
			//$chicang <= $maxloss => $chicang <= 0
			else if($chicang <= $maxloss && $chicang>=-2*$maxloss && !empty($chengjiao) == true && substr($chengjiao[$z2-1]['d'],0,1) == "s" 	&& isset($up["".($l['bidprice1']).""]) && checkweituo($weituo,$l['bidprice1'],"b",$minmove,$w) && $up["".($l['bidprice1']).""]['b']==0 && ($l['bidprice1']>$highestB || $l['bidprice1']<$highestS) && ($isH == 0 || $l['bidprice1']>$highestB ))
			{
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				debugout($z1."	".$l['bidprice1']."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." D1,".$z1."	\n",$isdebug);
				$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1'];
				$wt['d'] = $weituo[$z1]['d'] = "b";
				$weituo[$z1]['d1'] = "b-1";
				$weituo[$z1]['t'] = $l['date'];
				$z1++;
			}
			//净持仓为负
			//前一个成交为买
			//交易区间有空单,没有多单
			else if($chicang<=-$maxloss && !empty($chengjiao) == true  	&& isset($up["".($l['bidprice1']).""]) && checkweituo($weituo,$l['bidprice1'],"b",$minmove,$w) && (($up["".($l['bidprice1']).""]['b']==0 && $up["".($l['bidprice1']).""]['s']==0 && $isH==0 && substr($chengjiao[$z2-1]['d'],0,1) == "b") || ($up["".($l['bidprice1']).""]['b']==0 && $up["".($l['bidprice1']).""]['s']==1)))
			{
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				debugout($z1."	".$l['bidprice1']."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." D1,".$z1."	\n",$isdebug);
				$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1'];
				$wt['d'] = $weituo[$z1]['d'] = "b";
				$weituo[$z1]['d1'] = "b-2";
				$weituo[$z1]['t'] = $l['date'];
				$z1++;
			}
			
			//$chicang >= -$maxloss => $chicang >= 0
			else if($chicang >= -$maxloss && $chicang<=2*$maxloss && !empty($chengjiao) == true && substr($chengjiao[$z2-1]['d'],0,1) == "b" && isset($up["".($l['askprice1']-$minmove*$w).""]) && checkweituo($weituo,$l['askprice1'],"s",$minmove,$w) && $up["".($l['askprice1']-$minmove*$w).""]['s']==0 && ($l['askprice1']<$lowestS || $l['askprice1']>$lowestB) && ($isL == 0 || $l['askprice1']<$lowestS))
			{
				foreach($weituo as $k1 => $v1)
				{
					if( $v1['p']=="417.5" && $l['date']=="2016-08-15 09:00:12" && $isdebug)
					{
						print "净持仓:".$chicang."\n";
						print "区间买持仓:".$up[$v1['p']-$minmove*$w]['b']."\n";
						$v1['d'] != "0"?print "1\n":print "-1\n";
						$v1['p']>=$l['askprice1']?print "1\n":print "-1\n";
						substr($v1['d'],0,1) == "s"?print "方向:1\n":print "方向:-1\n";
						($up[$v1['p']-$minmove*$w]['b']!=0 || $chicang==-$maxloss)?print "1\n":print "-1\n";
					}
					if($v1['d'] != "0" && $v1['p']>=$l['askprice1'] && substr($v1['d'],0,1) == "s")// && ($up[$v1['p']-$minmove*$w]['b']==0 || $chicang==-$maxloss))
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				debugout($z1."	".($l['askprice1'])."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: G1,".$z1."	\n",$isdebug);
				$wt['p'] = $weituo[$z1]['p'] = $l['askprice1'];
				$wt['d'] = $weituo[$z1]['d'] = "s";
				$weituo[$z1]['d1'] = "s-1";
				$weituo[$z1]['t'] = $l['date'];
				$z1++;
			}
			
			else if($chicang>=$maxloss && !empty($chengjiao) == true 	&& isset($up["".($l['bidprice1']).""]) && checkweituo($weituo,$l['bidprice1']+$minmove*$w,"s",$minmove,$w) && (($up["".($l['bidprice1']).""]['b']==0 && $up["".($l['bidprice1']).""]['s']==0 && $isL==0  && substr($chengjiao[$z2-1]['d'],0,1) == "s") || ($up["".($l['bidprice1']).""]['b']==1 && $up["".($l['bidprice1']).""]['s']==0)))
			{
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']>=$l['bidprice1']+$minmove*$w && substr($v1['d'],0,1) == "s")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				debugout($z1."	".($l['askprice1'])."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: G1,".$z1."	\n",$isdebug);
				$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$minmove*$w;
				$wt['d'] = $weituo[$z1]['d'] = "s";
				$weituo[$z1]['d1'] = "s-2";
				$weituo[$z1]['t'] = $l['date'];
				$z1++;
			}/**/
			/**/
			else if($chicang == 0)
			{
				if(isset($up["".($l['askprice1']-$minmove*$w).""]) && $up["".($l['askprice1']-$minmove*$w).""]['b']==1 && $up["".($l['askprice1']-$minmove*$w).""]['s']==0 &&  checkweituo($weituo,$l['askprice1'],"s",$minmove,$w))
				{
					foreach($weituo as $k1 => $v1)
					{
						if($v1['d'] != "0" && $v1['p']>=$l['askprice1']-$minmove*$w && substr($v1['d'],0,1) == "s")
						{
							$weituo[$k1]['d'] = "0";
							unset($weituo[$k1]);
						}
					}
					debugout($z1."	".($l['bidprice1']+$minmove*$w)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($l['bidprice1']+$minmove*$w)." E1,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $l['askprice1'];
					$wt['d'] = $weituo[$z1]['d'] = "s";
					$wt['n'] = $weituo[$z1]['n'] = 1;
					$weituo[$z1]['d1'] = "s-3";
					$weituo[$z1]['t'] = $l['date'];
					$z1++;
				}
				
				else if(isset($up["".($l['bidprice1']).""]) && $up["".($l['bidprice1']).""]['b']==0 && $up["".($l['bidprice1']).""]['s']==1 && checkweituo($weituo,$l['bidprice1'],"b",$minmove,$w))
				{
					foreach($weituo as $k1 => $v1)
					{
						if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
						{
							$weituo[$k1]['d'] = "0";
							unset($weituo[$k1]);
						}
					}
					debugout($z1."	".$l['bidprice1']."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." D1,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1'];
					$wt['d'] = $weituo[$z1]['d'] = "b";
					$wt['n'] = $weituo[$z1]['n'] = 1;
					$weituo[$z1]['d1'] = "b-3";
					$weituo[$z1]['t'] = $l['date'];
					$z1++;
				}
				/**/
			}
			//不只是新高才补区间
			else if($chicang == -$maxloss && substr($chengjiao[$z2-1]['d'],0,1) == "s" && isset($up["".($l['bidprice1']).""]) && $up["".($l['bidprice1']).""]['b']==1 && $up["".($l['bidprice1']).""]['s']==0 && checkweituo($weituo,$l['bidprice1']+$minmove*$w,"s",$minmove,$w) && ($l['bidprice1']+$minmove*$w != $wt['p']|| $wt['d'] != "s"))// && $l['bidprice1']>=$highestB
			{
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']>=$l['bidprice1']+$minmove*$w && substr($v1['d'],0,1) == "s")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				debugout($z1."	".($l['bidprice1']+$minmove*$w)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($l['bidprice1']+$minmove*$w)." E1,".$z1."	\n",$isdebug);
				$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$minmove*$w;
				$wt['d'] = $weituo[$z1]['d'] = "s";
				$weituo[$z1]['d1'] = "s-4";
				$weituo[$z1]['t'] = $l['date'];
				$z1++;
			}
			//不只是新低才补区间
			else if($chicang == $maxloss && substr($chengjiao[$z2-1]['d'],0,1) == "b" && isset($up["".($l['bidprice1']).""]) && $up["".($l['bidprice1']).""]['s']==1 && $up["".($l['bidprice1']).""]['b']==0  && checkweituo($weituo,$l['bidprice1'],"b",$minmove,$w) && ($l['bidprice1'] != $wt['p']|| $wt['d'] != "b"))//&& $l['bidprice1']+$minmove*$w<=$lowestS
			{
				foreach($weituo as $k1 => $v1)
				{
					if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				debugout($z1."	".$l['bidprice1']."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." D1,".$z1."	\n",$isdebug);
				$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1'];
				$wt['d'] = $weituo[$z1]['d'] = "b";
				$wt['n'] = $weituo[$z1]['n'] = 1;
				$weituo[$z1]['d1'] = "b-4";
				$weituo[$z1]['t'] = $l['date'];
				$z1++;
			}
		}
		
	}	
}
if($argv[6]==1)
	{
		$time_end = microtime_float();
		$time = $time_end - $time_start;
		print "计算耗时:".$time."\n";
	}
function checkinit(array $ar,array $data5)
{
	if($ar['b']*$ar['s']==0)
			return $ar;
	else
	{
		$ar['b'] = 0;
		$ar['s'] = 0;
	}
	return $ar;
	
}
function debugout($str,$b)
{
	if($b==true)
		print $str;
}
function checkweituo(array $ar,$p,$d,$minmove,$w,$debug=0)
{
	foreach($ar as $k1 => $v1)
	{

		if(($v1['p'] == $p && $v1['d'] == $d && $d=="b") || ($v1['p'] == $p+$minmove*$w && $v1['d'] == "s" && $d=="b") || ($v1['p'] == $p-$minmove*$w && $v1['d'] == "b" && $d=="s") || ($v1['p'] == $p && $v1['d'] == $d && $d=="s"))
			return false;
	}
	return true;
}
function calprofit(array $ar,$p)
{
	$profit = 0;
	foreach($ar as $k=>$v)
	{
		if($v['d']=="b")
		{
			$profit = $profit + $p-$v['p'];
		}
		else if($v['d']=="s")
		{
			$profit = $profit + ($v['p']-$p);
		}
	}
	return $profit;
}
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$n=0;
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if($isdebug==false)
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
foreach($chengjiao as $k=>$v)
{
	$data1[$v['p']] = $v['p'];
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
print "需要循环:".(max($data1)+$minmove*$w-(min($data1)-$minmove*$w)).",".key($lastrow)."\n";
for($i = max($data1)+$minmove*$w;$i>=min($data1)-$minmove*$w;)
{
	//print "循环:".$i."\n";
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $i);
	$data2[$i."s"] = $n;
	//$data4[$i."s"] = 0;
	$objPHPExcel->getActiveSheet()->getRowDimension( $n )->setRowHeight(12);
	$n++;
	$objPHPExcel->getActiveSheet()->setCellValue('A'.$n, $i-$minmove*$w);
	$data2[$i-$minmove*$w."b"] = $n;
	
	//$data4[$i-$minmove*$w."b"] = 0;
	
	//$time_start1 = microtime_float();
	$objPHPExcel->getActiveSheet()->getStyle('A'.$n)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
	$objPHPExcel->getActiveSheet()->getRowDimension( $n )->setRowHeight(12);
	if($argv[6]==1)
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
	$i = $i-$minmove*$w;
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
if($argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-1耗时:".$time."\n";
}
$time_start = microtime_float();
//var_dump($chengjiao);
//var_dump($data2);

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(5);
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
}
unset($data2);
if($argv[6]==1)
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
if($argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-3耗时:".$time."\n";
}
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
		$profit = $profit + $blance-$v['p'];
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$n,$blance-$v['p']);
	}
	else if($v['d']=="s")// || $v['d']=="sk"
	{
		$profit = $profit + $v['p']-$blance;
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$n,$v['p']-$blance);
	}
	if($fee_type=="+")
		$profit3 = $profit3-$fee;
	else
		$profit3 = $profit3-$v['p']*$fee*$unit;
	$objPHPExcel->getActiveSheet()->setCellValue('H'.$n,$profit3);
	$objPHPExcel->getActiveSheet()->setCellValue('I'.$n,$profit);
	$n++;
	$z++;
}

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
if($argv[6]==1)
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
if($argv[6]==1)
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
$objPHPExcel->setActiveSheetIndex(0);
if($argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-6耗时:".$time."\n";
}
$time_start = microtime_float();
$filename = iconv("gb2312","utf-8","成交明细");
/*
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$_REQUEST['contracts'].'('.$_REQUEST['startdate'].'-'.$_REQUEST['enddate'].')-'.$blance.'-'.$w.'.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0
*/
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->setIncludeCharts(true);
//$objWriter->save('php://output');
$objWriter->save($_REQUEST['contracts'].'('.$_REQUEST['startdate'].'-'.$_REQUEST['enddate'].')-'.$blance.'-'.$w.'('.time().').xlsx');
if($argv[6]==1)
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	print "生成EXCEL-7耗时:".$time."\n";
}
/**/
}
?>
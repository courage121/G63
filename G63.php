<?php
//SELECT date,bidprice1,askprice1 FROM level2.j1701 j where date>="2016-09-02 08" and date<="2016-09-02 16";
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

//初始化数组
$n = 800;
//格宽
if(empty($_REQUEST))
{
	$_REQUEST['contracts'] = strtolower($argv[1]);
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
if(isset($_REQUEST['debug']) && $_REQUEST['debug']=="1")
{
	$isdebug = true;
}
else
	$isdebug = false;
if(isset($_REQUEST['maxloss']))
	$maxloss = $_REQUEST['maxloss'];
else
	$maxloss = 1;
$highestB = 0;
$highestS = 0;
$lowestS = 9999999;
$lowestB = 9999999;
$isH = 0;
$isL = 0;
$init = true;
$checkS = 9999999;
$checkB = 0;
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
$level0 = 0;
$para = 0.99382;
//委托索引z1
//成交索引z2
$z1 = 0;
$z2 = 0;
$prebidprice = 0;
$preaskprice = 0;
$tick = array();
$time_start = microtime_float();
$q = 'select date,askprice1,bidprice1 from '.$_REQUEST['contracts'].' where date>="'.$_REQUEST['startdate'].'" and date<="'.$_REQUEST['enddate'].'"';//and date<"2016/08/25 11:05:54.492"
$r = mysql_query($q);
$z4 = 0;

while($l = mysql_fetch_array($r))
{
	if($l['bidprice1']!=$prebidprice || $l['askprice1']!=$preaskprice)
	{
		$tick[$z4]['date'] = $l['date'];
		$tick[$z4]['bidprice1'] = $l['bidprice1'];
		$tick[$z4]['askprice1'] = $l['askprice1'];
		$prebidprice = $l['bidprice1'];
		$preaskprice = $l['askprice1'];
		$z4++;
	}
}
mysql_free_result($r);
if(isset($argv) && $argv[6]==1)
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
			if($v['d'] != "0" && ($l['askprice1']<=$v['p'] || $l['bidprice1']<$v['p']) && substr($v['d'],0,1) == "b")
			{
				if($init == true)
					$init = false;
				if($v['p']>$highestB 
				//|| $v['p']>$lowestS*(1+1-$para)
				)
				{
					$highestB = $v['p'];
					$isH = 1;
					$isL = 0;
				}
				
				if($v['p']<$lowestB)
					$lowestB = $v['p'];
				if($highestB!=0 && $lowestS!=9999999)
					$level0 = ($highestB+$lowestS)/2;
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
				$up["".($v['p']).""][$v['d']] = $up["".($v['p']).""][$v['d']]+1;
				$tmp = checkinit($up,$v['p'],1,$minmove,$w);
				if($tmp!=NULL)
				{
					$up["".($v['p']).""][$v['d']] = $up["".($v['p']).""][$v['d']]-1;
					$up["".($tmp).""]['s'] = $up["".($tmp).""]['s']-1;
				}
				//checkinit(&$up,$v['p'],1,$minmove,$w);
				debugout($z1."		".($v['p'])."deal1 ".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p'])."deal1,".$v['d']."\n",$isdebug);
				/*unset($weituo[$k]);
				
				foreach($weituo as $k1 => $v1)
				{
					//if($v1['d'] != "0" && $v1['p']!=$v['p'] && substr($v1['d'],0,1) == "s")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}

				{
					debugout($z1."	".($v['p']+$w*$minmove)."	s	".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p']+$w*$minmove)." A1,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $v['p']+$w*$minmove;
					$wt['d'] = $weituo[$z1]['d'] = "s";
					$weituo[$z1]['d1'] = "s-5";
					$weituo[$z1]['t'] = $l['date'];	
					$z1++;
				}
				*/
			}
			else if($v['d'] != "0" && ($l['bidprice1']>=$v['p'] || $l['askprice1']>$v['p']) && substr($v['d'],0,1) == "s")
			{	
				if($init == true)
					$init = false;
				if($v['p']<$lowestS && $v['p']<=$highestB
				//|| $v['p']<$highestB*$para
				)
				{
					$lowestS = $v['p'];						
					$isH = 0;
					$isL = 1;
				}
				if($v['p']<$checkS && $up["".($v['p']-$w*$minmove).""]['b']==0)
						$checkS = $v['p'];
				//if($v['p']>$checkB && $up["".($v['p']+$w*$minmove).""]['s']==0)
				//		$checkB = $v['p'];
				if($v['p']>$highestS)
					$highestS = $v['p'];	
				if($highestB!=0 && $lowestS!=9999999)
					$level0 = ($highestB+$lowestS)/2;	
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
				$up["".($v['p']).""][$v['d']] = $up["".($v['p']).""][$v['d']]+1;	
				$tmp = checkinit($up,$v['p'],-1,$minmove,$w);
				if($tmp!=NULL)
				{
					$up["".($v['p']).""][$v['d']] = $up["".($v['p']).""][$v['d']]-1;
					$up["".($tmp).""]['b'] = $up["".($tmp).""]['b']-1;
				}
				if((isset($argv) && $argv[6]==1) ||  $isdebug==1)
				{
					if($l['date'] == "2016-09-02 09:03:22")
					{
						//print "[".$l['bidprice1'].",".$l['askprice1']."]\n";
						//print $v['p']."\n";
						var_dump($tmp);
					}
				}

				debugout($z1."		".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p'])."deal2,".$v['d']."\n",$isdebug);
				/*unset($weituo[$k]);
				
				foreach($weituo as $k1 => $v1)
				{
					//if($v1['d'] != "0" && $v1['p']!=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				
				{
					debugout($z1."	".($v['p']-$w*$minmove)."	b	".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p']-$w*$minmove)." B1,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $v['p']-$w*$minmove;
					$wt['d'] = $weituo[$z1]['d'] = "b";
					$weituo[$z1]['d1'] = "b-5";
					$weituo[$z1]['t'] = $l['date'];
					$z1++;
				}
				*/
			}				
		}
		//print "检查委托结束\n";
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			if($init==true)
			{
				/**/
				$highestB = 0;
				$highestS = 0;
				$lowestS = 9999999;
				$isL = 0;
				$lowestB = 9999999;
				$isH = 0;
				
				
				
				$checkS = 9999999;
				$checkB = 0;
				
				for($i=0;$i<=$n;$i++)
				{
					$up["".($blance+$i*$minmove*$w).""]['b'] = 0;
					$up["".($blance+$i*$minmove*$w).""]['s'] = 0;
				}
				/**/
				
				$init = true;
				/**/
				if(isset($up["".($l['bidprice1']).""]))
				{
					$weituo = NULL;
					if(!(empty($weituo)))
					{
						foreach($weituo as $k1 => $v1)
						{
							//if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
							{
								$weituo[$k1]['d'] = "0";
								unset($weituo[$k1]);
							}
						}
					}
				
					debugout($z1."	".$l['bidprice1']."	b	".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']."A-0,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1'];
					$wt['d'] = $weituo[$z1]['d'] = "b";
					$weituo[$z1]['d1'] = "a-0";
					$weituo[$z1]['t'] = $l['date'];
					$z1++;
				}
				if(isset($up["".($l['askprice1']).""]))
				{
					$weituo = NULL;
					if(!(empty($weituo)))
					{
						foreach($weituo as $k1 => $v1)
						{
							//if($v1['d'] != "0" && $v1['p']<=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
							{
								$weituo[$k1]['d'] = "0";
								unset($weituo[$k1]);
							}
						}
					}
					debugout($z1."	".$l['askprice1']."	s	".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['askprice1']."A-0,".$z1."	\n",$isdebug);
					$wt['p'] = $weituo[$z1]['p'] = $l['askprice1'];
					$wt['d'] = $weituo[$z1]['d'] = "s";
					$weituo[$z1]['d1'] = "a-0";
					$weituo[$z1]['t'] = $l['date'];
					$z1++;
				}
			}
			else if($z2!=0)
			{
				for($i=0;$i<($l['askprice1']-$l['bidprice1'])/$minmove;$i++)
				{	
					//B-1
					(!empty($chengjiao) && $chicang==-$maxloss && isset($up["".($l['bidprice1']+$i*$minmove).""]))?$t0 = true:$t0 = false;
					if($t0 == true)
					{
						$t1 = $up["".($l['bidprice1']+$i*$minmove).""]['b']==0;
						$t2 = true;//substr($chengjiao[$z2-1]['d'],0,1) == "s";
						$t3 = true;
						$t6 = true;
						$t4 = true;
						$t5 = true;
						$t6 = true;
						$t7 = true;
						$t8 = true;
					}
					else
					{
						$t1 = $t2 = $t3 = $t4 = $t5 = $t6 = $t7 = $t8 = false;
					}
					//S-1
					(!empty($chengjiao) && $chicang==$maxloss && isset($up["".($l['askprice1']-$i*$minmove).""]))?$t100 = true:$t100 = false;
					if($t100 == true)
					{
						$t101 = $up["".($l['askprice1']-$i*$minmove).""]['s']==0 ;
						$t102 = true;//substr($chengjiao[$z2-1]['d'],0,1) == "b" ;
						$t103 = true;
						$t104 = true;
						$t105 = true;
						$t106 = true;
						$t107 = true;
						$t108 = true;
					}
					else
					{
						$t101 = $t102 = $t103 = $t104 = $t105 = $t106 = $t107 = $t108 = false;
					}
					//B-2
					(!empty($chengjiao) && $chicang==0 && isset($up["".($l['bidprice1']+$i*$minmove).""]))?$t200 = true:$t200 = false;
					if($t200 == true)
					{
						$t201 = $up["".($l['bidprice1']+$i*$minmove+$minmove*$w).""]['s']==1;
						$t202 = ($l['bidprice1']+$i*$minmove!=$chengjiao[$z2-1]['p'] || substr($chengjiao[$z2-1]['d'],0,1) == "s");
						$t203 = true;
						$t204 = true;
						$t205 = true;
						$t206 = true;
						$t207 = true;
						$t208 = true;
					}
					else
					{
						$t201 = $t202 = $t203 = $t204 = $t205 = $t206 = $t207 = $t208 = false;
					}
					//S-2
					(!empty($chengjiao) && $chicang==0 && isset($up["".($l['askprice1']-$i*$minmove).""]))?$t300 = true:$t300 = false;
					if($t300 == true)
					{
						$t301 = $up["".($l['askprice1']-$i*$minmove-$minmove*$w).""]['b']==1 ;
						$t302 = ($l['askprice1']-$i*$minmove!=$chengjiao[$z2-1]['p'] || substr($chengjiao[$z2-1]['d'],0,1) == "b");
						$t303 = true;
						$t304 = true;
						$t305 = true;
						$t306 = true;
						$t307 = true;
						$t308 = true;
					}
					else
					{
						$t301 = $t302 = $t303 = $t304 = $t305 = $t306 = $t307 = $t308 = false;
					}
					//B-3
					$tmp = checkStatus4($up,($l['bidprice1']+$i*$minmove));
					(!empty($chengjiao) && $chicang==0 && isset($up["".($l['bidprice1']+$i*$minmove).""]))?$t400 = true:$t400 = false;
					if($t400 == true)
					{
						$t401 = $up["".($l['bidprice1']+$i*$minmove).""]['b']==0;
						$t402 = substr($chengjiao[$z2-1]['d'],0,1) == "b";
						$t403 = $l['bidprice1']+$i*$minmove!=$chengjiao[$z2-1]['p'];
						$t404 = true;//count($tmp['s'])==1;
						$t405 = true;
						$t406 = true;
						$t407 = true;
						$t408 = true;
					}
					else
					{
						$t401 = $t402 = $t403 = $t404 = $t405 = $t406 = $t407 = $t408 = false;
					}
					//S-3
					$tmp = checkStatus4($up,($l['askprice1']-$i*$minmove));
					(!empty($chengjiao) && $chicang==0 && isset($up["".($l['askprice1']-$i*$minmove).""]))?$t500 = true:$t500 = false;
					if($t500 == true)
					{
						$t501 = $up["".($l['askprice1']-$i*$minmove).""]['s']==0;
						$t502 = substr($chengjiao[$z2-1]['d'],0,1) == "s";
						$t503 = $l['askprice1']-$i*$minmove!=$chengjiao[$z2-1]['p'];
						$t504 = true;//count($tmp['s'])==1;
						$t505 = true;
						$t506 = true;
						$t507 = true;
						$t508 = true;
					}
					else
					{
						$t501 = $t502 = $t503 = $t504 = $t505 = $t506 = $t507 = $t508 = false;
					}
					if($l['date'] == "2016-09-02 09:55:28" && $isdebug==1)
					{
						$t400?print "t0 1\n": print "t0 0\n";
						$t401?print "t1 1\n": print "t1 0\n";
						$t402?print "t2 1\n": print "t2 0\n";
						$t403?print "t3 1\n": print "t3 0\n";
						$t404?print "t4 1\n": print "t4 0\n";
						$t405?print "t5 1\n": print "t5 0\n";
						$t406?print "t6 1\n": print "t6 0\n";
						$t407?print "t7 1\n": print "t7 0\n";
						$t408?print "t8 1\n": print "t8 0\n";
						print "[".$isH.",".$isL."]\n";
						print "[".$highestB.",".$lowestS."]\n";
						var_dump(checkStatus4($up,($l['bidprice1']+$i*$minmove)));
						exit;
					}
				
					if($t0  
					&& $t1
					&& $t2
					&& $t3
					&& $t4
					&& $t5
					&& $t6
					&& $t7
					&& $t8
					)
					{
						{
							foreach($weituo as $k1 => $v1)
							{
								//if($v1['d'] != "0" && $v1['p']!=$l['bidprice1']+$i*$minmove && substr($v1['d'],0,1) == "b")
								{
									$weituo[$k1]['d'] = "0";
									unset($weituo[$k1]);
								}
							}
							debugout($z1."	".($l['bidprice1']+$i*$minmove)."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." B-1,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "b";
							$weituo[$z1]['d1'] = "b-1";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t100  
					&& $t101
					&& $t102
					&& $t103
					&& $t104
					&& $t105
					&& $t106
					&& $t107
					&& $t108
					)
					{
						{
							foreach($weituo as $k1 => $v1)
							{
								//if($v1['d'] != "0" && $v1['p']!=$l['askprice1']-$i*$minmove && substr($v1['d'],0,1) == "s")
								{
									$weituo[$k1]['d'] = "0";
									unset($weituo[$k1]);
								}
							}
							debugout($z1."	".($l['askprice1']-$i*$minmove)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: S-1,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['askprice1']-$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "s";
							$weituo[$z1]['d1'] = "s-1";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t200  
					&& $t201
					&& $t202
					&& $t203
					&& $t204
					&& $t205
					&& $t206
					&& $t207
					&& $t208
					)
					{
						{
							foreach($weituo as $k1 => $v1)
							{
								//if($v1['d'] != "0" && $v1['p']!=$l['bidprice1']+$i*$minmove && substr($v1['d'],0,1) == "b")
								{
									$weituo[$k1]['d'] = "0";
									unset($weituo[$k1]);
								}
							}
							debugout($z1."	".($l['bidprice1']+$i*$minmove)."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." B-2,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "b";
							$weituo[$z1]['d1'] = "b-2";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t300  
					&& $t301
					&& $t302
					&& $t303
					&& $t304
					&& $t305
					&& $t306
					&& $t307
					&& $t308
					)
					{
						{
							foreach($weituo as $k1 => $v1)
							{
								//if($v1['d'] != "0" && $v1['p']!=$l['askprice1']-$i*$minmove && substr($v1['d'],0,1) == "s")
								{
									$weituo[$k1]['d'] = "0";
									unset($weituo[$k1]);
								}
							}
							debugout($z1."	".($l['askprice1']-$i*$minmove)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: S-2,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['askprice1']-$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "s";
							$weituo[$z1]['d1'] = "s-2";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t400  
					&& $t401
					&& $t402
					&& $t403
					&& $t404
					&& $t405
					&& $t406
					&& $t407
					&& $t408
					)
					{
						{
							foreach($weituo as $k1 => $v1)
							{
								//if($v1['d'] != "0" && $v1['p']!=$l['bidprice1']+$i*$minmove && substr($v1['d'],0,1) == "b")
								{
									$weituo[$k1]['d'] = "0";
									unset($weituo[$k1]);
								}
							}
							debugout($z1."	".($l['bidprice1']+$i*$minmove)."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." B-3,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "b";
							$weituo[$z1]['d1'] = "b-3";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t500  
					&& $t501
					&& $t502
					&& $t503
					&& $t504
					&& $t505
					&& $t506
					&& $t507
					&& $t508
					)
					{
						{
							foreach($weituo as $k1 => $v1)
							{
								//if($v1['d'] != "0" && $v1['p']!=$l['askprice1']-$i*$minmove && substr($v1['d'],0,1) == "s")
								{
									$weituo[$k1]['d'] = "0";
									unset($weituo[$k1]);
								}
							}
							debugout($z1."	".($l['askprice1']-$i*$minmove)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: S-3,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['askprice1']-$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "s";
							$weituo[$z1]['d1'] = "s-3";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					/*else if($t200)
					{
						foreach($weituo as $k1 => $v1)
						{
							//if($v1['d'] != "0" && $v1['p']!=$l['askprice1']-$i*$minmove && substr($v1['d'],0,1) == "s")
							{
								$weituo[$k1]['d'] = "0";
								unset($weituo[$k1]);
							}
						}
						debugout($z1."	".($l['askprice1']-$i*$minmove)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: S-3,".$z1."	\n",$isdebug);
						$wt['p'] = $weituo[$z1]['p'] = $l['askprice1']-$i*$minmove;
						$wt['d'] = $weituo[$z1]['d'] = "s";
						$weituo[$z1]['d1'] = "s-3";
						$weituo[$z1]['t'] = $l['date'];
						$z1++;
						$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						for($ii=0;$ii<($l['askprice1']-$l['bidprice1'])/$minmove;$ii++)
						{
							if(isset($up["".($l['bidprice1']+$ii*$minmove).""]))
							{
								debugout($z1."	".($l['bidprice1']+$ii*$minmove)."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." B-3,".$z1."	\n",$isdebug);
								$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$ii*$minmove;
								$wt['d'] = $weituo[$z1]['d'] = "b";
								$weituo[$z1]['d1'] = "b-3";
								$weituo[$z1]['t'] = $l['date'];
								$z1++;
								$ii=($l['askprice1']-$l['bidprice1'])/$minmove+1;
							}
						}
					}
					
					else if(!empty($chengjiao) == true 
					&& $chicang>=-$maxloss+1 
					&& isset($up["".($l['askprice1']-$i*$minmove).""]) 
					//&& checkweituo($weituo,$l['askprice1']-$i*$minmove,"s",$minmove,$w) 
					&& $up["".($l['askprice1']-$i*$minmove).""]['s']==0 
					&& $up["".($l['askprice1']-$i*$minmove-$minmove*$w).""]['b']==1
					//&& (!($isL==1 && $up["".($l['askprice1']-$i*$minmove).""]['s']==0 && checkUnDeal($up,"s",($l['askprice1']-$i*$minmove))) || $l['askprice1']-$i*$minmove<=$lowestS)
					&& ($l['askprice1']-$i*$minmove-$minmove*$w)<$highestB
					&& $isH==1
					//&& (substr($chengjiao[$z2-1]['d'],0,1) == "s" ) 
					//&& (substr($chengjiao[$z2-2]['d'],0,1) == "b" ) 
					//&& $chengjiao[$z2-1]['p'] <= $chengjiao[$z2-2]['p']
					)
					{
						{
							foreach($weituo as $k1 => $v1)
							{
								//if($v1['d'] != "0" && $v1['p']!=$l['askprice1']-$i*$minmove && substr($v1['d'],0,1) == "s")
								{
									$weituo[$k1]['d'] = "0";
									unset($weituo[$k1]);
								}
							}
							debugout($z1."	".($l['askprice1']-$i*$minmove)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: S-2,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['askprice1']-$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "s";
							$weituo[$z1]['d1'] = "s-2";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
						}
					}
					*/
					
				}
			}
	}
}
if(isset($argv) && $argv[6]==1)
	{
		$time_end = microtime_float();
		$time = $time_end - $time_start;
		print "计算耗时:".$time."\n";
	}
/*
*检查大于P的S
*
*检查小于P的B
*/
function checkUnDeal(array $ar,$d,$p,$t=false)
{
	foreach($ar as $k1 => $v1)
	{
		if(($d=="s" && $k1>$p && $v1['s'] == "1") || ($d=="b" && $k1<$p && $v1['b']=="1"))
			return true;
	}
	return false;
}
function checkStatus1(array $ar,$d,$p,$minG,$t=false)
{
	$z=0;
	foreach($ar as $k1 => $v1)
	{
		if($t==true)
		{
			print $d.",".$k1.">".$p.",".$v1['b']."\n";
		}
		if(($d=="b" && $k1>=$p && $v1['b'] == "1") || ($d=="s" && $k1<=$p && $v1['s']=="1"))
			$z++;
		if($z>=2)
			return true;
	}
	return false;
}
function checkStatus2(array $ar,$d,$p,$minG,$t=false)
{
	$z=0;
	foreach($ar as $k1 => $v1)
	{
		if($t==true)
		{
			print $d.",".$k1.">".$p.",".$v1['b']."\n";
		}
		if(($d=="b" && $k1-$p>=$minG && $v1['b'] == "1") || ($d=="s" && $p-$k1>=$minG && $v1['s']=="1"))
			return false;
	}
	return true;
}
function checkStatus3(array $ar)
{
	$H=0;
	$L=9999999;
	foreach($ar as $k1 => $v1)
	{
		if($k1>$H && $v1['b'] == "1")
			$H = $k1;
		if($k1<$L && $v1['s'] == "1")
			$L = $k1;
	}
	return $H-$L;
}
/*
*B
*
*   B
*       
*		(S)X
*
* S
*         
*    S
*/
function checkStatus4(array $ar,$p,$t=false)
{
	$B=array();
	$S=array();
	foreach($ar as $k1 => $v1)
	{
		if($v1['b'] == "1" && $k1>=$p)
			array_push($B,$k1);
		else if($v1['s'] == "1" && $k1<=$p)
			array_push($S,$k1);
		
	}
	//rsort($h);
	//sort($l);
	if($t==true)
	{
		//print_r($ar);
		print_r($B);
		print_r($S);
	}
	return array("b"=>$B,"s"=>$S);
}
/*
*B
*
*   B
*       
*		(B)X
*
* S
*         
*    S
*/
function checkStatus5(array $ar,$p,$t=false)
{
	$upB=array();
	$downB=array();
	$upS=array();
	$downS=array();
	foreach($ar as $k1 => $v1)
	{
		if($v1['b'] == "1" && $k1>=$p)
			array_push($upB,$k1);
		else if($v1['b'] == "1" && $k1<$p)
			array_push($downB,$k1);
		if($v1['s'] == "1" && $k1>$p)
			array_push($upS,$k1);
		else if($v1['s'] == "1" && $k1<=$p)
			array_push($downS,$k1);
		
	}
	//rsort($h);
	//sort($l);
	if($t==true)
	{
		//print_r($ar);
		print_r($upB);
		print_r($downB);
		print_r($upS);
		print_r($downS);
	}
	return array("upb"=>$upB,"downb"=>$downB,"ups"=>$upS,"downs"=>$downS);
}
/*
*   S
*	  (B)X
*S     |
*	   B
*/
function checkStatus6(array $ar,$d,$p,$minG,$t=false)
{
	foreach($ar as $k1 => $v1)
	{
		if($t==true)
		{
			print $d.",".$k1.">".$p.",".$v1['b']."\n";
		}
		if(($d=="b" && $k1>=$p && $v1['b'] == "1") || ($d=="s" && $k1<=$p && $v1['s']=="1"))
			return true;
	}
	return false;
}
/*
*
*
*
*
*
*/
function checkStatus7(array $ar,$d,$p,$minG,$t=false)
{
	$z=0;
	foreach($ar as $k1 => $v1)
	{
		if($t==true)
		{
			print $d.",".$k1.">".$p.",".$v1['b']."\n";
		}
		if(($d=="b" && $k1>=$p && $v1['b'] == "1") || ($d=="s" && $k1<=$p && $v1['s']=="1"))
			$z++;
		if($z>=2)
			return true;
	}
	return false;
}
function checkinit(array $up,$p,$d,$minmove,$w,$t=0)
{	
	$tmp = NULL;
	$maxB = 0;
	$minS = 9999999;
	if($d==1)
	{
		foreach($up as $k=>$v)
		{
			if($k<$minS && $k>$p && $v['s']>=1)
			{
				$tmp = $k;
				$minS = $k;
			}
		}
	}
	else if($d==-1)
	{
		foreach($up as $k=>$v)
		{
			if($k>$maxB && $k<$p && $v['b']>=1)
			{
				$tmp = $k;
				$maxB = $k;
			}
		}
	}
	return $tmp;	
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
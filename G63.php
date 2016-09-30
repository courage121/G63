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
				/*
				if($chicang==0)
				{
					
					$highestB = 0;
					$highestS = 0;
					$lowestS = 9999999;
					$lowestB = 9999999;
					$isH = 0;
					$isL = 0;
					
					for($i=0;$i<=$n;$i++)
					{
						$up["".($blance+$i*$minmove*$w).""]['b'] = 0;
						$up["".($blance+$i*$minmove*$w).""]['s'] = 0;
					}
				}
				*/
				$chengjiao[$z2]['t'] = $l['date'];
				$chengjiao[$z2]['t2'] = $v['t'];
				$chengjiao[$z2]['d'] = $v['d'];
				$chengjiao[$z2]['p'] = $v['p'];
				$chengjiao[$z2]['nt'] = $chicang;
				$chengjiao[$z2]['d1'] = $v['d1'];
				$chengjiao[$z2]['profit'] = calprofit($chengjiao,$v['p']);
				
				$z2++;
				$up["".($v['p']).""][$v['d']] = 1;
				checkinit(&$up,$v['p'],1,$minmove,$w);
				debugout($z1."		".($v['p'])."deal1 ".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p'])."deal1,".$v['d']."\n",$isdebug);
				unset($weituo[$k]);
				
				foreach($weituo as $k1 => $v1)
				{
					//if($v1['d'] != "0" && $v1['p']!=$v['p'] && substr($v1['d'],0,1) == "s")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				//if(!($chicang==2*$maxloss && $isL==1 && $up["".($v['p']).""]['b']==0))
				//if(($v['p']+$w*$minmove>=$level0) || $up["".($v['p']).""]['b']==1)
				//if(!checkStatus1($up,"s",$v['p'],$minmove*$w)
				//	&& !($isL==1 && $up["".($v['p']+$w*$minmove).""]['s']==0 && $up["".($v['p']).""]['b']==0)
				//	&& !checkUnDeal($up,"s",$v['p']+$w*$minmove)
				//))
				//)
				// || $chicang>=0
				/*
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
				/*if(checkStatus1($up,"b",$v['p']))
				{
					$lowestS = $v['p'];	
					$isH = 0;
					$isL = 1;
					for($i=0;$i<=$n;$i++)
					{
						$up["".($blance+$i*$minmove*$w).""]['b'] = 0;
						$up["".($blance+$i*$minmove*$w).""]['s'] = 0;
					}
				}
				
				if($chicang==0)
				{
					
					$highestB = 0;
					$highestS = 0;
					$lowestS = 9999999;
					$lowestB = 9999999;
					$isH = 0;
					$isL = 0;
					
					for($i=0;$i<=$n;$i++)
					{
						$up["".($blance+$i*$minmove*$w).""]['b'] = 0;
						$up["".($blance+$i*$minmove*$w).""]['s'] = 0;
					}
				}*/
				$chengjiao[$z2]['t'] = $l['date'];
				$chengjiao[$z2]['t2'] = $v['t'];
				$chengjiao[$z2]['d'] = $v['d'];
				$chengjiao[$z2]['p'] = $v['p'];
				$chengjiao[$z2]['nt'] = $chicang;
				$chengjiao[$z2]['d1'] = $v['d1'];
				$chengjiao[$z2]['profit'] = calprofit($chengjiao,$v['p']);
				$z2++;
				$up["".($v['p']).""][$v['d']] = 1;	
				if((isset($argv) && $argv[6]==1) ||  $isdebug==1)
				{
					if($l['date'] == "2016-07-01 22:29:32")
					{
						//print "[".$l['bidprice1'].",".$l['askprice1']."]\n";
						//print $v['p']."\n";
						//var_dump($up["".($v['p']).""]);
					}
				}
				checkinit(&$up,$v['p'],-1,$minmove,$w);
				if((isset($argv) && $argv[6]==1) ||  $isdebug==1)
				{
					if($l['date'] == "2016-07-01 22:29:32")
					{
						//print "[".$l['bidprice1'].",".$l['askprice1']."]\n";
						//print $v['p']."\n";
						//var_dump($up["".($v['p']).""]);
					}
				}
				debugout($z1."		".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".($v['p'])."deal2,".$v['d']."\n",$isdebug);
				unset($weituo[$k]);
				
				foreach($weituo as $k1 => $v1)
				{
					//if($v1['d'] != "0" && $v1['p']!=$l['bidprice1'] && substr($v1['d'],0,1) == "b")
					{
						$weituo[$k1]['d'] = "0";
						unset($weituo[$k1]);
					}
				}
				//if(!($chicang==-$minmove*$w && $isH==1 && $up["".($v['p']-$w*$minmove]['s']==0 && $up["".($v['p']-$w*$minmove]['b']==0))
				//if(!($chicang==-2*$maxloss && $isH==1))
				//if(($v['p']-$w*$minmove<=$level0) || $up["".($v['p']).""]['s']==1)
				//if(!checkStatus1($up,"b",$v['p'],$minmove*$w)
				//	&& !($isH==1 && $up["".($v['p']-$w*$minmove).""]['b']==0 && $up["".($v['p']).""]['s']==0)
				//	&& !($up["".($v['p']).""]['s']=="1" && checkStatus6($up,"s",$v['p']-$w*$minmove,$minmove*$w))
				//	&& (checkStatus5($up,"b",$v['p']-$w*$minmove) || $chicang==-$maxloss)
				//&& checkUnDeal($up,"s",$v['p']-$w*$minmove)
				//)) || $chicang<=0
				//)
				// 
				/*
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
			if(
			($init==true && isset($up["".($l['bidprice1']).""]))
			|| (!checkStatus1($up,"b") && !checkStatus1($up,"s") )
			)
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
				if(isset($up["".($l['bidprice1']).""]))
				{
					$weituo = NULL;
					
					/**/
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
			}
			else if($z2!=0)
			{
				for($i=0;$i<($l['askprice1']-$l['bidprice1'])/$minmove;$i++)
				{	
					//B-1
					(!empty($chengjiao) && $chicang<$maxloss-1 && isset($up["".($l['bidprice1']+$i*$minmove).""]))?$t0 = true:$t0 = false;
					if($t0 == true)
					{
						$t1 = $up["".($l['bidprice1']+$i*$minmove).""]['b']==0;
						$t2 = substr($chengjiao[$z2-1]['d'],0,1) == "s";
						$t3 = true;//($isL==1 || $l['bidprice1']+$i*$minmove>=$highestB || ($l['bidprice1']+$i*$minmove<$lowestS && $chicang<=$maxloss-1 && $chicang>=-$maxloss) || ($chicang<=$maxloss-1 && $chicang>=-$maxloss && $up["".($l['bidprice1']+$i*$minmove+$minmove*$w).""]['s']==1));
						$t6 = true;//(!($isH==1 && $up["".($l['bidprice1']+$i*$minmove).""]['b']==0 && checkUnDeal($up,"b",($l['bidprice1']+$i*$minmove))) 
							//		|| $l['bidprice1']+$i*$minmove>=$highestB
									//|| ($up["".($l['bidprice1']+$i*$minmove+$minmove*$w).""]['s']=="1" && checkUnDeal($up,"s",($l['bidprice1']+$i*$minmove+$minmove*$w)))
							//	);
						$t4 = true;//!($up["".($l['bidprice1']+$i*$minmove+$minmove*$w).""]['s']=="1" && checkStatus6($up,"s",$l['bidprice1']+$i*$minmove,$minmove*$w));
						$t5 = true;//(checkStatus5($up,"b",$l['bidprice1']+$i*$minmove) || $l['bidprice1']+$i*$minmove>=$highestB || $chicang==-$maxloss);//(!checkStatus5($up,$l['bidprice1']+$i*$minmove,$minmove*$w));
						$t6 = true;
						$t7 = true;
						$t8 = true;
					}
					else
					{
						$t1 = $t2 = $t3 = $t4 = $t5 = $t6 = $t7 = $t8 = false;
					}
					//S-1
					(!empty($chengjiao) && $chicang>-$maxloss+1 && isset($up["".($l['askprice1']-$i*$minmove).""]))?$t100 = true:$t100 = false;
					if($t100 == true)
					{
						$t101 = $up["".($l['askprice1']-$i*$minmove).""]['s']==0 ;
						$t102 = substr($chengjiao[$z2-1]['d'],0,1) == "b" ;
						$t103 = true;//($isH==1 || $l['askprice1']-$i*$minmove<=$lowestS || ($l['askprice1']-$i*$minmove>$highestB && $chicang>=-$maxloss+1 && $chicang<=$maxloss) || ($chicang>=-$maxloss+1 && $chicang<=$maxloss && $up["".($l['askprice1']-$i*$minmove-$minmove*$w).""]['b']==1)) 
						$t104 = true;//&& (!($isL==1 && $up["".($l['askprice1']-$i*$minmove).""]['s']==0 && checkUnDeal($up,"s",($l['askprice1']-$i*$minmove))) 
							//|| $l['askprice1']-$i*$minmove<=$lowestS 
							//|| ($up["".($l['askprice1']-$i*$minmove-$minmove*$w).""]['b']=="1" && checkUnDeal($up,"b",($l['askprice1']-$i*$minmove-$minmove*$w)))
						//)
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
					(!empty($chengjiao) && $chicang==$maxloss-1 && isset($up["".($l['bidprice1']+$i*$minmove).""]))?$t200 = true:$t200 = false;
					if($t200 == true)
					{
						$t201 = $up["".($l['bidprice1']+$i*$minmove).""]['b']==0;
						$t202 = substr($chengjiao[$z2-1]['d'],0,1) == "b";
						$t203 = checkStatus1($up,"b");
						$t206 = true;
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
					(!empty($chengjiao) && $chicang==-$maxloss+1 && isset($up["".($l['askprice1']-$i*$minmove).""]))?$t300 = true:$t300 = false;
					if($t300 == true)
					{
						$t301 = $up["".($l['askprice1']-$i*$minmove).""]['s']==0;
						$t302 = substr($chengjiao[$z2-1]['d'],0,1) == "s" ;
						$t303 = checkStatus1($up,"s");
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
					
					if($l['date'] == "2016-09-02 09:00:31" && $isdebug==1)
					{
						$t0?print "t0 1\n": print "t0 0\n";
						$t1?print "t1 1\n": print "t1 0\n";
						$t2?print "t2 1\n": print "t2 0\n";
						$t3?print "t3 1\n": print "t3 0\n";
						$t4?print "t4 1\n": print "t4 0\n";
						$t5?print "t5 1\n": print "t5 0\n";
						$t6?print "t6 1\n": print "t6 0\n";
						$t7?print "t7 1\n": print "t7 0\n";
						$t8?print "t8 1\n": print "t8 0\n";
						print "[".$isH.",".$isL."]\n";
						print "[".$highestB.",".$lowestS."]\n";
						checkStatus5($up,"b",$l['bidprice1']+$i*$minmove,true);
						exit;
					}
				
					if($t0 && $t1 && $t2 && $t3 && $t4 && $t5 && $t6 && $t7	&& $t8)
					{
						{
							foreach($weituo as $k1 => $v1){$weituo[$k1]['d'] = "0";unset($weituo[$k1]);}
							debugout($z1."	".($l['bidprice1']+$i*$minmove)."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." B-1,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "b";
							$weituo[$z1]['d1'] = "b-1";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t100 && $t101 && $t102	&& $t103 && $t104 && $t105 && $t106	&& $t107 && $t108)
					{
						{
							foreach($weituo as $k1 => $v1){$weituo[$k1]['d'] = "0";	unset($weituo[$k1]);}
							debugout($z1."	".($l['askprice1']-$i*$minmove)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: S-1,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['askprice1']-$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "s";
							$weituo[$z1]['d1'] = "s-1";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t200 && $t201 && $t202 && $t203 && $t204 && $t205 && $t206 && $t207 && $t208)
					{
						{
							foreach($weituo as $k1 => $v1){$weituo[$k1]['d'] = "0";unset($weituo[$k1]);}
							debugout($z1."	".($l['bidprice1']+$i*$minmove)."	b ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]:".$l['bidprice1']." B-2,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['bidprice1']+$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "b";
							$weituo[$z1]['d1'] = "b-2";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					else if($t300 && $t301 && $t302	&& $t303 && $t304 && $t305 && $t306	&& $t307 && $t308)
					{
						{
							foreach($weituo as $k1 => $v1){$weituo[$k1]['d'] = "0";	unset($weituo[$k1]);}
							debugout($z1."	".($l['askprice1']-$i*$minmove)."	s ".$chicang."|".$l['date']."[".$l['bidprice1'].",".$l['askprice1']."]: S-2,".$z1."	\n",$isdebug);
							$wt['p'] = $weituo[$z1]['p'] = $l['askprice1']-$i*$minmove;
							$wt['d'] = $weituo[$z1]['d'] = "s";
							$weituo[$z1]['d1'] = "s-2";
							$weituo[$z1]['t'] = $l['date'];
							$z1++;
							$i=($l['askprice1']-$l['bidprice1'])/$minmove+1;
						}
					}
					/*
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
function checkStatus1(array $ar,$d,$p=0,$t=false)
{
	foreach($ar as $k1 => $v1)
	{
		if(($d=="s" && $v1['s'] == "1") || ($d=="b" && $v1['b']=="1"))
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
function checkStatus4(array $ar,$d,$minG,$t=false)
{
	$hB=0;
	$hS=0;
	$lS=9999999;
	$lB=9999999;
	$nB=0;
	foreach($ar as $k1 => $v1)
	{
		if($k1>$hB && $v1['b'] == "1")
		{
			$hB = $k1;
			$nB++;
		}
		if($k1<$lS && $v1['s'] == "1")
			$lS = $k1;
		if($k1<$lB && $v1['b'] == "1")
			$lB = $k1;
		if($k1>$hS && $v1['s'] == "1")
			$hS = $k1;
	}
	if($t==true)
	{
		print $hB.",".$lS.",".$hS.",".$lB."\n";
		print $nB.",".($hS-$lB)/($hB-$lS)."\n";
	}
	if($nB==2 && $hB>$lS 
		//&& $hS>$lB 
		&& ($hS-$lB)/($hB-$lS)>=0.3 && ($hS-$lB)>=2*$minG)
		return true;
	else
		return false;
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
function checkStatus5(array $ar,$d,$p,$t=false)
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
	return array($upB,$downB,$upS,$downS);
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
	if($d==1 && $up["".$p.""]['b']==1 
		&& $up["".($p+$minmove*$w).""]['s']==1
		)
	{
		$up["".$p.""]['b'] = 0;
		$up["".($p+$minmove*$w).""]['s'] = 0;
	}
	else if($d==-1 && $up["".$p.""]['s']==1 && $up["".($p-$minmove*$w).""]['b']==1)
	{
		if($t=="2016-07-01 22:29:32")
		{
		print $p."\n";
		print ($p-$minmove*$w)."\n";
		}
		$up["".$p.""]['s'] = 0;
		$up["".($p-$minmove*$w).""]['b'] = 0;
	}
	/*
	else if($d==-1)
	{
		foreach($up as $k=>$v)
		{
			if($k<$p && $up["".$k.""]['b']==1)
			{
				$up["".$p.""]['s'] = 0;
				$up["".$k.""]['b'] = 0;
			}
				
		}
	}
	else if($d==1)
	{
		foreach($up as $k=>$v)
		{
			if($k>$p && $up["".$k.""]['s']==1)
			{
				$up["".$p.""]['b'] = 0;
				$up["".$k.""]['s'] = 0;
			}
				
		}
	}
	*/
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
include ("print.php");
?>
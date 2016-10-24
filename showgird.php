<?php
// Database user
define("OCC_DB_USER","root");

// Database password
define("OCC_DB_PASSWORD","");

// Database hostname
define("OCC_DB_HOST","localhost");

// Database name
define("OCC_DB_NAME","chengjiao");

mysql_connect(OCC_DB_HOST,OCC_DB_USER,OCC_DB_PASSWORD);

mysql_select_db(OCC_DB_NAME);
mysql_query("set names gb2312");

$mw = $_GET['mw'];
$q = 'select min(left(d,length(d)-1)) m1,max(left(d,length(d)-1)) m2 from '.$_GET['contract'].'';
$r = mysql_query($q);
$l = mysql_fetch_array($r);
$min = $l['m1']-$mw;
$max = $l['m2']+$mw;
print $min;
print $max;
$q = 'select * from '.$_GET['contract'].'';
$r = mysql_query($q);
$n = mysql_affected_rows();
print "<div id=\"content\" ><table border=\"1\">";
print "<tr><td></td>";
for($i=1;$i<=$n;$i++)
{
	print "<td>".$i."</td>";
}
print "</tr>";
$color2 = "EEF6FF";
$color1 = "FFFFFF";
for($i=1;$i<=($max-$min)/$mw+1;$i++)
{
	
		$t1 = explode('.',($max-$mw*($i-1))."s");
		if(count($t1)==1)
			$t = ($max-$mw*($i-1)).".0s";
		else
			$t = ($max-$mw*($i-1))."s";
		print "<tr bgcolor=\"".$color1."\"><td>".$t."</td>";
		for($j=1;$j<=$n;$j++)
		{
			$q1 = 'select * from '.$_GET['contract'].' where `index`='.$j.' and d="'.$t.'"';
			$r1 = mysql_query($q1);
			$n1 = mysql_affected_rows();
			$l1 = mysql_fetch_array($r1);
			if($n1==1)
			{
				print "<td width=\"2\">S</td>";
			}
			else
			{
				print "<td width=\"2\">&nbsp;</td>";
			}
		}
		print "</tr>";
	
		$t2 = explode('.',($max-$mw*($i))."b");
		if(count($t2)==1)
			$t = ($max-$mw*($i)).".0b";
		else
			$t = ($max-$mw*($i))."b";
		print "<tr bgcolor=\"".$color2."\"><td>".$t."</td>";
		for($j=1;$j<=$n;$j++)
		{
			$q1 = 'select * from '.$_GET['contract'].' where `index`='.$j.' and d="'.$t.'"';
			$r1 = mysql_query($q1);
			$n1 = mysql_affected_rows();
			$l1 = mysql_fetch_array($r1);
			if($n1==1)
			{
				print "<td width=\"2\">B</td>";
			}
			else
			{
				print "<td width=\"2\">&nbsp;</td>";
			}
		}
		print "</tr>";
	
	
}

print "</table></div>";

?>
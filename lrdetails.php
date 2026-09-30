<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(session_status() == PHP_SESSION_NONE) session_start();
include('session_expiry_page1.php');
$uname=@$_SESSION['usr_name'];
$display=@$_SESSION['display_name'];
$domestic=@$_SESSION['domestic'];
$cuno=@$_SESSION['cuno']??'CU1A03751';
$cuno1=	 @$cuno ? substr(@$cuno,3,9) : '';
$dpst= @$dpst ?? 0 ?? '';
include('checkelgi.php');
include('include/pdo_dpconn.php');
?>
<html><head><title>ELGI-Lorry Receipt Details</title>
<meta http-equiv="cache-control" content="max-age=0" />
<meta http-equiv="cache-control" content="no-cache" />
<meta http-equiv="expires" content="0" />
<meta http-equiv="expires" content="Tue, 01 Jan 1980 1:00:00 GMT" />
<meta http-equiv="pragma" content="no-cache" />
<link rel="stylesheet" type="text/css" href="dpstyle.css">
<script src="customer.js"></script>
<script src="excel.js"></script>
</head>
<body><center>
<?php
 require("head.php");
 require("menu.php");

 //$obj1=new head();
 //$obj1->display($display,$uname,'1','90%',0);
 $currentFile = @$_SERVER["SCRIPT_NAME"];
 $parts = Explode('/', $currentFile);
 $currentFile = $parts[count($parts) - 1];
 $obj=new menu();
 $obj->displaymenu(@$domestic,@$currentFile,'90%');
 
/*
 echo '<form name="f1"><p><table border=0 bgcolor=#EAEAEA width=90%>';
 echo '<tr><td align=center height=25><b>PRODUCT GROUP</b>&nbsp;&nbsp;<select name=dpst><option value=0>ALL PRODUCTS</option>';
  $result=pg_query($link,"select distinct m.dpst,d.dpst_desc from  lr_details m  left outer join dpst_master d on trim(m.dpst)=d.dpst_code::text where (cuno='$cuno' or cuno='$cuno1') and company!=600 order by m.dpst");
  while(list($code,$desc)=pg_fetch_row($result))
  {
      echo "<option value=".$code.">".$code."-[".$desc."]</option>";
  }
  echo "</select>&nbsp;&nbsp;<input type=button value='SHOW RECORDS' onClick=customer(4)><input type=button value='DOWNLOAD EXCEL FILE' onClick=excel(4)></td></tr></table></form>";
*/
  /*$query="select distinct(to_number(ordno,'9999999')) as aono,to_number(invno,'99999999') as invno,invdate,lrno,lrdate,tname,dpst,dpst_desc,invref,tcode from lrdetails left outer join dpst_master on trim(dpst)::numeric=dpst_code where (cuno='$cuno' or cuno='$cuno1') and divcode!='6' order by invdate desc";*/

  $query="select distinct c.dpst_desc,a.ordno,a.invref,a.invno,a.invdt,a.lrno,a.lrdate,a.tcode,a.tname,b.dpst from lr_details a,maintdealer b,dpst_master c  where trim(b.cuno)=:cuno and a.ordno=b.ordno and b.dpst=c.dpst_code::text  and a.company!=600 and a.company=b.company order by invdt desc";
  


  if (!($link instanceof PDO)) {
    echo '<p align="center"><b>LR details are unavailable because the dealer portal database did not connect.</b></p>';
    echo '</body></html>';
    exit;
  }

  $cuno = $cuno ?? 'CU1A03751';
  $result=$link->prepare($query);
  $result->bindParam(':cuno', $cuno, PDO::PARAM_STR);
  $result->execute();
  echo '<div id="output">';
  echo '<p><table border="1" bordercolor="#336699" width="90%" class="LGtable">';
  echo '<caption>LR DETAILS</caption>';
  echo '<tr><th>Product Group</th><th>AO Number</th><th>Invoice No</th><th>Despatch Date</th><th>Transporter</th><th>LR Number</th><th>LR Date</th></tr>';
 while($qryExe = $result->fetch(PDO::FETCH_ASSOC)){
	 $qryRes = array_values($qryExe);
	 list($dpstdesc,$aono,$invref,$invno,$invdt,$lrno,$lrdt,$tcode,$tname,$dpst)=$qryRes;
	 /*
	 $rs=$link->prepare("select url from lrwebsite where tcode=:tcode and length(url)>0");
	 $rs->bindParam(':tcode', $tcode, PDO::PARAM_STR);
	 $rs->execute();
	 $cnt = $rs->execute();
	 $getData = $rs->fetch(PDO::FETCH_ASSOC);
	 if($cnt>0)
	 {
		 $url=$getData['url'] ?? '';
		 $tnam="$tnam-<a href=$url target=\"_blank\"><img src=\"/images/url.png\"></a>";
	 }
	 */

	 echo "<tr align=\"right\"><td align=\"left\">$dpst-[$dpstdesc]</td><td align=\"center\"><a href=lrview.php?ordno=$aono&cuno=$cuno target=\"_blank\">$aono</a></td>
		 <td align=\"center\"><a href=despatchview1.php?invref=$invref&invno=$invno&cuno=$cuno target=\"_blank\">$invref-$invno</td>
		 <td align=\"center\">$invdt</td><td align=\"left\">$tname</td><td>$lrno</td><td>$lrdt</td></tr>";
	 $url='';
	 $tnam='';
 }
  echo '</table></div>';
?>
</body></html>

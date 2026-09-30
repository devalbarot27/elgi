<?php
if(session_status() == PHP_SESSION_NONE) session_start();
   include('session_expiry_page1.php');
   include('include/pdo_dpconn.php');
   include('include/pdo_obconn.php');
   $gPrice=0;
   $gSalesTax=0;
   $gTotal=0;
?>
<html><head><title>Despatch View</title><link rel="stylesheet" type="text/css" href="mystyle.css"></head>
<body>
<?php
   $invref=trim(pg_escape_string($_GET['invref']));
   $invno=trim(pg_escape_string($_GET['invno']));
   $cuno=trim(pg_escape_string($_GET['cuno']));
   $rs = $link->prepare("select distinct (cmp,ordno,posno) as a, invref,invno,invdate,ordno,ord_date,posno,item_desc,uom,qty,price,salestax,cmp from despatch where invref=:invref and invno=:invno and cuno=:cuno order by ordno,posno");
   $rs->bindParam(':invref', $invref, PDO::PARAM_STR);
   $rs->bindParam(':invno', $invno, PDO::PARAM_STR);
   $rs->bindParam(':cuno', $cuno, PDO::PARAM_STR);
   $rs->execute();
 if($rs->rowcount()>0)
 { 
   $qry = $link->prepare("select array(select distinct ordno from despatch where trim(invref)=:invref and invno=:invno and cuno=:cuno order by ordno) as arr");
   $qry->bindParam(':invref', $invref, PDO::PARAM_STR);
   $qry->bindParam(':invno', $invno, PDO::PARAM_STR);
   $qry->bindParam(':cuno', $cuno, PDO::PARAM_STR);
   $qry->execute();
   
   $newrs = $qry->fetch(PDO::FETCH_ASSOC);
   $aoarray = $newrs['arr'];
   $aoarray = str_replace("{","",$aoarray);
   $aoarray = str_replace("}","",$aoarray);

   $exe = $rs->fetch(PDO::FETCH_ASSOC);
   $invdate = $exe['invdate'];
   $uom = $exe['uom'];
   $cmp = $exe['cmp'];

   $dateRS = $link->prepare("select date_cmp('2017-06-30',:invdate) as diff_inv");
   $dateRS->bindParam(':invdate', $invdate, PDO::PARAM_STR);
   $dateRS->execute();
   $dateExe = $dateRS->fetch(PDO::PARAM_STR);
   $diff = $dateExe['diff_inv'];


   echo "<table border=1  cellpadding=6 align=center width=60%  bordercolor=#000000>";
   echo "<tr bgcolor=#C0C0C0><td colspan=7 align=center><b>DESPATCH DETAIL VIEW</b></td></tr>";
   echo "<tr><td bgcolor=#EAEAEA><b>Order Number(S)</b></td><td colspan=3>".$aoarray."</td></tr>";
  // echo "<tr><td bgcolor=#EAEAEA><b>Order Date</b></td><td colspan=5>".$exe['ord_date")."</td></tr>";
   echo "<tr><td bgcolor=#EAEAEA><b>Invoice Number</b></td><td colspan=3>".$exe['cmp']."-".$exe['invref']. "-".trim($exe['invno'])."</td></tr>";
   echo "<tr><td bgcolor=#EAEAEA><b>Invoice Date</b></td><td colspan=3>".$exe['invdate']."</td></tr>";
   $rs1 = $link->prepare("select distinct tname,lrno,lrdate from lr_details a, despatch b where a.invref=:invref and a.invno=:invno and a.invno::text = b.invno::text and a.invref = b.invref");
   $rs1->bindParam(':invref', $invref, PDO::PARAM_STR);
   $rs1->bindParam(':invno', $invno, PDO::PARAM_STR);
   $rs1->execute();
   if($rs1->rowcount()>0)
   {
    $exe1 = $rs1->fetch(PDO::FETCH_ASSOC);
    $tname=$exe1['tname'];
    $lrno=$exe1['lrno'];
    $lrdt=$exe1['lrdate'];
   }
   else
   {
    $tname='';
    $lrno='';
    $lrdt='';
   }
   //$rs1=pg_query($link,"select cases,bundles,boxes,cartoons,spcases,weight,invamt,uom,deladr,destination from dpboxes where trim(invpre)='$invref' and invno='$invno' and cuno='$cuno'");

   $rs1 = $link->prepare("select cases,bundles,boxes,carton_box,spl_cases,weight,w_unit,dly_code from lr_details where trim(invref)=:invref and invno=:invno");
   $rs1->bindParam(':invref', $invref, PDO::PARAM_STR);
   $rs1->bindParam(':invno', $invno, PDO::PARAM_STR);
   $rs1->execute();
   $exe1 = $rs1->fetch(PDO::FETCH_ASSOC);
   
   $cases=$exe1['cases'];
   $boxes=$exe1['boxes'];
   $bundles=$exe1['bundles'];
   $cartoons=@$exe1['carton_box"'];
   $spcases=$exe1['spl_cases'];
   $weight=$exe1['weight'];
   $w_unit=$exe1['w_unit'];
   $dly_code=$exe1['dly_code'];

//   $rs1=pg_query($link,"select cdel from dpboxes_deladdr where trim(invpre)='$invref' and invno='$invno' and cuno='$cuno'");
  // $deladr=$exe1['cdel");
/*
   $rs1=pg_query($con,"select address1,address2,address3,address4,address5,address6 from cust_delivery_address where cuno='$cuno' and delivery_code='$deladr'");
   $add1=$exe1['address1");
   $add2=$exe1['address2");
   $add3=$exe1['address3");
   $add4=$exe1['address4");
   $add5=$exe1['address5");
*/

   $rs1 = $con->prepare("select custaddr from customer_address where adr_code=:dly_code and cuno=:cuno");
   $rs1->bindParam(':dly_code', $dly_code, PDO::PARAM_STR);
   $rs1->bindParam(':cuno', $cuno, PDO::PARAM_STR);
   $rs1->execute();
   $exe1 = $rs1->fetch(PDO::FETCH_ASSOC);
   $custaddr = @$exe1['custaddr'];

  
   echo '<tr><td bgcolor="#EAEAEA"><b>Transporter</b></td><td colspan="3">'.$tname.'</td></tr>';
   echo '<tr><td bgcolor="#EAEAEA"><b>LR Number</b></td><td colspan="3">'.$lrno.'</td></tr>';
   echo '<tr><td bgcolor="#EAEAEA"><b>LR Date</b></td><td colspan="3">'.$lrdt.'</td></tr>';
   echo '<tr><td bgcolor="#EAEAEA" valign="top"><b>PACKING DETAILS</b></td><td colspan="3">CASES:-<b>'.$cases.'</b>&nbsp;&nbsp;BOXES:-<b>'.$boxes.'</b>&nbsp;&nbsp;BUNDLES:-<b>'.$bundles.'</b><br />CARTOONS:-<b>'.$cartoons.'</b>&nbsp;&nbsp;SPECIAL CASES:-<b>'.$spcases.'</b></td></tr>';
   echo '<tr><td bgcolor="#EAEAEA"><b>WEIGHT ACTUAL/CFT</b></td><td colspan="3">'.$weight.'&nbsp;'.$uom.'</td></tr>';
   //echo '<tr><td bgcolor="#EAEAEA"><b>INVOICE AMOUNT</b></td><td colspan="6">'.$invamt.'</td></tr>';
   echo '<tr><td bgcolor="#EAEAEA" valign="top"><b>DELIVERY CODE</b></td><td colspan="3">'.$dly_code.'</td></tr>';
   echo '<tr><td bgcolor="#EAEAEA" valign="top"><b>DELIVERY ADDRESS</b></td><td colspan="3">'.$custaddr.'</td></tr>';
  // echo '<tr><td bgcolor="#EAEAEA"><b>DESTINATION</b></td><td colspan="6">'.$destination.'</td></tr>';

   echo "<tr bgcolor=#C0C0C0></td><td><b>AO NO/POS NO</b></td><td><b>Item Description</b></td></td><td><b>Qty</b></td><td><b>Basic Value</b></td></tr>";

  while($row = $rs->fetch(PDO::FETCH_OBJ))
  {
    $gPrice=$gPrice+$row->price;
    echo "<tr align=right><td align=center>".$row->ordno."/".$row->posno."</td><td align=left>".$row->item_desc."</td><td>".$row->qty."</td><td>".number_format($row->price,2)."</td></tr>";
  }
  echo "<tr align=\"right\" bgcolor=\"#EAEAEA\"><td colspan=\"3\"><b>TOTAL BASIC VALUE</b></td><td><b>".number_format($gPrice,2)."</b></td></tr>";
 if($diff>=0)
 {
   $rs = $link->prepare("select edamt,taxamt from despatch where trim(invref)=:invref and invno=:invno and cuno=:cuno");
   $rs->bindParam(':invref', $invref, PDO::PARAM_STR);
   $rs->bindParam(':invno', $invno, PDO::PARAM_STR);
   $rs->bindParam(':cuno', $cuno, PDO::PARAM_STR);
   $rs->execute();
   $exe = $rs->fetch(PDO::FETCH_ASSOC);
  $edamt = $exe['edamt'];
  $taxamt = $exe['taxamt'];
  $InvoiceValue=$gPrice+$edamt+$taxamt;
  echo "<tr align=\"right\" bgcolor=\"#EAEAEA\"><td colspan=\"3\"><b>TOTAL EXCISE DUTY</b></td><td><b>".number_format($edamt,2)."</b></td></tr>";
  echo "<tr align=\"right\" bgcolor=\"#EAEAEA\"><td colspan=\"3\"><b>TOTAL SALES TAX</b></td><td><b>".number_format($taxamt,2)."</b></td></tr>";
  echo "<tr align=\"right\" bgcolor=\"#EAEAEA\"><td colspan=\"3\"><b>TOTAL INVOICE VALUE</b></td><td><b>".number_format($InvoiceValue,2)."</b></td></tr>";
}
else
{
   $rs = $link->prepare("select gstamt from despatch where trim(invref)=:invref and invno=:invno and cuno=:cuno");
   $rs->bindParam(':invref', $invref, PDO::PARAM_STR);
   $rs->bindParam(':invno', $invno, PDO::PARAM_STR);
   $rs->bindParam(':cuno', $cuno, PDO::PARAM_STR);
   $rs->execute();
   $exe = $rs->fetch(PDO::FETCH_ASSOC);

  $gstamt = $exe['gstamt'];
  $InvoiceValue=$gPrice+$gstamt;
  echo "<tr align=\"right\" bgcolor=\"#EAEAEA\"><td colspan=\"3\"><b>TOTAL GST</b></td><td><b>".number_format($gstamt,2)."</b></td></tr>";
  echo "<tr align=\"right\" bgcolor=\"#EAEAEA\"><td colspan=\"3\"><b>TOTAL INVOICE VALUE</b></td><td><b>".number_format($InvoiceValue,2)."</b></td></tr>";
}


  //echo "<tr align=\"right\" bgcolor=\"#EAEAEA\"><td colspan=\"6\"><b>TOTAL</b></td><td><b>$gTotal</b></td></tr>";
  echo "</table>";
 }

 $rs = $link->prepare("select distinct dpst,ordno from despatch where invref=:invref and invno=:invno and cuno=:cuno");
 $rs->bindParam(':invref', $invref, PDO::PARAM_STR);
 $rs->bindParam(':invno', $invno, PDO::PARAM_STR);
 $rs->bindParam(':cuno', $cuno, PDO::PARAM_STR);
 $rs->execute();
 $exe = $rs->fetch(PDO::FETCH_ASSOC);

 $dpst=$exe['dpst'];
 $aono=$exe['ordno'];

 if($dpst==90091  || $dpst==90092 || $dpst==90073)
 {
    echo '<p><form method="post" action="railway_rites_upload.php" enctype="multipart/form-data">';
    echo '<table border="1"  cellpadding="6" align="center" width="60%"  bordercolor="#000000">';
    echo '<tr><th colspan="2">RAILWAY RITES INSPECTION</th></tr>';
    echo '<tr><td><b>Filename</b></td><td><input type="file" name="ufile[]">
                <input type="hidden" name="invref" value='.$invref.'>
                <input type="hidden" name="invno" value='.$invno.'>
                <input type="hidden" name="aono" value='.$aono.'></td></tr>';
                echo '<tr><td colspan="2" align="center"><input type="submit" value="SUBMIT">
                          <input type="hidden" name="cuno" value="'.$cuno.'">
                          <input type="hidden" name="cmp" value="'.$cmp.'"></td></tr>';
                echo '</table></form></p>'; 
  $rs = $con->prepare("select fname from railways_rites_attachment where aono=:aono");
  $rs->bindParam(':aono', $aono, PDO::PARAM_STR);
  $rs->execute();
  while($exe = $rs->fetch(PDO::FETCH_ASSOC))
  {
     $fname = $exe['fname'];
     echo "<p align=\"center\"><a href=attachment.php?fname=".urlencode($fname)." target=\"_blank\">$fname</a></p>";
  }
 }

/*
 if($cmp==490)
 {
    echo '<p><form method="post" action="road_permit_upload.php" enctype="multipart/form-data">';
    echo '<table border="1"  cellpadding="6" align="center" width="60%"  bordercolor="#000000">';
    echo '<tr><th colspan="2">UPLOAD ROAD PERMIT DETAILS</th></tr>';
    echo '<tr><td><b>Filename</b></td><td><input type="file" name="ufile[]">
                <input type="hidden" name="invref" value='.$invref.'>
                <input type="hidden" name="invno" value='.$invno.'>
                <input type="hidden" name="aono" value='.$aono.'></td></tr>';
                echo '<tr><td colspan="2" align="center"><input type="submit" value="SUBMIT">
                          <input type="hidden" name="cuno" value="'.$cuno.'">
                          <input type="hidden" name="cmp" value="'.$cmp.'"></td></tr>';
                echo '</table></form></p>'; 

  $rs=pg_query($con,"select fname from road_permit_attachment where aono='$aono' and cmp=$cmp");
  if(pg_num_rows($rs)>0)
  {
    echo "<p align=\"center\">Road Permit:-<a href=attachment.php?fname=".urlencode($fname)." target=\"_blank\">$fname</a></p>";
  }
}
*/

 $qry = $link->prepare("select tpl,tpl_desc,fabno from ln_desp_details where trans_type=:invref and inv_no=:invno and company in ('401','440','450') order by fabno");
 $qry->bindParam(':invref', $invref, PDO::PARAM_STR);
 $qry->bindParam(':invno', $invno, PDO::PARAM_STR);
 $qry->execute();
 if($qry->rowcount() > 0)
 {
    echo '<p><table border="1"  cellpadding="6" align="center" width="60%"  bordercolor="#000000">';
    echo '<tr><td bgcolor="#EAEAEA" align="center" colspan="3"><b>FABNO DETAILS</b></td></tr>';
    echo '<tr><td bgcolor="#EAEAEA"><b>TPLCODE</b></td><td bgcolor="#EAEAEA"><b>TPL DESC</b></td><td bgcolor="#EAEAEA"><b>FABNO</b></td></tr>';
    while($exe = $rs->fetch(PDO::FETCH_ASSOC))
    {
      $tpl = $exe['tpl'];
      $tpldesc = $exe['tpldesc'];
      $fabno = $exe['fabno'];
      echo "<tr><td>$tpl</td><td>$tpldesc</td><td>$fabno</td></tr>";
    }
    echo '</table></p>';
 }
?>

<p><div align="center"><a href="javascript:window.close()"><font face=verdana size=2>Close</font></a></div></p></body></html>

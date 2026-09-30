<?php
session_start();
ob_start("ob_gzhandler");
require('../../session_expiry_page1.php');
$uname = @$_SESSION['usr_name'];
$display = @$_SESSION['display_name'];
$cuno = @$_SESSION['cuno'];
$uDesig = @$_SESSION['usr_desig'];
//echo date('d-m-Y');
if (isset($_GET['cuno'])) {
  $cuno = pg_escape_string($_GET['cuno']);
  $_SESSION['SESS_cuno'] = $cuno;
}
if (isset($_GET['refno'])) {
  $refno = pg_escape_string(pg_escape_string($_GET['refno']));
  $_SESSION['SESS_refno'] = $refno;
}
if (isset($_GET['odate'])) {
  $odate = trim(pg_escape_string($_GET['odate']));
}

if ($uDesig == 0) {
  $fname = 'vieworders.php';
} else {
  $fname = 'view_request.php';
}
require('../../include/obconn.php');
require('../../include/dpconn.php');
require('../../checkelgi.php');
//$query="select * from dealer_spares_dpst where 90092=any(dpst) and cuno='$cuno'";a
/*
$query="select * from railway_dealer where cuno='$cuno'";
$result=pg_query($con,$query);
if(pg_num_rows($result)<=0)
{
  echo "<p align=center>Permission Denied!!!</p>";
  echo "<p align=center><a href=../index.php>Click Here To Index Page</a></p>";
  exit();
}
pg_query($con,"delete from railway_spares_orders where cuno='$cuno' and confirm='N'");
*/
?>
<html>

<head>
  <title>Railway Spares Order Booking</title>
  <!-- <link rel=stylesheet type=text/css href=/elgi/dpstyle.css> -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/main.css">
  <style>
    :root {
      --railway-ink: #172b3a;
      --railway-blue: #176b87;
      --railway-teal: #2d9b91;
      --railway-line: #d7e3e6;
    }

    body {
      background: linear-gradient(135deg, #eaf2f4 0%, #f8fbfb 52%, #e6f1ef 100%);
      color: var(--railway-ink);
    }

    .page-shell {
      max-width: 1120px;
      margin: 0 auto;
      padding: 1rem 1rem 3rem;
    }

    .detail-card,
    .order-table-card {
      overflow: hidden;
      margin: 1rem auto;
      border: 1px solid rgba(23, 107, 135, .15);
      border-radius: 12px;
      background: rgba(255, 255, 255, .96);
      box-shadow: 0 12px 28px rgba(23, 43, 58, .08);
    }

    .detail-card-header {
      padding: 1rem 1.25rem;
      background: linear-gradient(115deg, var(--railway-ink), var(--railway-blue));
      color: #fff;
    }

    .detail-card-header h1,
    .detail-card-header h2 {
      margin: 0;
       font-size: clamp(0.9rem, 2vw, 0.3rem);
      font-weight: 700;
    }

    .btn-railway {
      background: #e01725;
      border: 0;
      color: #fff;
      font-weight: 700;
    }

    .btn-railway:hover,
    .btn-railway:focus {
      background: #237f78;
      color: #fff;
    }

    .detail-table,
    .items-table,
    .status-table,
    .approval-table {
      width: 100%;
      margin: 0;
      border-collapse: collapse;
      font-size: .82rem;
    }

    .detail-table td:first-child,
    .status-table td:first-child,
    .approval-table td:first-child {
      width: 24%;
      background: #f0f8f7;
      color: var(--railway-ink);
      font-weight: 700;
      text-transform: uppercase;
    }

    .detail-table td,
    .items-table th,
    .items-table td,
    .status-table td,
    .approval-table td {
      padding: 12px 16px;
      border: 1px solid var(--railway-line);
      color: #243746;
      vertical-align: middle;
    }

    .items-table th {
      background: var(--railway-ink);
      color: #fff;
      font-size: 11px;
      text-transform: uppercase;
      white-space: nowrap;
    }

    .detail-table a,
    .items-table a {
      color: var(--railway-blue);
      font-weight: 700;
    }

    .detail-table a:hover,
    .items-table a:hover {
      color: var(--railway-teal);
    }

    .detail-table input[type="file"] {
      font-size: 12px;
    }

    .status-table,
    .approval-table {
      margin: 24px auto;
    }

    .status-card {
      overflow: hidden;
      margin: 1rem auto;
      border: 1px solid rgba(23, 107, 135, .15);
      border-radius: 12px;
      background: rgba(255, 255, 255, .96);
      box-shadow: 0 12px 28px rgba(23, 43, 58, .08);
    }

    .status-card-header {
      padding: 1rem 1.25rem;
      background: linear-gradient(115deg, var(--railway-ink), var(--railway-blue));
      color: #fff;
      font-size: clamp(0.9rem, 2vw, 0.3rem);
      font-weight: 700;
    }

    .status-card-body {
      padding: 0;
    }

    .status-table {
      margin: 0;
    }

    .status-table td:last-child {
      white-space: pre-wrap;
      overflow-wrap: anywhere;
    }

    .status-value {
      display: inline-block;
      padding: 5px 10px;
      border-radius: 999px;
      background: #e2f3ef;
      color: #237f78;
      font-size: 11px;
      font-weight: 700;
    }

    .approval-table {
      background: #fff;
      border: 1px solid var(--railway-line);
      border-radius: 12px;
      overflow: hidden;
    }

    .approval-table caption {
      background: var(--railway-ink);
      caption-side: top;
      color: #fff;
      font-weight: 700;
      padding: .85rem 1rem;
      text-align: left;
    }

    .items-table tbody tr:hover,
    .detail-table tr:hover,
    .status-table tr:hover {
      background: #f7fbfb;
    }

    @media (max-width: 640px) {
      .page-shell {
        padding: .5rem .65rem 2rem;
      }

      .detail-table td,
      .items-table td,
      .status-table td,
      .approval-table td {
        padding: .7rem .65rem;
      }
    }
  </style>
  <script language="javascript" src="validate.js"></script>
  <script language="Javascript">
    function check_app() {
      if (document.f1.status.value == 0) {
        alert("Select Status");
        document.f1.status.focus();
        return false;
      }

      if (document.f1.status.value == "A") {
        //check comments
        if (document.f1.tod_app.value == 0) {
          alert("Select TOD Applicable");
          document.f1.tod_app.focus();
          return false;
        }
      }


      if (document.f1.status.value == "R") {
        //check comments
        if (document.f1.rem.value.length == 0) {
          alert("Enter remarks");
          document.f1.rem.focus();
          return false;
        }
      }

      if (confirm("Do you want to approve?")) {
        document.f1.method = "post";
        document.f1.action = "approve_reject.php";
        document.f1.submit();
      }


    }
  </script>
</head>

<body>
  <main class="page-shell">
  <?php include('header.php'); ?>
  <?
  $rs = pg_query($link, "select emp_code from userpass where usr_name='$uname'");
  $ecode = pg_fetch_result($rs, 0, 0);

  $rs = pg_query($con, "select dpst,ordertype,discount,aonumber,subcat,file1,file2,approved,remarks,seqid,pricetype,emp_code,first_apstatus,pono,pfcharges,first_app_remarks from railway_spares_orders where cuno='$cuno' and refno='$refno' and order_date='$odate' limit 1");
  $discount = pg_fetch_result($rs, 0, "discount");
  $aonumber = pg_fetch_result($rs, 0, "aonumber");
  $subcat = pg_fetch_result($rs, 0, "subcat");
  $file1 = pg_fetch_result($rs, 0, "file1");
  $file2 = pg_fetch_result($rs, 0, "file2");
  $approved = pg_fetch_result($rs, 0, "approved");
  $remarks = pg_fetch_result($rs, 0, "remarks");
  $pricetype = pg_fetch_result($rs, 0, "pricetype");
  $ecode1 = pg_fetch_result($rs, 0, "emp_code");
  $first_apstatus = pg_fetch_result($rs, 0, "first_apstatus");
  $first_app_remarks = pg_fetch_result($rs, 0, "first_app_remarks");
  $pono = pg_fetch_result($rs, 0, "pono");
  $pfcharges = pg_fetch_result($rs, 0, "pfcharges");
  $seqid = pg_fetch_result($rs, 0, "seqid");
  $dpst = pg_fetch_result($rs, 0, "dpst");
  $ordertype = pg_fetch_result($rs, 0, "ordertype");

  //fetch order desc
  $otqy = pg_query($con, "select otype,catdesc from railway_order_category where valid='Y' and otype = '$ordertype'");
  $otrs = pg_fetch_assoc($otqy);
  $order_type = $otrs['catdesc'];
  if ($ecode1 > '0') {
    $rs = pg_query($link, "select display_name from userpass where emp_code='$ecode1'");
    $display_name = pg_fetch_result($rs, 0, 0);
  }
  if (strlen($file1) <= 25) {
    $file1 = '';
  }

  if (strlen($file2) <= 25) {
    $file2 = '';
  }


  $_SESSION['SESS_seqid'] = $seqid;
  $rs = pg_query($con, "select subdesc from railway_order_subcategory where subcat='$subcat'");
  $subdesc = pg_fetch_result($rs, 0, 0);
  echo '<input type="hidden" id="hiddenCustomer" value="' . htmlspecialchars($cuno, ENT_QUOTES, 'UTF-8') . '"/>';
  echo '<section class="detail-card">';
  echo '<div class="detail-card-header d-flex align-items-center justify-content-between gap-3"><h1>ORDER DETAILS</h1><a href="' . htmlspecialchars($fname, ENT_QUOTES, 'UTF-8') . '" class="btn btn-railway">View Orders</a></div>';
  echo '<div class="table-responsive"><table class="detail-table">';
  echo '<tr><td><b>PO NO</b></td><td colspan="4">' . htmlspecialchars($pono, ENT_QUOTES, 'UTF-8') . '</td></tr>';
  echo '<tr><td><b>ORDERED DATE</b></td><td colspan="4">' . htmlspecialchars($odate, ENT_QUOTES, 'UTF-8') . '</td></tr>';
  echo '<tr><td><b>ORDER CATEGORY</b></td><td colspan="4">' . htmlspecialchars($subdesc, ENT_QUOTES, 'UTF-8') . '</td></tr>';
  echo '<tr><td><b>DPST</b></td><td colspan="4">' . htmlspecialchars($dpst, ENT_QUOTES, 'UTF-8') . '</td></tr>';
  echo '<tr><td><b>ORDER TYPE</b></td><td colspan="4">' . htmlspecialchars($order_type, ENT_QUOTES, 'UTF-8') . '</td></tr>';
  echo '<tr><td><b>AO NUMBER</b></td><td colspan="4"><input type="hidden" id="hiddenOrderNo" value="' . htmlspecialchars($aonumber, ENT_QUOTES, 'UTF-8') . '"/>' . htmlspecialchars($aonumber, ENT_QUOTES, 'UTF-8') . '</td></tr>';
  echo '<tr><td><b>PF CHARGES</b></td><td colspan="4">' . htmlspecialchars($pfcharges, ENT_QUOTES, 'UTF-8') . '</td></tr>';
  if ($pricetype == 'RLY') {
    echo '<tr><td width="18%"><b>PO COPY</b></td><td colspan="4" width="82%"><a href=uploads/' . str_replace(" ", "%20", $file1) . ' target="_new">' . $file1 . '</td></tr>';
    echo '<tr><td width="18%"><b>CHECK LIST</b></td><td colspan="4" width="82%"><a href=uploads/' . str_replace(" ", "%20", $file2) . ' target="_new">' . $file2 . '</td></tr>';
  }
  if ($pricetype == 'DMW') {
    echo '<tr><td width="18%"><b>PO COPY</b></td><td colspan="4" width="82%"><a href=/elgi/railways/dmw/uploads/' . str_replace(" ", "%20", $file1) . ' target="_new">' . $file1 . '</td></tr>';
    echo '<tr><td width="18%"><b>CHECK LIST</b></td><td colspan="4" width="82%"><a href=/elgi/railways/dmw/uploads/' . str_replace(" ", "%20", $file2) . ' target="_new">' . $file2 . '</td></tr>';
  }
  if ($aonumber) {
      $rsAo = pg_query($con, "select attachment from tbl_railway_ao_attachment where orderno='$aonumber'");
      if (pg_num_rows($rsAo) > 0) {
        $aoFile = pg_fetch_result($rsAo, 0, "attachment");
        echo '<tr><td width="18%"><b>AO COPY</b></td><td colspan="11"><a href=/elgi/railways/new/uploadsAo/' . str_replace(" ", "%20", $aoFile) . ' target="_new">' . $aoFile . '</td></tr>';
      }

        echo '<tr><td><b>AO ATTACHMENT</b></td><td colspan="4"><input type="file" id="aoFile"/><button class="btn btn-railway btn-sm" onclick="uploadAo(event)">Upload</button></td></tr>';
  }
      echo '</table></div></section>';
      echo '<section class="order-table-card"><div class="detail-card-header"><h2>ORDER ITEMS</h2></div><div class="table-responsive"><table class="items-table"><thead><tr><th>START DT</th><th>END DT</th><th>DELIVERY ADDRESS</th><th>ITEM CODE</th><th>ITEM DESCRIPTION</th><th>TOD</th><th>QTY</th><th>PRICE/UNIT</th><th>DISCOUNT</th><th>TOTAL</th></tr></thead><tbody>';
  $rs = pg_query($con, "select startdt,delivery_date,delivery_code,itemcode,quantity,clp,discount,tod_applicable from railway_spares_orders where cuno='$cuno' and confirm='Y' and refno='$refno' and order_date='$odate'");

  $gTotal = 0;
  while (list($startdt, $ddate, $dcode, $icode, $qty, $clp, $discount, $tod_applicable) = pg_fetch_row($rs)) {
    $rs1 = pg_query($con, "select custaddr from customer_address where cuno='$cuno' and adr_code='$dcode'");
    $add1 = pg_fetch_result($rs1, 0, 0);
    $rs1 = pg_query($con, "select item_desc from gsc_item_master where item_code='$icode'");
    $idesc = pg_fetch_result($rs1, 0, 0);
    $total = (($qty * $clp) * ((100 - $discount) / 100));
    $gTotal = $gTotal + $total;
    echo '<tr><td>' . $startdt . '</td><td>' . $ddate . '</td><td>' . $dcode . '-[' . $add1 . ']</td><td>' . $icode . '</td><td>' . $idesc . '</td><td>' . $tod_applicable . '</td><td align="right">' . $qty . '</td>
  <td align="right">' . number_format($clp, 2) . '</td><td align="right">' . $discount . '</td><td align="right">' . number_format($total, 2) . '</td></tr>';
  }
  echo "<tr align=\"right\"><td colspan=\"9\" align=\"right\"><b>TOTAL ORDER VALUE</b></td><td>" . number_format($gTotal, 2) . "</td></tr>";
  echo '</tbody></table></div></section>';
  //echo $ecode.'-'.$ecode1.'-'.$first_apstatus;
  if ($first_apstatus == 'N' && $ecode == $ecode1) {
    echo '<p><form name="f1" action="approve_reject.php" method="post" >
    <input type="hidden" name="pdpst" value="'.$dpst.'"/>
    <table class="approval-table">';
    echo '<caption>APPROVAL FORM</caption>';
    echo '<tr><td><b>STATUS</b></td><td>&nbsp;<select name="status"><option value="0">SELECT ONE</option>';
    echo '<option value="A">APPROVED</option><option value="R">REJECTED</option></select></td></tr>';
    echo '<tr><td><b>TOD APPLICABLE</b></td><td>&nbsp;<select name="tod_app"><option value="0">SELECT ONE</option>';
    echo '<option value="Yes">Yes</option><option value="No">No</option></select></td></tr>';
    echo '<tr valign="top"><td><b>REMARKS</b></td><td>&nbsp;<textarea name="rem" cols="50" rows="4"></textarea></td></tr>';
    echo '<tr><td colspan="2" align="center"><input type="button" value="SUBMIT" onclick="check_app();"></td></tr></table></form>';
  } else if ($first_apstatus == 'A' && $approved === 'N'  && $uDesig == 1) {
    echo '<p><form name="f1" action="approve_reject.php" method="post"><input type="hidden" name="pdpst" value="'.$dpst.'"/><table class="approval-table">';
    echo '<caption>APPROVAL FORM</caption>';
    echo '<tr><td><b>FIRST APPROVER</b></td><td>' . $display_name . '</td></tr>';
    echo '<tr><td><b>FIRST APPROVER REMARKS</b></td><td>' . $first_app_remarks . '</td></tr>';
    echo '<tr><td><b>STATUS</b></td><td>&nbsp;<select name="status"><option value="0">SELECT ONE</option>';
    echo '<option value="A">APPROVED</option><option value="R">REJECTED</option></select></td></tr>';
    echo '<tr valign="top"><td><b>REMARKS</b></td><td>&nbsp;<textarea name="rem" cols="50" rows="4"></textarea></td></tr>';
    echo '<tr><td colspan="2" align="center"><input type="submit" value="SUBMIT"></td></tr></table></form>';
  } else {
    if ($approved == 'N') {
      $statusDesc = 'WAITING FOR APPROVAL';
    }
    if ($approved == 'A') {
      $statusDesc = 'APPROVED';
    }
    if ($approved == 'R') {
      $statusDesc = 'REJECTED';
    }
    echo '<section class="status-card">';
    echo '<div class="status-card-header">ORDER STATUS</div>';
    echo '<div class="status-card-body"><table class="status-table">';
    echo '<tr><td><b>STATUS</b></td><td><span class="status-value">' . htmlspecialchars($statusDesc, ENT_QUOTES, 'UTF-8') . '</span></td></tr>';
    echo '<tr valign="top"><td><b>FIRST APPROVER REMARKS</b></td><td>' . nl2br(htmlspecialchars($first_app_remarks ?? '', ENT_QUOTES, 'UTF-8')) . '</td></tr>';
    echo '<tr valign="top"><td><b>REMARKS</b></td><td>' . nl2br(htmlspecialchars($remarks ?? '', ENT_QUOTES, 'UTF-8')) . '</td></tr></table></div></section>';
  }
  ?>
</main>
</body>

</html>
<script>
  function uploadAo(e) {
    e.preventDefault();
    e.stopPropagation();
    var file = document.getElementById('aoFile').files[0];
    if (!file) {
      alert("Please select a file");
      return;
    }
    let formData = new FormData();
    formData.append("file", file);
    formData.append('orderNo', document.getElementById('hiddenOrderNo').value);
    formData.append('customerNo', document.getElementById('hiddenCustomer').value);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "uploadAo.php", true);

    xhr.onload = function() {
      if (xhr.status === 200) {
        alert(xhr.responseText);
        window.location.reload();
      } else {
        alert("Upload failed");
      }
    };
    xhr.send(formData);
  }
</script>
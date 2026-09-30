<?php
session_start();
require('/var/www/html/elgi/session_expiry_page1.php');
require('/var/www/html/include/obconn.php');
require('/var/www/html/elgi/checkelgi.php');
$uname = $_SESSION['usr_name'];
$refno = $_SESSION['SESS_refno'];
$cuno = $_SESSION['SESS_cuno'];
$dpst = $_POST['pdpst'] ?? '90092';

$status = pg_escape_string($_POST['status']);
$remarks = pg_escape_string(pg_escape_string($_POST['rem']));
$tod_app = pg_escape_string(pg_escape_string($_POST['tod_app']));

$rs = pg_query($con, "select level2 from railway_customer_approval_matrix where cust_code='$cuno' and dpst='$dpst'");
$ecode = pg_fetch_result($rs, 0, 0);


$chk = pg_query($con, "SELECT first_apstatus, level2_status FROM railway_spares_orders WHERE refno='$refno' AND cuno='$cuno'");

$row = pg_fetch_assoc($chk);

if (strlen($status) > 0) {

  // Level 1 Approval
  if ($row['first_apstatus'] != 'A') {

    $qry = "UPDATE railway_spares_orders SET first_apstatus='$status', first_stdate=current_date,tod_applicable='$tod_app', first_app_remarks='$remarks', level2_approver=$ecode WHERE refno='$refno'AND cuno='$cuno' AND first_apstatus='N'";
  }
  // Level 2 Approval
  else if (
    $row['first_apstatus'] == 'A' && $row['level2_status'] == 'N') {
    echo $qry = "UPDATE railway_spares_orders SET level2_status='$status',approved='$status', remarks='$remarks', approver='$uname',approved_date=current_timestamp WHERE refno='$refno' AND cuno='$cuno' AND first_apstatus='A' AND level2_status='N'";
  }
}
pg_query($con, $qry);

// Mail Code Started
// email-related code
if (strlen($status) > 0 && ($status === 'A' || $status === 'R') && is_array($row)) {
  $safeRefno = pg_escape_string($refno);
  $safeCuno = pg_escape_string($cuno);
  $savedRs = pg_query($con, "SELECT first_apstatus, level2_status FROM railway_spares_orders WHERE refno='$safeRefno' AND cuno='$safeCuno' LIMIT 1");
  $saved = $savedRs ? pg_fetch_assoc($savedRs) : false;
  $isLevel1 = ($row['first_apstatus'] != 'A');
  $completed = false;

  if ($saved) {
    if ($isLevel1 && $saved['first_apstatus'] === $status) {
      $completed = true;
    } else if (!$isLevel1 && $saved['level2_status'] === $status) {
      $completed = true;
    }
  }

  if ($completed) {
    $aoRs = pg_query($con, "SELECT aonumber, pono, order_date, dpst, ordertype, subcat, pfcharges, discount, tod_applicable, itemcode, quantity, clp, startdt, delivery_date FROM railway_spares_orders WHERE refno='$safeRefno' AND cuno='$safeCuno'");
    $aoNumber = '';
    $poNumber = '';
    $orderDate = '';
    $dpstCode = '';
    $orderType = '';
    $orderTypeDesc = '';
    $subCategory = '';
    $pfCharges = '';
    $itemLines = '';

    if ($aoRs && pg_num_rows($aoRs) > 0) {
      $firstAoRow = true;
      while ($aoRow = pg_fetch_assoc($aoRs)) {
        if ($firstAoRow) {
          $aoNumber = $aoRow['aonumber'];
          $poNumber = $aoRow['pono'];
          $orderDate = $aoRow['order_date'];
          $dpstCode = $aoRow['dpst'];
          $orderType = $aoRow['ordertype'];
          $pfCharges = $aoRow['pfcharges'];

          $safeOrderType = pg_escape_string($orderType);
          $typeRs = pg_query($con, "SELECT catdesc FROM railway_order_category WHERE valid='Y' AND otype='$safeOrderType'");
          if ($typeRs && pg_num_rows($typeRs) > 0) {
            $orderTypeDesc = pg_fetch_result($typeRs, 0, 0);
          }

          $safeSubcat = pg_escape_string($aoRow['subcat']);
          $subRs = pg_query($con, "SELECT subdesc FROM railway_order_subcategory WHERE subcat='$safeSubcat'");
          if ($subRs && pg_num_rows($subRs) > 0) {
            $subCategory = pg_fetch_result($subRs, 0, 0);
          }

          $firstAoRow = false;
        }

        $itemLines .= "Item Code: " . $aoRow['itemcode']
          . " | Qty: " . $aoRow['quantity']
          . " | Price/Unit: " . $aoRow['clp']
          . " | Discount: " . $aoRow['discount']
          . " | TOD: " . $aoRow['tod_applicable']
          . " | Start Date: " . $aoRow['startdt']
          . " | Delivery Date: " . $aoRow['delivery_date']
          . "\r\n";
      }
    }

    $aoNumberSafe = str_replace(array("\r", "\n"), '', (string) $aoNumber);
    $approvalLevel = $isLevel1 ? 'Level 1' : 'Level 2';
    $to = 'coserve7@elgi.com';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "From: ELGI Railway Spares <noreply@elgi.com>\r\n";

    if ($status === 'A') {
      $subject = 'Railway Spares Order Approved - AO ' . $aoNumberSafe;
      $message = "A railway spares order approval is completed.\r\n\r\n";
      $message .= "AO Details\r\n";
      $message .= "----------\r\n";
      $message .= "AO Number: " . $aoNumber . "\r\n";
      $message .= "Customer Code: " . $cuno . "\r\n";
      $message .= "Reference No: " . $refno . "\r\n";
      $message .= "PO Number: " . $poNumber . "\r\n";
      $message .= "Order Date: " . $orderDate . "\r\n";
      $message .= "DPST: " . $dpstCode . "\r\n";
      $message .= "Order Type: " . $orderTypeDesc . "\r\n";
      $message .= "Order Category: " . $subCategory . "\r\n";
      $message .= "PF Charges: " . $pfCharges . "\r\n";
      $message .= "Approval Level: " . $approvalLevel . "\r\n";
      $message .= "Approved By: " . $uname . "\r\n\r\n";
      $message .= "Order Items\r\n";
      $message .= "-----------\r\n";
      $message .= $itemLines;
    } else {
      $rejectionReason = isset($_POST['rem']) ? $_POST['rem'] : '';
      $subject = 'Railway Spares Order Rejected - AO ' . $aoNumberSafe;
      $message = "A railway spares order rejection is completed.\r\n\r\n";
      $message .= "AO Number: " . $aoNumber . "\r\n";
      $message .= "Customer Code: " . $cuno . "\r\n";
      $message .= "Reference No: " . $refno . "\r\n";
      $message .= "Approval Level: " . $approvalLevel . "\r\n";
      $message .= "Rejected By: " . $uname . "\r\n\r\n";
      $message .= "Rejection Reason\r\n";
      $message .= "----------------\r\n";
      $message .= $rejectionReason . "\r\n";
    }

    mail($to, $subject, $message, $headers);
  }
}
// Mail Code End

header("Location:view_request.php");
exit();

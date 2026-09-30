<?php
 class head
 {
 function display($disp,$u,$i,$w,$d=0)
 {
   if($d==0)
   { 
    switch($i)
    {
      case 1: 
        $filename='aoprint.php';
        break;   
     case 2:
        $filename='despatchprint.php';
        break; 
     case 3:
        $filename='pendingprint.php';
        break; 
     case 4:
        $filename='arprint.php';
        break; 
     case 5:
        $filename='lrdetailsprint.php';
        break; 
    }
   }
   echo "<p><table border=0 cellpadding=0 cellspacing=0 align=center width=$w>";
   echo "<tr><td colspan=3 align=right><img border=0 src=/images/elgi_logo.jpg height=60></td></tr>";
if(!empty($filename))
{
  echo "<tr><td colspan=2>&nbsp;</td></tr><tr bgcolor=#EAEAEA><td align=left colspan=2 height=1 height=6>";
  echo "<font face=Georgia size=2><b>Welcome&nbsp;&nbsp;</font><font face=Garamond color=#B22222 size=2>".strtoupper($disp)."   , </font><font color=green>[".$u."]</font>  ELGI EQUIPMENTS LTD</td>";
}
else
{
  echo "<tr bgcolor=#EAEAEA><td colspan=3 align=left height=1 colspan=2>";
  echo "<font face=Georgia size=2><b>Welcome&nbsp;&nbsp;</font><font face=Georgia color=#B222222 size=2>".strtoupper($disp)."</b></font><font color=green>&nbsp<b>[".$u."]</b></font></td>";
}
echo "</table></p>"; 
}
}
?>


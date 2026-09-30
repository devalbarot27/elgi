<?php
/** 
    Programmer : K Dhanasekar
    Created on : 14/02/2008
    Description : To display different menu for the ELGI Domestic & International Dealers       
 **/
class menu
{
  function displaymenu($domestic,$curfile,$w)
  {
	   $str1='';
	   if($domestic=='Y')
	   {
	     $fname=array('ao.php','pending.php','despatch.php','lrdetails.php','ar.php');
	     $display=array('Order Acknowledgement','Pending Orders','Despatch Details','LR Details','AR Statement');
	  }
	  if($domestic=='N')
	  {
	     $fname=array('ao.php','pending.php','despatch.php','ar.php');
	     $display=array('Order Acknowledgement','Pending Orders','Despatch Details','AR Statement');
	  }
	  echo "<p><table border=0 cellpadding=0 cellspacing=0 style=border-collapse: collapse align=center width=$w>";
	  echo "<tr bgcolor=#EAEAEA align=center>";
	  //$ct=count(@$fname);
	  $ct=0;
		for($i=0;$i<$ct;$i++)
		{  
		   $str1=@$fname[$i];    
	 
		   if(strcasecmp($str1,$curfile)==0)
		   { 
		     echo "<td>".$display[$i]."</td><td>|</td>";
		   }
		   else
		   {
		    echo "<td><a href=".$fname[$i].">".$display[$i]."</td><td>|</td>";
		   }
		}
		echo "<td><a href=index.php>Home</a></td><td>|</td>";
		echo "<td><a href=../logout.php target=_top>Sign Out</a></td></tr></table></p>";
   }
} 
?>

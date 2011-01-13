A more 'hackish' example for parsing the Yale directory with PHP applications. For better LDAP integration, see ldap-yale.php. 
<br / ><br / >
<?php
  	$user_id = 'TestNetID';
    $url = 'http://directory.yale.edu/phonebook/index.htm?searchString=netid=' . $user_id;
		
    $fp = fopen( $url, ‘r’ );

 		$content = “”;
    while( !feof( $fp ) ) {
    $buffer = trim( fgets( $fp, 4096 ) );
       $content .= $buffer;
    }
	  $temp = substr($content, strpos($content, 'Email Address:'));
		$temp = substr($temp, 14, strpos($temp, '</td')-14);
		echo $temp;
   
?>

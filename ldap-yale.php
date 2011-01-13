<html>
<head>
</head>
<body>
	Simple example for adding LDAP integration to a Yale-centered php application.  <br />

<?php
$conn = ldap_connect("directory.yale.edu") or die("Could not connect to yale directory");  
//#bind to the Yale LDAP server
$r = ldap_bind($conn) or die("Could not bind to yale directory");     

//#this holds the netID to be searched for
$netID = 'TestNetID'

$result = ldap_search($conn,"ou=People,o=yale.edu", "(uid=" + $netID + ")") or die ("Invalid netid");   // add a * wildcard if desired
//#get entry data as array
$info = ldap_get_entries($conn, $result);
//#iterate over array and print data for each result.  1 netid => 1 result.
for ($i=0; $i<$info["count"]; $i++) 
{
	echo "givebname is: ". $info[$i]["givenname"][0] ."<br>";
	echo "sn is: ". $info[$i]["sn"][0] ."<br>";
	echo "first email address is: ". $info[$i]["mail"][0] ."<p>"; 
}
// #if using wild card search for multiple results:
//echo "Number of entries found: " . ldap_count_entries($conn, $result) . "<br />";


ldap_close($conn);
?>
</body>
</html>
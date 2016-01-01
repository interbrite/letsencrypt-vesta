<?php

$file = file_get_contents("/tmp/domains");
$exp = explode(",",$file);
usort($exp, function($a, $b) {
    return strlen($a) - strlen($b);
});
$return = "";
$domlist = "";
for($i = 0;$i<count($exp);$i++)
{
	$dom = str_ireplace("www.","",$exp[$i]);
	$newdom = str_ireplace("www.","",$exp[$i]).",".$exp[$i];
	if($i < count($exp) - 1)
	{
		$domlist .= $dom.",";
		$return .= $newdom.",";
	}else{
		$domlist .= $dom;
		$return .= $newdom;
	}
}

file_put_contents("/tmp/lst_domains",str_ireplace("\n","",$domlist));
file_put_contents("/tmp/all_domains",str_ireplace("\n","",$return));
?>

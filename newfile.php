<?php 
						
function mbstrlen($str)
{
	$len = strlen($str);
	
	if ($len <= 0)
	{
		return 0;
	}
	
	$count  = 0;
	
	for ($i = 0; $i < $len; $i++)
	{
		$count++;
		if (ord($str{$i}) >= 0x80)
		{
			$i += 2;
		}
	}
	
	return $count;
}

echo "output: " . mbstrlen("中国so强大！") . "\n";
?>
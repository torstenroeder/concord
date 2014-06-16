<?php

foreach ($parameters as $parameterKey => $parameter) {
	$current = &$parameters[$parameterKey];
	switch ($parameter['type']) {
		case 'string':
			$current['value'] = getUrlParameter ($parameterKey,NULL);
			if ($current['value']) {
				$current['tokens'] = explode(' ',$current['value']);
			}
		break; // case 'string'
		
		case 'char':
			$current['value'] = getUrlParameter ($parameterKey,NULL);
			if ($current['value']) {
				$current['tokens'] = substr($current['value'],0,1);
			}
		break; // case 'char'
		
		case 'country':
			$current['value'] = getUrlParameter ($parameterKey,NULL);
			if ($current['value']) {
				$current['tokens'] = explode(',',$current['value']);
			}
		break; // case 'country'
		
		case 'date':
			$current['value'] = getUrlParameter ($parameterKey,NULL);
			if ($current['value']) {
				$strippedDate = str_replace('-','',$current['value']);
				switch (strlen($strippedDate)) {
					case 2:
						$current['tokens'] = array(
							'c' => substr($strippedDate,0,2),
							'y' => NULL,
							'm' => NULL,
							'd' => NULL
						);
						break;
					case 4:
						$current['tokens'] = array(
							'c' => substr($strippedDate,0,2),
							'y' => substr($strippedDate,0,4),
							'm' => NULL,
							'd' => NULL
						);
						break;
					case 6:
						$current['tokens'] = array(
							'c' => substr($strippedDate,0,2),
							'y' => substr($strippedDate,0,4),
							'm' => substr ($strippedDate,4,2),
							'd' => NULL
						);
						break;
					case 8:
						$current['tokens'] = array(
							'c' => substr($strippedDate,0,2),
							'y' => substr($strippedDate,0,4),
							'm' => substr ($strippedDate,4,2),
							'd' => substr ($strippedDate,6,2)
						);
						break;
					default:
						// invalid
						$current['tokens'] = array(
							'c' => NULL,
							'y' => NULL,
							'm' => NULL,
							'd' => NULL
						);
						break;
				}
			}
			else {
				$current['tokens'] = array(
					'c' => NULL,
					'y' => NULL,
					'm' => NULL,
					'd' => NULL
				);
			}
		break; // case 'date'
		
		default: break;
	}
}

?>

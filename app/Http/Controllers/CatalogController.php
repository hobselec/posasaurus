<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;

class CatalogController extends Controller
{
    public function search(Request $request)
    {

        $term = $request->term;
        $use_ws_only = $request->use_ws;

        if(!is_numeric($term))
	    {
	
		// enable this to divide up the database, since no categories have been implemented or 
		// ways to group search data
		if(Config::get('pos.use_catalog_filter'))
			($use_ws_only) ? $ws_switch = ' AND barcode > 100000' : $ws_switch = ' AND barcode < 100000';
		 else
			$ws_switch = '';
	
			//	echo "SELECT * FROM catalog WHERE name LIKE '%$skn%'$ws_switch limit $catalog_limit";
		
		$fraction_regex = "/^[1-9][1-9]?\/[1-8][1-9]?.*$/";
		$dimension_regex = "/^[1-9][1-9]?x[1-9][1-9]?.*$/";
		//$cmpd_fraction_regex = "/^

		$query = "SELECT * FROM catalog WHERE name";

		if(preg_match($fraction_regex, $term) || preg_match($dimension_regex, $term))
			$query .= " LIKE '$term%' ";
		else
			$query .= " LIKE '%$term%' ";

		$query .= "$ws_switch ORDER BY name DESC LIMIT " . Config::get('pos.catalog_limit');
// manufacturer_id='$skn'

		$result = DB::select($query);
		
	}
	else
	{

		if(strlen($skn) == 12) // standard UPC length
		{
			// pad skn since catalog import of skn's have left zeros in front
			while(strlen($skn) < $pos->config->skuPadding)
				$skn = '0' . $skn;
		}

		
		$result = $DB::select("SELECT * FROM catalog WHERE barcode='$skn' OR product_id = '$skn'");
// OR manufacturer_id='$skn'

	}

        return response()->json($result);
    }
}

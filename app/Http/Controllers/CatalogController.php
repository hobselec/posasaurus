<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Config;

use App\Models\CatalogItem;

class CatalogController extends Controller
{
    public function search(Request $request)
    {

        $term = $request->term;
        $use_ws_only = $request->use_ws;

		$results = null;

        if(!is_numeric($term))
	    {
	
		// enable this to divide up the database, since no categories have been implemented or 
		// ways to group search data
		if($request->use_ws) //Config::get('pos.use_catalog_filter'))
			$results = CatalogItem::whereNotNull('barcode');

		//	($use_ws_only) ? $ws_switch = ' AND barcode > 100000' : $ws_switch = ' AND barcode < 100000';
		// else
		//	$ws_switch = '';
	
			//	echo "SELECT * FROM catalog WHERE name LIKE '%$skn%'$ws_switch limit $catalog_limit";

		$fraction_regex = "/^[1-9][1-9]?\/[1-8][1-9]?.*$/";
		$dimension_regex = "/^[1-9][1-9]?x[1-9][1-9]?.*$/";
		//$cmpd_fraction_regex = "/^


		//if(!$resul)

		if(preg_match($fraction_regex, $term) || preg_match($dimension_regex, $term))
			$likeQuery = "{$term}%";
			//$results = $results->where('name', 'like', "{$term}%");
			//$query .= " LIKE '$term%' ";
		else
			$likeQuery = "%{$term}%";
			//$results = $results->where('name', 'like', "%{$term}%");
			//$query .= " LIKE '%$term%' ";
		
		if($results)
			$results = $results->where('name', 'like', $likeQuery);
		else
			$results = CatalogItem::where('name', 'like', $likeQuery);
		

		//$query .= "$ws_switch ORDER BY name DESC LIMIT " . Config::get('pos.catalog_limit');


// manufacturer_id='$skn'

		//$result = DB::select($query);
		
	}
	else
	{

		if(strlen($skn) == 12) // standard UPC length
		{
			// pad skn since catalog import of skn's have left zeros in front
			while(strlen($skn) < $pos->config->skuPadding)
				$skn = '0' . $skn;
		}

		
		//$result = $DB::select("SELECT * FROM catalog WHERE barcode='$skn' OR product_id = '$skn'");
		$results = CatalogItem::where('barcode', $skn)->orWhere('product_id', '$skn');

// OR manufacturer_id='$skn'

	}

		$results = $results->orderBy('name', 'desc');

		$limit = Config::get('pos.catalog_limit');
		if($limit != '')
			$results = $results->take($limit);

			return response()->json($results->get());
    }

	public function editItem(Request $request)
	{
		$item = CatalogItem::where('id', $request->item['id'])->first();

		$item->fill($request->item);
		$item->save();

		return response()->json();
	}

	public function addItem(Request $request)
	{
		$item = new CatalogItem();
		$item->name = $request->name;
		$item->price = $request->price;
		$item->qty = $request->qty ?? 0;

		if(!is_numeric($request->barcode))
			$item->barcode = $request->barcode;
		
		$item->save();

		return response()->json($item);
	}
}

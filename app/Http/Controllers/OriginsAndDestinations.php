<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Origin;
use Illuminate\Http\Request;

class OriginsAndDestinations extends Controller
{
    //
    public static function origins()
    {
        $cityOrigin = Origin::all();

        return response()->json(['success' => true, 'city' => $cityOrigin], 200);
    }


    public static function destinations()
    {
        $cityOrigin = Destination::all();

        return response()->json(['success' => true, 'city' => $cityOrigin], 200);
    }
}

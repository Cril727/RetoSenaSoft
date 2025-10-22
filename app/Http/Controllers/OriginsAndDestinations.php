<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Flight;
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

    public static function flightsByOrigin($origin_id)
    {
        $flights = Flight::where('origin_id', $origin_id)->get();

        return response()->json(['success' => true, 'flights' => $flights], 200);
    }

    public static function flightsByDestination($destination_id)
    {
        $flights = Flight::where('destination_id', $destination_id)->get();

        return response()->json(['success' => true, 'flights' => $flights], 200);
    }
}

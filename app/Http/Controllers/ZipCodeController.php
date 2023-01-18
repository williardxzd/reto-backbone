<?php

namespace App\Http\Controllers;

use App\Http\Resources\ZipCodeResource;
use App\Models\ZipCode;
use Illuminate\Support\Facades\Log;

class ZipCodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ZipCodeResource::collection(ZipCode::paginate(25));
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $zipCode
     * @return \Illuminate\Http\Response
     */
    public function show(Zipcode $zipCode)
    {
        return new ZipCodeResource($zipCode);
    }
}

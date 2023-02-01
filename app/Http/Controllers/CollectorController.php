<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Collector;
use Illuminate\Http\Request;

class CollectorController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Collector::where('Kode','like',substr($this->user->Kode,0,2).'%')->get();
        return response()->json(['data'=>$data],200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Collector  $collector
     * @return \Illuminate\Http\Response
     */
    public function show(Collector $collector)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Collector  $collector
     * @return \Illuminate\Http\Response
     */
    public function edit(Collector $collector)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Collector  $collector
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Collector $collector)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Collector  $collector
     * @return \Illuminate\Http\Response
     */
    public function destroy(Collector $collector)
    {
        //
    }
}

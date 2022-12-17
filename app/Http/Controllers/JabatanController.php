<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JabatanController extends Controller
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
        return Jabatan::where('perusahaan',substr($this->user->Kode,0,2))->get();
        // return Jabatan::with('Menus')->find(1);
    }
    // $request->$nama
    public function jabatandata(Request $request){
        return Jabatan::where('jabatan', $request->jabatan)->get();
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
        $jabatan = Jabatan::create([
            'jabatan' => $request->jabatan,
            'perusahaan' => substr($this->user->Kode,0,2)
        ]);
        return response()->json([
            'status' => true,
            'data' => $jabatan,
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Jabatan  $jabatan
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Jabatan::with('Menus')->find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Jabatan  $jabatan
     * @return \Illuminate\Http\Response
     */
    public function edit(Jabatan $jabatan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Jabatan  $jabatan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Jabatan $jabatan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Jabatan  $jabatan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Jabatan $jabatan)
    {
        //
    }
}

<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\MataUangs;
use Illuminate\Http\Request;

class MataUangsController extends Controller
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
        return MataUangs::all();
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
        $this->validate($request, [
            "Kode" => "required",
        ]);
        $mataUang = new MataUangs;
        $mataUang->Kode = $request->Kode;
        $mataUang->Nama = $request->Nama;
        $mataUang->Aktif = $request->Aktif;
        $mataUang->DiUbahOleh = $this->user->Kode;
        if ($this->user->mataUangs()->save($mataUang)){
            return response()->json([
                "status" => true,
                "mataUang" => $mataUang
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "gagal save"
            ], 500);
        };
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\MataUangs  $mataUangs
     * @return \Illuminate\Http\Response
     */
    public function show(MataUangs $mataUangs, $id)
    {
        $mataUangs = MataUangs::find($id);
        return $mataUangs;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\MataUangs  $mataUangs
     * @return \Illuminate\Http\Response
     */
    public function edit(MataUangs $mataUangs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\MataUangs  $mataUangs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, MataUangs $mataUangs, $id)
    {
        // $this->validate($request, [
        //     "kode" => "required",
            
        // ]);
        $mataUangs = MataUangs::find($id);
        $mataUangs->Kode = $request->Kode;
        $mataUangs->Nama = $request->Nama;
        $mataUangs->Aktif = $request->Aktif;
        $mataUangs->DiUbahOleh = $this->user->Kode;

        if($mataUangs->save()){
            return response()->json([
                "status"=>true,
                "mataUangs"=>$mataUangs
            ]);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\MataUangs  $mataUangs
     * @return \Illuminate\Http\Response
     */
    public function destroy(MataUangs $mataUangs, $id)
    {
        $mataUangs = MataUangs::find($id);
        $mataUangs->Aktif = false;
        $mataUangs->DiUbahOleh = $this->user->Kode;
        if ($mataUangs->save()){
            return response()->json([
                "status"=> true,
                "mataUangs"=> $mataUangs
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }
}

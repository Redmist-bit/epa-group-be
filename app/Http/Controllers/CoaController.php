<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Coa;
use Illuminate\Http\Request;

class CoaController extends Controller
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
        return Coa::orderBy('Kode')->get();
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

        $coa = new Coa;
        $coa->Kode = $request->Kode;
        $coa->Nama = $request->Nama;
        // $coa->Deskripsi = $request->Deksripsi;
        // $coa->AccNo = $request->AccNo;
        $coa->Memo = $request->Memo;
        $coa->Aktif = $request->Aktif;
        $coa->IsDetail = $request->IsDetail;
        $coa->Sifat = $request->Sifat;
        $coa->DiUbahOleh = $this->user->Kode;

        if ($this->user->Coa()->save($coa)){
            return response()->json([
                "status" => true,
                "coa" => $coa
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
     * @param  \App\Models\Coa  $gudangs
     * @return \Illuminate\Http\Response
     */
    public function show(Coa $coa, $id)
    {
        $coa = Coa::find($id);
        return $coa;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Coa  $gudangs
     * @return \Illuminate\Http\Response
     */
    public function edit(Coa $coa)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Coa  $coa
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $this->validate($request, [
            "Kode" => "required",
            
        ]);
        
        $coa = Coa::find($id);
        $coa->Kode = $request->Kode;
        $coa->Nama = $request->Nama;
        // $coa->Deskripsi = $request->Deskripsi;
        // $coa->AccNo = $request->AccNo;
        $coa->Memo = $request->Memo;
        $coa->Aktif = $request->Aktif;
        $coa->IsDetail = $request->IsDetail;
        $coa->Sifat = $request->Sifat;
        $coa->DiUbahOleh = $this->user->Kode;

        if($coa->save()){
            return response()->json([
                "status"=>true,
                "coa"=>$coa
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
     * @param  \App\Models\Coa  $coa
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $coa = Coa::find($id);
        $coa->Aktif = false;
        $coa->DiUbahOleh = $this->user->Kode;
        if ($coa->save()){
            return response()->json([
                "status"=> true,
                "coa"=> $coa
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }
}

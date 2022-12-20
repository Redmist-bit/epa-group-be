<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
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
        $data = Unit::with('supplier:Kode,Nama')
        ->where('Unit.Kode','like',substr($this->user->Kode,0,2).'%')
        ->get();
        return response()->json([
            'data'=>$data,
            'status'=>'success'
        ],200);
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
        $last = $last = Unit::where('Kode','like',substr($this->user->Kode,0,2).'%')->orderByDesc('Kode')->first('Kode');
        if (is_null($last)) {
            $kode = substr($this->user->Kode,0,2).'/00000001';
        } else {
            $nmr = substr($last->Kode, -8);
            $kode = substr($this->user->Kode,0,2).'/'.str_pad($nmr + 1, 8, 0, STR_PAD_LEFT);
        }
        $unit = new Unit;
        $unit->Kode = $kode;
        $unit->Nama = $request->Nama;
        $unit->Product = $request->Product;
        $unit->Brand = $request->Brand;
        $unit->Type = $request->Type;
        $unit->CodeUnit = $request->CodeUnit;
        $unit->SerialNumber = $request->SerialNumber;
        $unit->TanggalPembelian = $request->TanggalPembelian;
        $unit->Supplier = $request->Supplier;
        $unit->Memo = $request->Memo;
        $unit->Aktif = true;
        $unit->Posting = substr($this->user->Kode,0,4).'/01';
        $unit->JamperHari = 24;
        $unit->DiUbahOleh = $this->user->Kode;
        if ($this->user->unit()->save($unit)){
            return response()->json([
                'status'=>'Success'
            ], 200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "failed"
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function show(Unit $unit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function edit(Unit $unit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $unit = Unit::find($id);
        $unit->Nama = $request->Nama;
        $unit->Product = $request->Product;
        $unit->Brand = $request->Brand;
        $unit->Type = $request->Type;
        $unit->CodeUnit = $request->CodeUnit;
        $unit->SerialNumber = $request->SerialNumber;
        $unit->TanggalPembelian = $request->TanggalPembelian;
        $unit->Supplier = $request->Supplier;
        $unit->Memo = $request->Memo;
        $unit->DiUbahOleh = $this->user->Kode;
        if($unit->save()){
            return response()->json([
                'data'=>$unit,
                'status'=>true
            ],200);
        } else {
            return response()->json([
                'status'=>'fail'
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Unit  $unit
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $unit = Unit::find($id);
        $unit->Aktif = false;
        $unit->DiUbahOleh = $this->user->Kode;
        if ($unit->save()){
            return response()->json([
                "status"=> true,
                "unit"=> $unit
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "fail"
            ]);
        }
    }
}

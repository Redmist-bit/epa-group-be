<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Periodes;
use Illuminate\Http\Request;
use App\Http\Controllers\StokController;
use Illuminate\Support\Facades\DB;

class PeriodesController extends Controller
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
    public function period()
    {
        $data = Periodes::orderBy('id','desc')->get(['id','Kode','Nama','TglAwal','TglAkhir','Status']);
        return $data;
    }
    public function index()
    {
        return Periodes::orderByDesc('id')->get();
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

        $periode = new Periodes;
        $periode->Kode = $request->Kode;
        $periode->Nama = $request->Nama;
        $periode->TglAwal = $request->TglAwal;
        $periode->TglAkhir = $request->TglAkhir;
        $periode->Status = $request->Status;
        $periode->DiUbahOleh = $this->user->Kode;

        // $stok = new StokController;
        // $stok->store($request->kode);

        if ($this->user->periodes()->save($periode)){
            return response()->json([
                "status" => true,
                "periode" => $periode
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
     * @param  \App\Models\Periodes  $periodes
     * @return \Illuminate\Http\Response
     */
    public function show(Periodes $periodes, $id)
    {
        $periodes = Periodes::find($id);
        return $periodes;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Periodes  $periodes
     * @return \Illuminate\Http\Response
     */
    public function edit(Periodes $periodes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Periodes  $periodes
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Periodes $periodes, $id)
    {
        $this->validate($request, [
            "Kode" => "required",
            
        ]);
        
        $periodes = Periodes::find($id);
        $periodes->Kode = $request->Kode;
        $periodes->Nama = $request->Nama;
        $periodes->TglAwal = $request->TglAwal;
        $periodes->TglAkhir = $request->TglAkhir;
        $periodes->Status = $request->Status;
        $periodes->DiUbahOleh = $this->user->Kode;

        $val = [];
        if (!empty($request->disable)) {
            for ($i=0; $i < count($request->disable); $i++) { 
                array_push($val,[
                    'event' => "updated",
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\Periode",
                    'auditable_id' => $request->disable[$i],
                    'new_values' => '{Status:0}'
                ]);
            }
            Periodes::whereIn('id',$request->disable)->update(['Status' => 0,'DiUbahOleh'=>$this->user->Kode]);
        } elseif (!empty($request->enable)) {
            for ($i=0; $i < count($request->enable); $i++) { 
                array_push($val,[
                    'event' => "updated",
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\Periode",
                    'auditable_id' => $request->enable[$i],
                    'new_values' => '{Status:1}'
                ]);
            }
            Periodes::whereIn('id',$request->enable)->update(['Status' => 1,'DiUbahOleh'=>$this->user->Kode]);
        }
        if($periodes->save()){
            if (count($val) > 0) {
                DB::table('audits')->insert($val);
            }
            
            return response()->json([
                "status"=>true,
                "periodes"=>$periodes
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
     * @param  \App\Models\Periodes  $periodes
     * @return \Illuminate\Http\Response
     */
    public function destroy(Periodes $periodes, $id)
    {
        $id = explode(',', $id);
        $val = [];
        for ($i=0; $i < count($id); $i++) { 
            array_push($val,[
                'event' => "updated",
                'user_type' => 'App\Models\User',
                'user_id' => $this->user->id,
                'auditable_type' => "App\Models\Periode",
                'auditable_id' => $id[$i],
                'new_values' => '{Status:0}'
            ]);
        }
        DB::table('audits')->insert($val);
        $periodes = Periodes::whereIn('id',$id)->update(['Status' => 0,'DiUbahOleh'=>$this->user->Kode]);
        return response()->json([
            "status"=> true,
            "periodes"=> $periodes
        ]);
    }
}

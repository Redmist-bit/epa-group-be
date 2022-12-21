<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\WorkOrder;
use App\Models\WoKeluhan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    public function lessor()
    {
        $data = collect();
        DB::table('Pelanggan')->
        select('Kode','Nama')->orderBy('Nama')->chunk(500, function($datas) use ($data) {
            foreach ($datas as $d) {
                $data->push($d);
            }
        });
        return response()->json([
            "data" => $data
        ]);
    }
    public function picScm()
    {
        $data = DB::table('User')->where('Departemen','SCM')->where('Aktif',true)->get('Nama');
        return response()->json([
            "data" => $data
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($from,$to)
    {
        $data = WorkOrder::with('pelanggan:Kode,Nama','uang:Kode,Nama','unit:Kode,Nama'
        // ,'pemilik:Kode,Nama','lessor:Kode,Nama','rangka.kendaraan:Kode,Nama','estimasi:KodeNota,NomorWO,TanggalKeluarFisik,NoTiketKeluar'
        )->where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        // ->whereBetween('DiBuatTgl', [$from, $to])
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->whereNotNull('NomorRangka')
        ->get();
        return response()->json(["data"=>$data],200);
    }

    public function wipProcessing($from,$to)
    {
        $data = WorkOrder::withCount('invoice','invoiceDeductible')
        ->with('estimasi:NomorWO,Tanggal,TanggalMasuk,TanggalKeluar,TanggalKeluarFisik',
        'invoiceDeductible:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        // 'invoiceNonClaim:KodeNota,NomorWO,TotalBayar',
        'invoiceDeductible.pelanggan:Kode,Nama',
        'invoice:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice.wo:KodeNota,Pelanggan,Pemilik',
        'invoice.pelanggan:Kode,Nama',
        'rangka.kendaraan:Kode,Nama',
        'pelanggan:Kode,Nama','pemilik:Kode,Nama','lessor:Kode,Nama')
        // ->with('invoice:KodeNota,NomorWO,TotalBayar,Pelanggan')
        // ->whereBetween('DiBuatTgl', [$from, $to])
        ->whereDate('Tanggal','>=',$from)
        ->whereDate('Tanggal','<=',$to)
        ->whereNull('Status')
        ->whereNotNull('NomorRangka')
        ->where(function($q){
            $q->whereNull('KeteranganWIP')->orWhere(function($q1){
                $q1->WhereNotIn('Coding',[
                    'D3',
                    'D',
                    'Z',
                    'X',
                    'T3',
                ])->orWhereNull('Coding');
            });
        })
        // ->whereNotIn('Coding',[
        // 'D3',
        // 'D',
        // 'Z',
        // 'X',
        // 'T3',
        // ])
        // ->orWhereNull('KeteranganWIP')
        // ->orWhereNull('Coding')
        ->get();
        return response()->json(["data"=>$data],200);
        //bug invoice ass total dan invoice non claim wip
    }
    
    public function wipFinance($from,$to)
    {
        $data = WorkOrder::withCount('invoice','invoiceDeductible')
        ->with(
        'invoiceDeductible:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice.wo:KodeNota,Pelanggan,Pemilik',
        'invoice.pelanggan:Kode,Nama',
        'rangka.kendaraan:Kode,Nama',
        'pelanggan:Kode,Nama','pemilik:Kode,Nama'
        )
        // ->whereBetween('DiBuatTgl', [$from, $to])
        ->whereDate('Tanggal','>=',$from)
        ->whereDate('Tanggal','<=',$to)
        ->whereNull('Status')
        ->where(function($q){
            $q->whereNull('KeteranganWIP')->orWhere(function($q1){
                $q1->WhereNotIn('Coding',[
                    'D',
                    'Z',
                    'X',
                ])->orWhereNull('Coding');
            });
        })
        // ->whereNotIn('Coding',[
        //     // 'D3',
        //     'D',
        //     'Z',
        //     'X',
        //     // 'T3',
        // ])
        ->get();
        return response()->json(["data"=>$data],200);
    }

    public function wipClaim($from,$to)
    {
        $data = WorkOrder::withCount('invoice','invoiceDeductible')
        ->with(
        'invoiceDeductible:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice.wo:KodeNota,Pelanggan,Pemilik',
        'invoice.pelanggan:Kode,Nama',
        'rangka.kendaraan:Kode,Nama',
        'pelanggan:Kode,Nama','pemilik:Kode,Nama'
        )
        // ->whereBetween('DiBuatTgl', [$from, $to])
        ->whereDate('Tanggal','>=',$from)
        ->whereDate('Tanggal','<=',$to)
        ->whereNull('Status')
        ->whereNotNull('NomorRangka')
        ->where(function($q){
            $q->whereNull('KeteranganWIP')->orWhere(function($q1){
                $q1->WhereNotIn('Coding',[
                    'D',
                    'Z',
                    'X','T1'
                ])->orWhereNull('Coding');
            });
        })
        ->get();
        return response()->json(["data"=>$data],200);
    }

    public function wipScm($from,$to)
    {
        $data = WorkOrder::withCount('invoice','invoiceDeductible')
        ->with(
        'invoiceDeductible:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice.wo:KodeNota,Pelanggan,Pemilik',
        'invoice.pelanggan:Kode,Nama',
        'pelanggan:Kode,Nama','pemilik:Kode,Nama'
        )
        // ->whereBetween('DiBuatTgl', [$from, $to])
        ->whereDate('Tanggal','>=',$from)
        ->whereDate('Tanggal','<=',$to)
        ->whereNull('Status')
        ->where(function($q){
            $q->whereNull('KeteranganWIP')->orWhere(function($q1){
                $q1->WhereNotIn('Coding',[
                    'D',
                    'Z',
                    'X','T3','R1','D3','A3'
                ])->orWhereNull('Coding');
            });
        })
        ->get();
        return response()->json(["data"=>$data],200);
    }

    public function wipAnalis($from,$to)
    {
        $data = WorkOrder::withCount('invoice','invoiceDeductible')
        ->with('estimasi:NomorWO,Tanggal',
        'invoiceDeductible:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice:KodeNota,NomorWO,TotalBayar,Pelanggan,OnRisk',
        'invoice.wo:KodeNota,Pelanggan,Pemilik',
        'invoice.pelanggan:Kode,Nama',
        'rangka.kendaraan:Kode,Nama',
        'pelanggan:Kode,Nama','pemilik:Kode,Nama'
        )
        // ->whereBetween('DiBuatTgl', [$from, $to])
        ->whereDate('Tanggal','>=',$from)
        ->whereDate('Tanggal','<=',$to)
        ->whereNotNull('NomorRangka')
        ->whereNull('Status')
        ->get();
        return response()->json(["data"=>$data],200);
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
        $last = WorkOrder::where('KodeNota','like',substr($this->user->Kode,0,2).'%')->latest()->first();
        $kode = substr($this->user->Kode,0,4).'/WO/';
        date_default_timezone_set('Asia/Makassar');
        $periode = date('ym');
        if (!$last) {
            $kode = $kode.$periode.'/000001';
        } elseif (substr($last->KodeNota,8,4) === $periode) {
            $nomer = substr($last->KodeNota,13);
            $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);;
        } else {
            $kode = $kode.$periode.'/000001';
        }
        $data = new WorkOrder;
        $data->KodeNota = $kode;
        $data->Tanggal = $request->Tanggal;
        $data->NomorMesin = 0;
        $data->NomorPolisi = 0;
        $data->JenisWorkOrder = $request->JenisWorkOrder;
        $data->Odometer = $request->Odometer;
        $data->Lokasi = $request->Lokasi;
        $data->PaymentTerm = $request->PaymentTerm;
        $data->Pelanggan = $request->Pelanggan;
        $data->MataUang = $request->MataUang;
        // $data->Pemilik = $request->Pemilik;
        $data->Kurs = $request->Kurs;
        // $data->Lessor = $request->Lessor;
        $data->SellTo = $request->SellTo;
        $data->Keterangan = $request->Keterangan;
        $data->Referensi = $request->Referensi;
        $data->NomorRangka = 0;
        $data->DPP = $request->DPP;
        $data->PPn = $request->PPn;
        $data->PPnPersen = $request->PPnPersen;
        $data->DiUbahOleh = $this->user->Kode;
        $data->ReserveOutcome = $request->ReserveOutcome;
        $data->ReserveOutcomeJasa = $request->ReserveOutcomeJasa;
        $data->Unit = $request->Unit;
        if ($this->user->WorkOrder()->save($data)) {
            //save detailnya belum ya 
            // for ($i=0; $i < count($request->keluhan); $i++) { 
            //     $urutan = $i;
            //     $keluhan = new WoKeluhan;
            //     $keluhan->NoUrut = $urutan+1;
            //     $keluhan->Keluhan = $request->keluhan[$i]['Keluhan'];
            //     $data->keluhan()->save($keluhan);
            // }
            return response()->json([
                "status" => true,
                "data" => $data,
                // "keluhan" => count($request->keluhan)
            ],200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "failed to save"
            ],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = WorkOrder::with('keluhan')->find($id);
        return response()->json([
            "status" => true,
            "data" => $data
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function edit(WorkOrder $workOrder)
    {
        //
    }

    public function updateWipClaim(Request $request,$id)
    {
        // $data = WorkOrder::find($id);
        // $data->Persetujuan = $request->Persetujuan;
        // $data->KeteranganWIP = $request->KeteranganWIP;
        // $data->Coding = $request->Coding;
        // $data->Remarks = $request->Remarks;
        // $data->PICSite = $request->PICSite;
        // $data->Surveyor = $request->Surveyor;
        // $data->JenisKerusakan = $request->JenisKerusakan;
        // $data->ProgressPengerjaan = $request->ProgressPengerjaan;
        // $data->DetailProgress = $request->DetailProgress;
        // $data->TglDOL = $request->TglDOL;
        // $data->ReserveOutcome = $request->ReserveOutcome;
        // $data->NoPolisAsuransi = $request->NoPolisAsuransi;
        // $data->NoRegistrasi = $request->NoRegistrasi;
        // $data->RFU = $request->RFU;
        // $data->CekList = $request->CekList;
        // $data->Adjuster = $request->Adjuster;
        // $data->PICAdj = $request->PICAdj;
        // $data->Broker = $request->Broker;
        // $data->PICBroker = $request->PICBroker;
        // $data->DiUbahOleh = $this->user->Kode;
        // $data->save();
        // return response()->json([
        //     "status" => 'success',
        //     "message" => 'update '.'KeteranganWIP'.'to '.$request->KeteranganWIP
        // ],200);

        $data = WorkOrder::findMany(explode(',',$id));
        $rowData = $request->rowData;
        $data->each(function ($item,$Key) use($rowData){
            $item->update([
                'KeteranganWIP' => $rowData[$Key]['KeteranganWIP'],
                'Coding' => $rowData[$Key]['Coding'],
                'Persetujuan' => $rowData[$Key]['Persetujuan'],
                'Remarks' => $rowData[$Key]['Remarks'],
                'PICSite' => $rowData[$Key]['PICSite'],
                'Surveyor' => $rowData[$Key]['Surveyor'],
                'JenisKerusakan' => $rowData[$Key]['JenisKerusakan'],
                'ProgressPengerjaan' => $rowData[$Key]['ProgressPengerjaan'],
                'DetailProgress' => $rowData[$Key]['DetailProgress'],
                'TglDOL' => $rowData[$Key]['TglDOL'],
                'ReserveOutcome' => $rowData[$Key]['ReserveOutcome'],
                'NoPolisAsuransi' => $rowData[$Key]['NoPolisAsuransi'],
                'NoRegistrasi' => $rowData[$Key]['NoRegistrasi'],
                'RFU' => $rowData[$Key]['RFU'],
                'CekList' => $rowData[$Key]['CekList'],
                'Adjuster' => $rowData[$Key]['Adjuster'],
                'PICAdj' => $rowData[$Key]['PICAdj'],
                'Broker' => $rowData[$Key]['Broker'],
                'PICBroker' => $rowData[$Key]['PICBroker'],
                'DiUbahOleh' => $rowData[$Key]['DiUbahOleh'],
            ]);
        });
        return response()->json([
            "status" => true,
            "message" => 'update success'
        ],200);
    }

    public function updateWipFinanc(Request $request,$id)
    {
        // $data = WorkOrder::find($id);
        // $data->KeteranganWIP = $request->KeteranganWIP;
        // $data->Coding = $request->Coding;
        // $data->DiUbahOleh = $this->user->Kode;
        // $data->save();
        $data = WorkOrder::findMany(explode(',',$id));
        $rowData = $request->rowData;
        $data->each(function ($item,$Key) use($rowData){
            $item->update([
                'KeteranganWIP' => $rowData[$Key]['KeteranganWIP'],
                'Coding' => $rowData[$Key]['Coding'],
                'DiUbahOleh' => $rowData[$Key]['DiUbahOleh'],
            ]);
        });
        return response()->json([
            "status" => true,
            "message" => 'update success'
        ],200);
    }

    public function updateWipProc(Request $request,$id)
    {
        // RecomendPart::where('KodeNota',$request->wo)->where('NoPartOrder',$request->kodeNota)->where('NoUrut',$urut)
        // ->where('Barang',$request->Barang)->update([$request->field => $request->data,'DiUbahOleh' => $this->user->Kode]);
        // DB::table('audits')->insert([
        //     'event' => "updated",
        //     'user_type' => 'App\Models\User',
        //     'user_id' => $this->user->id,
        //     'auditable_type' => "App\Models\WorkOrder",
        //     'auditable_id' => $id,
        //     'old_values' => $request->oldVal,
        //     'new_values' => $request->data
        // ]);
        // $field = $request->field;
        // $newData = $request->data;
        // return explode(',',$id);
        $data = WorkOrder::findMany(explode(',',$id));
        $rowData = $request->rowData;
        $data->each(function ($item,$Key) use($rowData){
            $item->update([
                'KeteranganWIP' => $rowData[$Key]['KeteranganWIP'],
                'Coding' => $rowData[$Key]['Coding'],
                'Lessor' => $rowData[$Key]['Lessor'],
                'Persetujuan' => $rowData[$Key]['Persetujuan'],
                'TglSPK' => $rowData[$Key]['TglSPK'],
                'TglTerimaSPK' => $rowData[$Key]['TglTerimaSPK'],
                'RFU' => $rowData[$Key]['RFU'],
                'AdmClear' => $rowData[$Key]['AdmClear'],
                'ReserveOutcome' => $rowData[$Key]['ReserveOutcome'],
                'ReserveOutcomeJasa' => $rowData[$Key]['ReserveOutcomeJasa'],
                'MataUang' => $rowData[$Key]['MataUang'],
                'Kurs' => $rowData[$Key]['Kurs'],
                'CekList' => $rowData[$Key]['CekList'],
                'Remarks' => $rowData[$Key]['Remarks'],
                'TglDOL' => $rowData[$Key]['TglDOL'],
                'NoPolisAsuransi' => $rowData[$Key]['NoPolisAsuransi'],
                'NoRegistrasi' => $rowData[$Key]['NoRegistrasi'],
                'Adjuster' => $rowData[$Key]['Adjuster'],
                'PICAdj' => $rowData[$Key]['PICAdj'],
                'Broker' => $rowData[$Key]['Broker'],
                'PICBroker' => $rowData[$Key]['PICBroker'],
                'DiUbahOleh' => $this->user->Kode
            ]);
        });
        return response()->json(['p' => $data]);
        // $data->$field = $data;
        // $data->Lessor = $request->Lessor;
        // $data->Persetujuan = $request->Persetujuan;
        // $data->TglSPK = $request->TglSPK;
        // $data->TglTerimaSPK = $request->TglTerimaSPK;
        // $data->RFU = $request->RFU;
        // $data->AdmClear = $request->AdmClear;
        // $data->ReserveOutcome = $request->ReserveOutcome;
        // $data->ReserveOutcomeJasa = $request->ReserveOutcomeJasa;
        // $data->MataUang = $request->MataUang;
        // $data->Kurs = $request->Kurs;
        // $data->KeteranganWIP = $request->KeteranganWIP;
        // $data->Coding = $request->Coding;
        // $data->CekList = $request->CekList;
        // $data->Remarks = $request->Remarks;
        // $data->TglDOL = $request->TglDOL;
        // $data->NoPolisAsuransi = $request->NoPolisAsuransi;
        // $data->NoRegistrasi = $request->NoRegistrasi;
        // $data->Adjuster = $request->Adjuster;
        // $data->PICAdj = $request->PICAdj;
        // $data->Broker = $request->Broker;
        // $data->PICBroker = $request->PICBroker;
        // $data->DiUbahOleh = $this->user->Kode;
        // $data->save();
        return response()->json([
            "status" => 'success',
            "message" => 'update '.$request->field.'to '.$request->data
        ],200);
    }

    public function updateWipAnalis(Request $request,$id)
    {
        // $data = WorkOrder::find($id);
        // $data->Analisa = $request->Analisa;
        // $data->NoteAnalis = $request->NoteAnalis;
        // $data->DiUbahOleh = $this->user->Kode;
        // $data->save();
        // return response()->json([
        //     "status" => 'success',
        //     "message" => 'update '.'KeteranganWIP'.'to '.$request->KeteranganWIP
        // ],200);
        $data = WorkOrder::findMany(explode(',',$id));
        $rowData = $request->rowData;
        $data->each(function ($item,$Key) use($rowData){
            $item->update([
                'Analisa' => $rowData[$Key]['Analisa'],
                'NoteAnalis' => $rowData[$Key]['NoteAnalis'],
                'DiUbahOleh' => $rowData[$Key]['DiUbahOleh'],
            ]);
        });
        return response()->json([
            "status" => true,
            "message" => 'update success'
        ],200);
    }

    public function updateWipScm(Request $request,$id)
    {
        // $data = WorkOrder::find($id);
        // $data->RemarksSCM = $request->RemarksSCM;
        // $data->PICSCM1 = $request->PICSCM1;
        // $data->PICSCM2 = $request->PICSCM2;
        // $data->DiUbahOleh = $this->user->Kode;
        // $data->save();
        // return response()->json([
        //     "status" => 'success',
        //     "message" => 'update '.'KeteranganWIP'.'to '.$request->KeteranganWIP
        // ],200);

        $data = WorkOrder::findMany(explode(',',$id));
        $rowData = $request->rowData;
        $data->each(function ($item,$Key) use($rowData){
            $item->update([
                'RemarksSCM' => $rowData[$Key]['RemarksSCM'],
                'PICSCM1' => $rowData[$Key]['PICSCM1'],
                'PICSCM2' => $rowData[$Key]['PICSCM2'],
                'DiUbahOleh' => $rowData[$Key]['DiUbahOleh'],
            ]);
        });
        return response()->json([
            "status" => true,
            "message" => 'update success'
        ],200);
    }

    public function updateOwnRisk(Request $request,$id)
    {
        $data = WorkOrder::findMany(explode(',',$id));
        $rowData = $request->rowData;
        $data->each(function ($item,$Key) use($rowData){
            $item->update([
                'OwnRisk' => $rowData[$Key]['OwnRisk'],
                'TglOwnRisk' => $rowData[$Key]['TglOwnRisk'],
                'KeteranganWIP' => $rowData[$Key]['KeteranganWIP'],
                'Coding' => $rowData[$Key]['Coding'],
            ]);
        });
        return response()->json([
            "status" => true,
            "message" => 'update success'
        ],200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $data = WorkOrder::find($id);
        // $data->KodeNota = $kode;
        $data->JenisWorkOrder = $request->JenisWorkOrder;
        $data->Tanggal = $request->Tanggal;
        // $data->NomorMesin = $request->NomorMesin;
        // $data->NomorPolisi = $request->NomorPolisi;
        $data->Odometer = $request->Odometer;
        $data->Lokasi = $request->Lokasi;
        $data->PaymentTerm = $request->PaymentTerm;
        $data->Pelanggan = $request->Pelanggan;
        $data->MataUang = $request->MataUang;
        // $data->Pemilik = $request->Pemilik;
        $data->Kurs = $request->Kurs;
        // $data->Lessor = $request->Lessor;
        $data->SellTo = $request->SellTo;
        $data->Keterangan = $request->Keterangan;
        $data->Referensi = $request->Referensi;
        // $data->NomorRangka = $request->NomorRangka;
        $data->DPP = $request->DPP;
        $data->PPn = $request->PPn;
        $data->PPnPersen = $request->PPnPersen;
        $data->DiUbahOleh = $this->user->Kode;
        $data->ReserveOutcome = $request->ReserveOutcome;
        $data->ReserveOutcomeJasa = $request->ReserveOutcomeJasa;
        $data->Unit = $request->Unit;
        if ($data->save()) {
            // for ($i=0; $i < count($request->keluhan); $i++) { 
            //     if (isset($request->keluhan[$i]['NoUrut']) == false) {
            //         $last = WoKeluhan::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
            //         $urutan = empty($last) == true ? 1 : $last['NoUrut'] + 1;
            //         $keluhan = new WoKeluhan;
            //         $keluhan->NoUrut = $urutan;
            //         $keluhan->Keluhan = $request->keluhan[$i]['Keluhan'];
            //         $data->keluhan()->save($keluhan);
            //     } else {
            //         # code...
            //         $keluhan = WoKeluhan::where('KodeNota',$request->KodeNota)
            //         ->where('NoUrut',$request->keluhan[$i]['NoUrut'])->get('Keluhan');
            //         // return $keluhan[0]->Keluhan;
            //         WoKeluhan::where('KodeNota',$request->KodeNota)
            //         ->where('NoUrut',$request->keluhan[$i]['NoUrut'])
            //         ->update(['Keluhan' => $request->keluhan[$i]['Keluhan']]);
            //         if ($keluhan[0]->Keluhan != $request->keluhan[$i]['Keluhan']) {
            //             DB::table('audits')->insert([
            //                 'event' => "updated",
            //                 'auditable_type' => "App\Models\WoKeluhan",
            //                 'auditable_id' => $request->keluhan[$i]['NoUrut'],
            //                 'old_values' => $keluhan[0]['Keluhan'],
            //                 'new_values' => $request->keluhan[$i]['Keluhan']
            //             ]);
            //         }
            //         // return $keluhan;
            //         // $keluhan->Keluhan = $request->keluhan[$i]['Keluhan'];
            //         // $keluhan->save();
            //     }
                
            // }
            // if (!empty($request->hapus_items)) {
            //     for ($i=0; $i < count($request->hapus_items); $i++) { 
            //         // return $request->hapus_items[$i];
            //         $kl = WoKeluhan::where('KodeNota',$request->KodeNota)->where('NoUrut',$request->hapus_items[$i])->get();
            //         DB::table('audits')->insert([
            //             'event' => "deleted",
            //             'auditable_type' => "App\Models\WoKeluhan",
            //             'auditable_id' => $request->hapus_items[$i],
            //             'old_values' => $kl,
            //             'new_values' => '[]'
            //         ]);
            //         // echo $kl;
            //         WoKeluhan::where('KodeNota',$request->KodeNota)->where('NoUrut',$request->hapus_items[$i])->delete();
            //     }
            //     return response()->json([
            //         "status"=>true,
            //         "data"=>$data,
            //         "items"=>$request->keluhan,
            //         "delet item"=>count($request->hapus_items)
            //     ],200);
            // }
            return response()->json([
                "status"=>true,
                "data"=>$data,
                // "items"=>$request->keluhan,
            ],200);
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
     * @param  \App\Models\WorkOrder  $workOrder
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $data = WorkOrder::find($id);
        // $item = $data->items()->get('id')
        // date_default_timezone_set('Asia/Makassar');
        // $tgl = date('d/m/y H:i:s A');
        // $keterangan = "Batal oleh ". $this->user->Kode . " - " . $this->user->Name . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        // $data->Status = 'BATAL';
        // $data->KeteranganStatus = $keterangan;
        // $data->DiUbahOleh = $this->user->Kode;
        // $data->save();
        // return response()->json([
        //     "status"=>true,
        //     "message"=>"berhasil batalin"
        // ],200);
    }

    public function batalin(Request $request, $id)
    {
        $data = WorkOrder::find($id);
        // $item = $data->items()->get('id')
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->Kode . " - " . $this->user->Name . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $data->Status = 'BATAL';
        $data->KeteranganStatus = $keterangan;
        $data->DiUbahOleh = $this->user->Kode;
        $data->save();
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    public function report(Request $request,$id)
    {
        if ($request->Cetak === 'cetakCheckList') {
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama','lessor:Kode,Nama')->select('id','KodeNota','Pelanggan','Pemilik','Lessor','Tanggal','NomorPolisi','Lokasi')->find($id);
        } elseif ($request->Cetak === 'cetakClaim') {
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama,Alamat,Kota','rangka:NomorRangka,Warna,Tahun,Kendaraan','rangka.kendaraan:Kode,Merk,Nama','estimasi:KodeNota,NomorWO,TanggalMasuk,TanggalKeluar,TanggalKeluarFisik')->select('id','KodeNota','Tanggal','Pemilik','Pelanggan','NomorRangka','NomorMesin','NomorPolisi')->find($id);
        } elseif ($request->Cetak === 'cetakGesekRangka') {
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama','rangka:NomorRangka,Warna,Tahun,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','Pemilik','NomorRangka','NomorPolisi','NomorMesin')->find($id);
        } elseif ($request->Cetak === 'cetakKuitansiOR') {
            $data = WorkOrder::with('estimasi:KodeNota,NomorWO,OnRisk','pelanggan:Kode,Nama','pemilik:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','Pemilik','NomorRangka','NomorPolisi','OwnRisk')->find($id);
        } elseif ($request->Cetak === 'cetakKuitansiULIN') {
            $data = WorkOrder::with('estimasi:KodeNota,NomorWO,OnRisk','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','Pemilik','NomorRangka','NomorPolisi','OwnRisk')->find($id);
        } elseif ($request->Cetak === 'cetakTiket') {
            $data = WorkOrder::with('estimasi:KodeNota,NomorWO,OnRisk,NoTiketKeluar,TanggalKeluarFisik','pelanggan:Kode,Nama','pemilik:Kode,Nama','rangka:NomorRangka,Warna,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Tanggal','Pelanggan','Pemilik','OwnRisk','NomorRangka','NomorPolisi')->find($id);
        } elseif ($request->Cetak === 'cetakWOI') {
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama','rangka:NomorRangka,Warna,Kendaraan,Tahun','rangka.kendaraan:Kode,Merk,Nama','rwl:KodeNota,JenisPekerjaan,Keterangan','rwl.pekerjaan:Kode,Nama')->select('id','KodeNota','NomorPolisi','NomorRangka','NomorMesin','Odometer','Pelanggan','Pemilik','Tanggal','TanggalIWO','Keterangan')->find($id);
        } elseif ($request->Cetak === 'cetakHE') {
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama,Alamat,Telp','rangka:NomorRangka,Warna,Kendaraan,Tahun','rangka.kendaraan:Kode,Merk,Nama','rwl:KodeNota,JenisPekerjaan,Keterangan','rwl.pekerjaan:Kode,Nama','keluhan')->select('id','KodeNota','NomorPolisi','NomorRangka','NomorMesin','Odometer','Pelanggan','Pemilik','Tanggal','Keterangan')->find($id);
        } elseif ($request->Cetak === 'cetakCAR') {
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama,Alamat,Telp','rangka:NomorRangka,Warna,Kendaraan,Tahun','rangka.kendaraan:Kode,Merk,Nama','keluhan')->select('id','KodeNota','NomorPolisi','NomorRangka','NomorMesin','Odometer','Pelanggan','Pemilik','Tanggal','Keterangan')->find($id);
        }
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }

    public function contextMenu(Request $request,$id)
    {
        // return $request->update;
        $data = WorkOrder::find($id);
        switch ($request->update) {
            case 'CloseWO':
                $data->IsClose = $request->CloseWO;
                $data->DiUbahOleh = $this->user->Kode;
                $data->save();
                break;
            case 'UbahTanggalMasuk/TanggalKeluar':
                $estimasi = $data->estimasi;
                $estimasi->TanggalMasuk = $request->awal;
                $estimasi->TanggalKeluar = $request->akhir;
                $estimasi->DiUbahOleh = $this->user->Kode;
                $estimasi->save();
                break;
            case 'UbahTanggalKeluarFisik':
                $lastTicket = DB::table('MasterEstimasi')
                ->where('NoTiketKeluar','like','%0101/TK/'.substr($request->keluarFisik,0,4).substr($request->keluarFisik,5,2).'/%')
                ->orderByDesc('NoTiketKeluar')->limit(1)->get('NoTiketKeluar');
                // return count($lastTicket);
                if (count($lastTicket) == 0) {
                    $tiket = '0101/TK/'.substr($request->keluarFisik,0,4).substr($request->keluarFisik,5,2).'/000001';
                } else {
                    $nomer = substr($lastTicket[0]->NoTiketKeluar,15);
                    $tiket = '0101/TK/'.substr($request->keluarFisik,0,4).substr($request->keluarFisik,5,2).'/'.str_pad($nomer+1, 6, '0', STR_PAD_LEFT);;
                }
                $estimasi = $data->estimasi;
                $estimasi->TanggalKeluarFisik = $request->keluarFisik;
                $estimasi->NoTiketKeluar = $tiket;
                $estimasi->DiUbahOleh = $this->user->Kode;
                $estimasi->save();
                break;
            default:
                # code...
                break;
        }
        return response()->json([
            "status" => true,
            "message" => "success update"
        ],200);
    }
}

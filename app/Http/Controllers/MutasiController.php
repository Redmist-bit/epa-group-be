<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Mutasi;
use App\Models\ItemsMutasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MutasiController extends Controller
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
    public function indexKas($from,$to)
    {
        $kk = Mutasi::where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->where('KodeNota','like','%KK%')
        ->get();
        $km = Mutasi::where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->Where('KodeNota','like','%KM%')
        ->get();
        $data = $kk->merge($km);
        return response()->json([
            'data' => $data
        ],200);
    }
    public function indexBank($from,$to)
    {
        $bk = Mutasi::where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->where('KodeNota','like','%BK%')
        ->get();
        $bm = Mutasi::where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->where('KodeNota','like','%BM%')
        ->get();
        $data = $bk->merge($bm);
        return response()->json([
            'data' => $data
        ],200);
    }

    public function dataPv(Request $request)
    {
        $skip = $request->query('skip');
        $take = $request->query('take');
        $search = $request->query('search');

        if ($search != null) {
            $allColumns = ['KodeNota','Keterangan','Tanggal'];
            $data = DB::table('MasterPreKasBank')->select('KodeNota','Keterangan','Tanggal','DiBuatOleh','DiBuatTgl','DiUbahTgl','DiUbahOleh');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->orderBy('MasterPreKasBank.KodeNota')->offset($skip)->limit($take)->get();
        } else {
            $count = DB::table('MasterPreKasBank')->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->count();
            $data = DB::table('MasterPreKasBank')->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->select('KodeNota','Keterangan','Tanggal','DiBuatOleh','DiBuatTgl','DiUbahTgl','DiUbahOleh')
            ->orderBy('MasterPreKasBank.KodeNota')->offset($skip)->limit($take)->get();
        }
        
        return response()->json([
            'result' => $data,
            'count' => $count
        ]);
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
        $kode = substr($this->user->Kode,0,4).'/';// KK, KM, BK, OR BM
        date_default_timezone_set('Asia/Makassar');
        $periode = date('ym');

        if ($request->modulMutasi == "KAS") {
            if ($request->arus_kas == "KK") {
                $last = Mutasi::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','like','%KK%')->orderByDesc('KodeNota')->first('KodeNota');
                if (!$last) {
                    $kode = $kode.$request->arus_kas.'/'.$periode.'/000001';
                } else if (substr($last->KodeNota,8,4) === $periode) {
                    $nomer = substr($last->KodeNota,13);
                    $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);
                } else {
                    $kode = $kode.$request->arus_kas.'/'.$periode.'/000001';
                }
            } else {
                $last = Mutasi::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','like','%KM%')->orderByDesc('KodeNota')->first('KodeNota');
                if (!$last) {
                    $kode = $kode.$request->arus_kas.'/'.$periode.'/000001';
                } else if (substr($last->KodeNota,8,4) === $periode) {
                    $nomer = substr($last->KodeNota,13);
                    $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);
                } else {
                    $kode = $kode.$request->arus_kas.'/'.$periode.'/000001';
                }
            }
        } else if ($request->modulMutasi == "BANK") {
            if ($request->arus_bank == "BK") {
                $last = Mutasi::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','like','%BK%')->orderByDesc('KodeNota')->first('KodeNota');
                if (!$last) {
                    $kode = $kode.$request->arus_bank.'/'.$periode.'/000001';
                } else if (substr($last->KodeNota,8,4) === $periode) {
                    $nomer = substr($last->KodeNota,13);
                    $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);
                } else {
                    $kode = $kode.$request->arus_bank.'/'.$periode.'/000001';
                }
            } else {
                $last = Mutasi::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','like','%BM%')->orderByDesc('KodeNota')->first('KodeNota');
                if (!$last) {
                    $kode = $kode.$request->arus_bank.'/'.$periode.'/000001';
                } else if (substr($last->KodeNota,8,4) === $periode) {
                    $nomer = substr($last->KodeNota,13);
                    $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);
                } else {
                    $kode = $kode.$request->arus_bank.'/'.$periode.'/000001';
                }
            }
        }

        // if (!$last) {
        //     $kode = str_contains($request->Tipe,"KAS") ? $kode.$request->arus_kas.'/'.$periode.'/000001' : $kode.$request->arus_bank.'/'.$periode.'/000001';
        // } elseif (substr($last->KodeNota,8,4) === $periode) {
        //     $nomer = substr($last->KodeNota,13);
        //     $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);
        // } else {
        //     $kode = str_contains($request->Tipe,"KAS") ? $kode.$request->arus_kas.'/'.$periode.'/000001' : $kode.$request->arus_bank.'/'.$periode.'/000001';
        // }
        // return $kode;
        $mutasi = new Mutasi;
        $mutasi->KodeNota = $kode;
        $mutasi->Tanggal = $request->Tanggal;
        $mutasi->PerkiraanKasBank = $request->PerkiraanKasBank;
        $mutasi->Referensi = $request->Referensi;
        $mutasi->Tipe = $request->Tipe;
        $mutasi->NoCekBG = $request->NoCekBG;
        $mutasi->StatusCekBG = $request->StatusCekBG;
        $mutasi->TglCairBatalCekBG = $request->TglCairBatalCekBG;
        // $mutasi->total = $request->total;
        // $mutasi->status = $request->status;
        // $mutasi->keterangan_status = $request->keterangan_status;
        $mutasi->Keterangan = $request->Keterangan;
        $mutasi->DiUbahOleh = $this->user->Kode;
        if ($this->user->mutasi()->save($mutasi)) {
            $items = collect($request->items)->map(function ($item) {
                $item['Perkiraan'] = $item['KodePerkiraan'];
                $item['MataUang'] = $item['KodeUang'];
                unset($item['Kurs']);
                return $item;
            });
            $mutasi->items()->createMany($items);
            // for ($i=0; $i < count($request->items); $i++) { 
            //     $urutan = $i;
            //     $item = new ItemsMutasi;
            //     $item->no_urut = $urutan+1;
            //     $item->perkiraan = $request->items[$i]['perkiraan']['Kode'];
            //     $item->NomorWO = $request->items[$i]['NomorWO'];
            //     $item->Keterangan = $request->items[$i]['Keterangan'];
            //     $item->MataUang = $request->items[$i]['MataUang'];
            //     $item->JumlahAsing = $request->items[$i]['JumlahAsing'];
            //     $item->kurs = $request->items[$i]['kurs'];
            //     $item->Jumlah = $request->items[$i]['Jumlah'];
            //     $mutasi->items()->save($item);
            // }
            return response()->json([
                "status" => true,
                "mutasi" => $mutasi,
                "items" => count($request->items),
            ],200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "gagal save"
            ], 500);
        }
        
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mutasi  $mutasi
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Mutasi::with('perkiraan:id,Kode,Nama')->with(['items' => function ($q){
            return $q->with('perkiraan:id,Kode,Nama','mataUang:id,Kode,Nama');
        }])->find($id);
        return response()->json(['data'=>$data]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mutasi  $mutasi
     * @return \Illuminate\Http\Response
     */
    public function edit(Mutasi $mutasi)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mutasi  $mutasi
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $mutasi = Mutasi::find($id);
        $mutasi->Tanggal = $request->Tanggal;
        $mutasi->PerkiraanKasBank = $request->PerkiraanKasBank;
        $mutasi->Referensi = $request->Referensi;
        $mutasi->Tipe = $request->Tipe;
        $mutasi->NoCekBG = $request->NoCekBG;
        $mutasi->StatusCekBG = $request->StatusCekBG;
        $mutasi->TglCairBatalCekBG = $request->TglCairBatalCekBG;

        $mutasi->Keterangan = $request->Keterangan;
        $mutasi->DiUbahOleh = $this->user->Kode;
        $last = ItemsMutasi::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        $items = collect($request->new_items)->map(function ($itm,$key) use($last){
            $itm['NoUrut'] = $key+1+$last['NoUrut'];
            $itm['Perkiraan'] = $itm['KodePerkiraan'];
            $itm['MataUang'] = $itm['KodeUang'];
            unset($itm['Kurs']);
            return $itm;
        });
        // return $items;
        if ($mutasi->save()) {
            // for ($i=0; $i < count($request->items); $i++) { 
            //     if (isset($request->items[$i]['id']) == false) {
            //         $last = ItemsMutasi::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
            //         $urutan = $last['NoUrut'] + 1;
            //         $item = new ItemsMutasi;
            //         $item->no_urut = $urutan+1;
            //         $item->perkiraan = $request->items[$i]['perkiraan']['Kode'];
            //         $item->no_wo = $request->items[$i]['no_wo'];
            //         $item->keterangan = $request->items[$i]['keterangan'];
            //         $item->mata_uang = $request->items[$i]['mata_uang'];
            //         $item->jumlah_asing = $request->items[$i]['jumlah_asing'];
            //         $item->kurs = $request->items[$i]['kurs'];
            //         $item->jumlah = $request->items[$i]['jumlah'];
            //         $mutasi->items()->save($item);
            //     } else {
            //         $item = ItemsMutasi::find($request->items[$i]['id']);
            //         $item->perkiraan = $request->items[$i]['perkiraan']['Kode'];
            //         $item->no_wo = $request->items[$i]['no_wo'];
            //         $item->keterangan = $request->items[$i]['keterangan'];
            //         $item->mata_uang = $request->items[$i]['mata_uang'];
            //         $item->jumlah_asing = $request->items[$i]['jumlah_asing'];
            //         $item->kurs = $request->items[$i]['kurs'];
            //         $item->jumlah = $request->items[$i]['jumlah'];
            //         $item->save();
            //     }
            // }
            $mutasi->items()->createMany($items);
            if (!empty($request->hapus_items)) {
                // $hps = ItemsMutasi::destroy($request->hapus_items);
                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = ItemsMutasi::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsMutasi",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
                return response()->json([
                    "status" => true,
                    "mutasi" => $mutasi,
                    "items" => $request->items,
                    "deleted Items" => $request->hapus_items
                ],200);
            }
            return response()->json([
                "status"=>true,
                "mutasi" => $mutasi,
                "items" => $request->items,
            ],200);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"failed to update"
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mutasi  $mutasi
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // $data = Mutasi::find($id);
        // $item = $data->items()->get('id');
        // $x = [];
        // foreach ($item as $key => $value) {
        //     array_push($x,$value['id']);
        // }
        // if ($data->delete()) {
        //     ItemsMutasi::destroy($x);
        //     return response()->json([
        //         "status" => true,
        //         "mutasi" => $data
        //     ],200);
        // } else {
        //     return response()->json([
        //         "status" => false,
        //         "message" => "fail"
        //     ],500);
        // }
        return response()->json([
            'info' => 'nothing method for deleted data master mutasi'
        ]);
    }

    public function batalin(Request $request, Mutasi $mutasi, $id)
    {
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->Kode . " - " . $this->user->Nama . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $data = $mutasi::find($id);
        $data->Status = 'BATAL';
        $data->KeteranganStatus = $keterangan;
        $data->DiUbahOleh = $this->user->Kode;
        $data->save();
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    public function report($id)
    {
        $data = Mutasi::with('items:KodeNota,Perkiraan,NoUrut,Keterangan,Jumlah','items.perkiraan:Kode,Nama','perkiraan:Kode,Nama')->select('KodeNota','Tanggal','Total','Keterangan','PerkiraanKasBank')->find($id);
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

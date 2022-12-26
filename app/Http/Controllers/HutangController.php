<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Hutang;
use App\Models\Pembelians;
use App\Models\ItemsHutangPembelian;
use App\Models\ItemsHutangPembayaran;
use App\Models\ItemsPembelians;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HutangController extends Controller
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
    public function index($from,$to)
    {
        $data = Hutang::where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->with('supplier:id,Kode,Nama')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        // whereBetween('DiBuatTgl',[$from,$to])
        ->get();
        return response()->json([
            'data' => $data
        ],200);
    }

    public function dataPembelian(Request $request,Pembelians $pembelian)
    {
        $data = $pembelian::where('Supplier',$request->supplier)
        ->whereNull('Status')
        ->where('SisaBayar','<>',0)
        // ->with('wo:KodeNota,NomorPolisi')
        ->get(['id','KodeNota',
        // 'NoWorkOrder',
        'Tanggal','Status','TotalBayar','Keterangan','Referensi','SisaBayar']);
        return response()->json([
            'data' => $data
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
        $last = Hutang::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->orderByDesc('KodeNota')->first('KodeNota');
        // latest()->first();
        $kode = substr($this->user->Kode,0,4).'/HT/';
        date_default_timezone_set('Asia/Makassar');
        $periode = date('ym');
        
        if (!$last) {
            $kode = $kode.$periode.'/000001';
        } elseif (substr($last->KodeNota,8,4) === $periode) {
            $nomer = substr($last->KodeNota,13);
            $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);
        } else {
            $kode = $kode.$periode.'/000001';
        }
        
        $data = new Hutang;
        $data->KodeNota = $kode;
        $data->Tanggal = $request->Tanggal;
        $data->Supplier = $request->Supplier;
        $data->MataUang = $request->MataUang;
        $data->Kurs = $request->Kurs;
        $data->Total = $request->Total;
        // $data->status = $request->status;
        // $data->keterangan_status = $request->keterangan_status;
        $data->Keterangan = $request->Keterangan;
        $data->Referensi = $request->Referensi;
        // $data->jumlah_cetak = $request->jumlah_cetak;
        $data->DiUbahOleh = $this->user->Kode;
        
        if ($this->user->hutang()->save($data)) {
            $pembelian = collect($request->pembelian)->map(function ($item){
                $item['Faktur'] = $item['KodeNota'];
                unset($item['KodeNota']);
                return $item;
            });
            $data->itemspembelian()->createMany($pembelian);
            $pembayaran = collect($request->pembayaran)->map(function ($item,$key){
                $item['NoUrut'] = $key+1;
                $item['Perkiraan'] = $item['Kode'];
                return $item;
            });
            $data->itemspembayaran()->createMany($pembayaran);
            // for ($i=0; $i < count($request->itemspembayaran); $i++) { 
            //     $urutan = $i;
            //     $item = new ItemsHutangPembayaran;
            //     $item->perkiraan = $request->itemspembayaran[$i]['perkiraan']['Kode'];
            //     $item->keterangan = $request->itemspembayaran[$i]['keterangan'];                 
            //     $item->jumlah = $request->itemspembayaran[$i]['jumlah'];
            //     $data->itemspembayaran()->save($item);
            // }
            // for ($i=0; $i < count($request->itemspembelian); $i++) { 
            //     $urutan = $i;
            //     $item = new ItemsHutangPembelian;
            //     $item->faktur = $request->itemspembelian[$i]['faktur']['kode_nota'];
            //     $item->no_polisi = $request->itemspembelian[$i]['no_polisi'];   
            //     $item->keterangan = $request->itemspembelian[$i]['keterangan'];        
            //     $item->total_bayar = $request->itemspembelian[$i]['total_bayar'];      
            //     $item->sisa_bayar = $request->itemspembelian[$i]['sisa_bayar'];  
            //     $item->jumlah = $request->itemspembelian[$i]['jumlah'];
            //     $data->itemspembelian()->save($item);
            // }
            return response()->json([
                "status" => true,
                "hutang" => $data,
                "pembelian" => count($request->pembelian),
                "pembayaran" => count($request->pembayaran),
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
     * @param  \App\Models\Hutang  $hutang
     * @return \Illuminate\Http\Response
     */
    public function show(Hutang $db, $hutang)
    {
        // $id = 1;
        $data = $db::with('supplier:id,Kode,Nama')
            ->with(['itemspembayaran' => function ($q){return $q->with('perkiraan:id,Kode,Nama');}])
            ->with(['itemspembelian' => function ($q){return $q->with('faktur:id,KodeNota,TotalBayar,SisaBayar'
                // ,'faktur.wo:KodeNota,NomorPolisi'
            );}])
            ->find($hutang);
        return response()->json([
            'data' => $data,
            'status' => true
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Hutang  $hutang
     * @return \Illuminate\Http\Response
     */
    public function edit(Hutang $hutang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Hutang  $hutang
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Hutang $db, $hutang)
    {
        $data = $db::find($hutang);
        // $data->tanggal = $request->tanggal;
        // $data->supplier = $request->supplier;
        // $data->mata_uang = $request->mata_uang;
        // $data->kurs = $request->kurs;
        // $data->total = $request->total;
        // $data->status = $request->status;
        // $data->keterangan_status = $request->keterangan_status;
        // $data->keterangan = $request->keterangan;
        // $data->referensi = $request->referensi;
        // $data->jumlah_cetak = $request->jumlah_cetak;
        // $data->updated_by = $this->user->id;

        $data->Tanggal = $request->Tanggal;
        $data->MataUang = $request->MataUang;
        $data->Total = $request->Total;
        $data->Keterangan = $request->Keterangan;
        $data->Referensi = $request->Referensi;
        $data->DiUbahOleh = $this->user->Kode;
        
        // $last = ItemsHutangPembayaran::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        $pembayaran_new = collect($request->newItemsPembayaran)->map(function ($item,$key) {
            // $item['NoUrut'] = $key+1;
            // $item['NoUrut'] = $last['NoUrut'] != null ? $key+1+$last['NoUrut'] : $key+1;
            $item['Perkiraan'] = $item['Kode'];
            return $item;
        });
        // return $request->all();
        if ($data->save()) {
            for ($i=0; $i < count($request->pembayaran); $i++) { 
                // if (isset($request->itemspembayaran[$i]['id']) == false) {
                //     $last = ItemsHutangPembayaran::where('kodenota',$request->kode_nota)->latest('urutan')->first('urutan');
                //     $urutan = $last['urutan']+1;
                //     $item = new ItemsHutangPembayaran;
                //     $item->urutan = $urutan;
                //     $item->perkiraan = $request->itemspembayaran[$i]['perkiraan']['Kode'];
                //     $item->keterangan = $request->itemspembayaran[$i]['keterangan'];                 
                //     $item->jumlah = $request->itemspembayaran[$i]['jumlah'];
                //     $data->itemspembayaran()->save($item);
                // } else {
                //     $item = ItemsHutangPembayaran::find($request->itemspembayaran[$i]['id']);
                //     $item->perkiraan = $request->itemspembayaran[$i]['perkiraan']['Kode'];
                //     $item->keterangan = $request->itemspembayaran[$i]['keterangan'];                 
                //     $item->jumlah = $request->itemspembayaran[$i]['jumlah'];
                //     $item->save();
                // }
                $oldVal = ItemsHutangPembayaran::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->pembayaran[$i]['NoUrut'])
                ->where('Perkiraan',$request->pembayaran[$i]['Perkiraan'])
                ->get();
                $newVal = [
                    'Keterangan' => $request->pembayaran[$i]['Keterangan'],
                    'Jumlah' => $request->pembayaran[$i]['Jumlah']
                ];
                ItemsHutangPembayaran::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->pembayaran[$i]['NoUrut'])
                ->where('Perkiraan',$request->pembayaran[$i]['Perkiraan'])
                ->update([
                    'Keterangan' => $request->pembayaran[$i]['Keterangan'],
                    'Jumlah' => $request->pembayaran[$i]['Jumlah']
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsHutangPembayaran",
                    'auditable_id' => $request->pembayaran[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }

            for ($i=0; $i < count($request->pembelian); $i++) { 
                $oldVal = ItemsHutangPembelian::where('KodeNota',$request->KodeNota)
                ->where('Faktur',$request->pembelian[$i]['KodeNota'])
                ->get();
                $newVal = [
                    'Keterangan' => $request->pembelian[$i]['Keterangan'],
                    'Jumlah' => $request->pembelian[$i]['Jumlah']
                ];
                ItemsHutangPembelian::where('KodeNota',$request->KodeNota)
                ->where('Faktur',$request->pembelian[$i]['KodeNota'])
                ->update([
                    'Keterangan' => $request->pembelian[$i]['Keterangan'],
                    'Jumlah' => $request->pembelian[$i]['Jumlah']
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsHutangPembelian",
                    'auditable_id' => 0,
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }

            $data->itemspembayaran()->createMany($pembayaran_new);

            if (!empty($request->hapus_items)) {
                // $hps = ItemsHutangPembayaran::destroy($request->hapus_items);

                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = ItemsHutangPembayaran::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->where('Perkiraan',$request->hapus_items[$i]['Perkiraan'])
                    ->delete();

                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsHutangPembayaran",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
                return response()->json([
                    "status"=>true,
                    "hutang"=>$data,
                    "itemspembayaran"=>$request->pembayaran,
                    'new_pembayaran'=>$pembayaran_new,
                    'itemspembelian'=>$request->pembelian,
                    "delet item"=>$hps
                ],200);
            }
            return response()->json([
                "status"=>true,
                "hutang"=>$data,
                "itemspembayaran"=>$request->pembayaran,
                'itemspembelian'=>$request->pembelian,
                'new_pembayaran'=>$pembayaran_new,
            ],200);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
    }

    // public function batalin(Request $request, Hutang $hutang, $id)
    // {

    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Hutang  $hutang
     * @return \Illuminate\Http\Response
     */
    public function destroy(Hutang $db,$hutang)
    {
        // $data = $db::find($hutang);
        // $item = $data->itemspembayaran()->get('id');
        // $x = [];
        // foreach ($item as $key => $value) {
        //     array_push($x,$value['id']);
        // }
        // if ($data->delete()) {
        //     ItemsHutangPembayaran::destroy($x);
        //     return response()->json([
        //         "status" => true,
        //         "message" => "deleted"
        //     ],200);
        // } 
        // else {
        //     return response()->json([
        //         "status" => false,
        //         "message" => "gagak delete"
        //     ],500);
        // }
        
    }

    public function batalin(Request $request, Hutang $hutang, $id)
    {
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->Kode . " - " . $this->user->Nama . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $data = $hutang::find($id);
        $data->Status = 'BATAL';
        $data->KeteranganStatus = $keterangan;
        $data->DiUbahOleh = $this->user->Kode;
        $data->save();
        ItemsHutangPembelian::where('KodeNota',$data->KodeNota)->update(['Jumlah' => 0]);
        ItemsHutangPembayaran::where('KodeNota',$data->KodeNota)->update(['Jumlah' => 0]);
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    public function report($id)
    {
        $data = Hutang::with('supplier:Kode,Nama','itemspembelian','itemspembelian.faktur:KodeNota,NoWorkOrder','itemspembelian.faktur.wo:KodeNota,NomorPolisi')->select('KodeNota','Tanggal','Total','Keterangan','Supplier')->find($id);
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

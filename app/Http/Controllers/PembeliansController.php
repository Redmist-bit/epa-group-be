<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Barangs;
use App\Models\MataUangs;
use App\Models\Pembelians;
use App\Models\PurchaseOrders;
use App\Models\ItemsPembelians;
use App\Models\ItemsPembeliansJasa;
use App\Models\ItemsPurchaseOrders;
use App\Models\ItemsHutangPembelian;
use App\Http\Controllers\StokController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembeliansController extends Controller
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
        $data = Pembelians::
        where('KodeNota','Like','%FB%')
        ->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->with('supplier:Kode,Nama','wo:KodeNota,NomorPolisi','po:KodeNota,Referensi')
        // ->whereBetween('DiBuatTgl',[$from,$to])
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->get();
        return response()->json([
            'data' => $data,
        ],200);
    }

    public function indexJasa($from,$to)
    {
        $data = Pembelians::where('KodeNota','Like','%FC%')->with('supplier:Kode,Nama','wo:KodeNota,NomorPolisi,Lokasi')
        // ->whereBetween('DiBuatTgl',[$from,$to])
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->get();
        return response()->json([
            'data' => $data,
        ],200);
    }

    public function dataPo(Request $request,PurchaseOrders $PO)
    {
        $kodeSupplier = $request->query('supplier');
        // return 'succes dapat po dengan no supplier ....'.$request->Supplier;
        $data = $PO::with('wo:KodeNota,Coding,Status,KeteranganWIP,IsClose')->where('Supplier',$kodeSupplier)->where('KodeNota','Like','%PO%')->whereNull('Status')
        ->whereExists(function ($query) {
            $query->select('KodeNota')
                    ->from('DetailPO')
                    ->whereColumn('MasterPO.KodeNota', 'DetailPO.KodeNota')->whereColumn('DetailPO.Terpenuhi','<', 'DetailPO.Jumlah')->limit(1);
        })
        ->orWhereExists(function ($query) {
            $query->select('KodeNota')
                    ->from('DetailPOPerkiraan')
                    ->whereColumn('MasterPO.KodeNota', 'DetailPOPerkiraan.KodeNota')->whereColumn('DetailPOPerkiraan.Terpenuhi','<', 'DetailPOPerkiraan.Jumlah')->limit(1);
        })
        ->orderByDesc('KodeNota')->get(['id','KodeNota','Tanggal','TanggalKirim','NomorWO','Status','Keterangan','Referensi','Supplier','PPnPersen']);
        return response()->json([
            'data' => $data,
        ],200);
    }

    public function dataPoJasa(Request $request,PurchaseOrders $PO)
    {
        $kodeSupplier = $request->query('supplier');
        // return 'succes dapat po dengan no supplier ....'.$request->Supplier;
        $data = $PO::with('wo:KodeNota,Coding,Status,KeteranganWIP,IsClose')->where('Supplier',$kodeSupplier)->where('KodeNota','Like','%PJ%')->whereNull('Status')->whereExists(function ($query) {
            $query->select('KodeNota')
                    ->from('DetailPOPerkiraan')
                    ->whereColumn('MasterPO.KodeNota', 'DetailPOPerkiraan.KodeNota')->whereColumn('DetailPOPerkiraan.Terpenuhi','<', 'DetailPOPerkiraan.Jumlah')->limit(1);
            })
        ->orderByDesc('id')->get(['id','KodeNota','Tanggal','TanggalKirim','NomorWO','Status','Keterangan','Referensi','PPnPersen']);
        // CodingTerlarang:['A3','B3','C3','E3','D3','T3','Z'],
        return response()->json([
            'data' => $data,
        ],200);
    }

    public function itemsPo(PurchaseOrders $PO,$id)
    {
        $data = $PO::find($id)->items->load('barang:id,Kode,Nama,Merk,PartNumber1,Kendaraan','satuan:Barang,Rasio,Nama');
        $jasa = $PO::find($id)->itemsJasa->load('perkiraan:Kode,Nama');
        return response()->json([
            'data' => $data,
            'jasa' => $jasa
        ],200);
        // return response()->json([
        //     "barang" => $data
        // ],200);
    }

    public function itemsPoJasa(PurchaseOrders $PO,$id)
    {
        $data = $PO::find($id)->itemsJasa->load('pekerjaan:Kode,Nama','perkiraan:Kode,Nama');
        // $item = $data->load('barang:id,Kode,Nama,Merk,PartNumber1,Kendaraan');
        return response()->json([
            'data' => $data,
        ],200);
        // return response()->json([
        //     "barang" => $data
        // ],200);
    }

    public function supplier()
    {
        $data = collect();
        DB::table('Supplier')->where('Kode','like',substr($this->user->Kode,0,2).'%')->
        select('Kode','Nama','BillFrom','SellFrom','BadanHukum','Kota','Alamat','Telp','ContactPerson','Aktif')->orderBy('id')->chunk(500, function($datas) use ($data) {
            foreach ($datas as $d) {
                $data->push($d);
            }
        });
        return response()->json([
            "data" => $data
        ]);
    }

    public function uang()
    {
        $data = MataUangs::where('Aktif',true)->get(['Kode','Nama','Aktif']);
        return response()->json(['mataUang' => $data]);
    }

    public function gudang()
    {
        $data = DB::table('Gudang')->where('Kode','like',substr($this->user->Kode,0,2).'%')->select('id','Kode','Nama')->get();
        return response()->json([
            "data" => $data
        ]);
    }

    public function barang(Request $request)
    {
        $skip = $request->query('skip');
        $take = $request->query('take');
        $search = $request->query('search');
        
        if ($search != null) {
            $allColumns = ['Kode','Barang.Nama','PartNumber1'];
            // $data = Barangs::with('satuan:Barang,Rasio,Nama','satuan.hrgjual:Barang,Rasio,Harga')
            // $data = DB::table('Barang')->join('Satuan','Barang.Kode','=','Satuan.Barang')
            $data = DB::table('HrgJual')->rightJoin('Satuan',function($join){
                $join->on('HrgJual.Barang','=','Satuan.Barang')->on('HrgJual.Rasio','=','Satuan.Rasio');
            })->join('Barang','Satuan.Barang','=','Barang.Kode','full')
            // ->select('Kode','Barang.Nama','PartNumber1','Kendaraan','Merk','Rasio','Satuan.Nama as Satuan');
            ->select('Barang.Kode','Barang.Nama','Barang.PartNumber1','Barang.Kendaraan','Barang.Merk','Satuan.Rasio','Satuan.Nama as Satuan','HrgJual.Harga');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->count();
            $data = $data->offset($skip)->limit($take)->get();
        } else {
            $count = Barangs::count();
            // $hrg = DB::table('Barang')->join('HrgJual','Barang.Kode',)
            $data = DB::table('HrgJual')->rightJoin('Satuan',function($join){
                $join->on('HrgJual.Barang','=','Satuan.Barang')->on('HrgJual.Rasio','=','Satuan.Rasio');
            })->join('Barang','Satuan.Barang','=','Barang.Kode','full')
            ->select('Barang.Kode','Barang.Nama','Barang.PartNumber1','Barang.Kendaraan','Barang.Merk','Satuan.Rasio','Satuan.Nama as Satuan','HrgJual.Harga')
            ->orderBy('Barang.Kode')->skip($skip)->take($take)->get();
            // $data = Barangs::with('satuan:Barang,Rasio,Nama')
            // ->skip($skip)->take($take)->get(['Kode','Nama','PartNumber1','Kendaraan','Merk']);
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
    public function updateGrid(Request $request,$id)
    {
        $data = Pembelians::find($id);
        $data->PPnFaktur = $request->PPnFaktur;
        $data->CekFisikInv = $request->CekFisikInv;
        $data->NoFakturPajak = ($request->NoFakturPajak == "") ? '' : $request->NoFakturPajak;
        $data->FPComplete = $request->FPComplete;
        $data->NoInvSupplier = ($request->NoInvSupplier == "") ? '' : $request->NoInvSupplier;
        $data->DiUbahOleh = $this->user->Kode;
        $data->save();
        return response()->json([
            "status" => 'success',
            "message" => 'updated'
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $last = PurchaseOrders::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','like','%PO%')->orderByDesc('KodeNota')->first('KodeNota');
        // $kode = substr($this->user->Kode,0,4).'/PO/';
        $last = Pembelians::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','Like','%FB%')->orderByDesc('KodeNota')->first('KodeNota');
        $kode = substr($this->user->Kode,0,4).'/FB/';
        date_default_timezone_set('Asia/Makassar');
        $periode = date('ym');
        // return substr($last->kode_nota,0,13).str_pad(substr($last->kode_nota,13)+1,6,'0',STR_PAD_LEFT);
        if (!$last) {
            $kode = $kode.$periode.'/000001';
        } elseif (substr($last->KodeNota,8,4) === $periode) {
            $nomer = substr($last->KodeNota,13);
            $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);;
        } else {
            $kode = $kode.$periode.'/000001';
        }
        // return $kode;

        $pembelian = new Pembelians;
        $pembelian->KodeNota = $kode;
        $pembelian->Tanggal = $request->Tanggal;
        $pembelian->Supplier = $request->Supplier;
        $pembelian->TanggalPengiriman = $request->TanggalPengiriman;
        $pembelian->Referensi = $request->Referensi;
        // $pembelian->total = $request->Total;
        // $pembelian->diskon = $request->Diskon;
        // $pembelian->ppn = $request->ppn;
        // $pembelian->total_bayar = $request->total_bayar;
        // $pembelian->ppn_faktur = $request->ppn_faktur;
        // $pembelian->cek_fisik_inv = $request->cek_fisik_inv;
        $pembelian->NoPO = $request->NoPO;
        // $pembelian->NoWorkOrder = $request->NoWorkOrder;
        // $pembelian->no_polisi = $request->no_polisi;
        // $pembelian->status = $request->status;
        // $pembelian->keterangan_status = $request->keterangan_status;
        
        $pembelian->BillFrom = $request->BillFrom;
        $pembelian->SellFrom = $request->SellFrom;
        $pembelian->PaymentTerm = $request->PaymentTerm;
        $pembelian->MataUang = $request->MataUang;
        $pembelian->Kurs = $request->Kurs;
        $pembelian->Gudang = substr($this->user->Kode,0,4).'/0001';
        
        $pembelian->Keterangan = $request->Keterangan;
        // $pembelian->dpp = $request->dpp;
        // $pembelian->ppn_persen = $request->ppn_persen;
        $pembelian->DiUbahOleh = $this->user->Kode;
        $pembelian->PPnPersenManual = $request->PPnPersen;

        if ($this->user->pembelians()->save($pembelian)){
            $barang = collect($request->items)->map(function ($item,$key) {
                $item['NoUrut'] = $key+1;
                $item['Diskon1'] = $item['Diskon'];
                $item['NoPO'] = isset($item['KodeNota'],$item) ? $item['KodeNota'] : NULL;
                return $item;
            });
            $pembelian->items()->createMany($barang);
            $jasa = collect($request->itemsJasa)->map(function ($item,$key) {
                // $item['NoUrut'] = $key+1;
                $item['Diskon1'] = $item['Diskon'];
                $item['NoPO'] = isset($item['KodeNota'],$item) ? $item['KodeNota'] : NULL;
                return $item;
            });
            $pembelian->itemsJasa()->createMany($jasa);
            return response()->json([
                "status" => true,
                "pembelian" => $pembelian,
                "items" => $barang,
                "stok" => $jasa
            ],200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "gagal save"
            ], 500);
        };
    }

    public function storeJasa(Request $request)
    {
        $last = Pembelians::where('KodeNota','Like','%FC%')->latest()->first();
        $kode = '0101/FC/';
        date_default_timezone_set('Asia/Makassar');
        $periode = date('ym');
        // return substr($last->kode_nota,0,13).str_pad(substr($last->kode_nota,13)+1,6,'0',STR_PAD_LEFT);
        if (!$last) {
            $kode = $kode.$periode.'/000001';
        } elseif (substr($last->KodeNota,8,4) === $periode) {
            $nomer = substr($last->KodeNota,13);
            $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);;
        } else {
            $kode = $kode.$periode.'/000001';
        }
        // return $kode;

        $pembelian = new Pembelians;
        $pembelian->KodeNota = $kode;
        $pembelian->Tanggal = $request->Tanggal;
        $pembelian->Supplier = $request->Supplier;
        $pembelian->TanggalPengiriman = $request->TanggalPengiriman;
        $pembelian->Referensi = $request->Referensi;
        $pembelian->NoPO = $request->NoPO;
        $pembelian->NoWorkOrder = $request->NoWorkOrder;
        $pembelian->BillFrom = $request->BillFrom;
        $pembelian->SellFrom = $request->SellFrom;
        $pembelian->PaymentTerm = $request->PaymentTerm;
        $pembelian->MataUang = $request->MataUang;
        $pembelian->Kurs = $request->Kurs;
        $pembelian->Gudang = '0101/0001';
        $pembelian->Keterangan = $request->Keterangan;
        $pembelian->DiUbahOleh = $this->user->Kode;
        $pembelian->PPnPersenManual = $request->PPnPersen;

        if ($this->user->pembelians()->save($pembelian)){
            $jasa = collect($request->items)->map(function ($item,$key) {
                // $item['NoUrut'] = $key+1;
                $item['Diskon1'] = $item['Diskon'];
                $item['NoPO'] = isset($item['KodeNota'],$item) ? $item['KodeNota'] : NULL;
                return $item;
            });
            $pembelian->itemsJasa()->createMany($jasa);
            return response()->json([
                "status" => true,
                "pembelian" => $pembelian,
                "items" => $jasa,
                // "stok" => $update_stok
            ],200);
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
     * @param  \App\Models\Pembelians  $pembelians
     * @return \Illuminate\Http\Response
     */
    public function show(Pembelians $pembelians, $id)
    {
        $pembelians = Pembelians::with('supplier:id,Kode,Nama')->with('uang:Kode,Nama')
        ->with('BillFrom:id,Kode,Nama')->with('SellFrom:id,Kode,Nama')
        ->with(['items' => function ($q){
            return $q->with('barang:id,Kode,Nama,Merk,PartNumber1,Kendaraan','gudang:Kode,Nama','satuan:Barang,Rasio,Nama','detailPo:KodeNota,Barang,Jumlah,Terpenuhi');
        }])
        ->with(['itemsJasa' => function ($q){
            return $q->with('perkiraan:Kode,Nama','detailPo:KodeNota,Jumlah,Terpenuhi,NoUrut,Perkiraan,Keterangan');
        }])
        ->find($id);
        return response()->json([
            'data' => $pembelians,
            'status' => true
        ],200);
    }

    public function showJasa(Pembelians $pembelians, $id)
    {
        $pembelians = Pembelians::with('supplier:id,Kode,Nama')->with('uang:Kode,Nama')
        // ->with(['po' => function ($q) {
        //     return $q->with('itemsJasa:KodeNota,JenisPekerjaan,Jumlah,Terpenuhi')->select('KodeNota');
        // }])
        ->with('BillFrom:id,Kode,Nama')->with('SellFrom:id,Kode,Nama')
        ->with(['itemsJasa' => function ($q){
            return $q->with('pekerjaan:Kode,Nama','perkiraan:Kode,Nama','detailPo:KodeNota,JenisPekerjaan,Jumlah,Terpenuhi,NoUrut,Perkiraan,Keterangan');
        }])->find($id);
        return response()->json([
            'data' => $pembelians,
            'status' => true
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Pembelians  $pembelians
     * @return \Illuminate\Http\Response
     */
    public function edit(Pembelians $pembelians)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Pembelians  $pembelians
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Pembelians $pembelians, $id)
    {
        // $this->validate($request, [
        //     "KodeNota" => "required",
            
        // ]);
        $pembelian = Pembelians::find($id);
        // $pembelian->kode_nota = $request->kode_nota;
        $pembelian->Tanggal = $request->Tanggal;
        // $pembelian->supplier = $request->supplier;
        $pembelian->TanggalPengiriman = $request->TanggalPengiriman;
        $pembelian->Referensi = $request->Referensi;
        // $pembelian->total = $request->Total;
        // $pembelian->diskon = $request->Diskon;
        // $pembelian->ppn = $request->ppn;
        // $pembelian->total_bayar = $request->total_bayar;
        // $pembelian->ppn_faktur = $request->ppn_faktur;
        // $pembelian->cek_fisik_inv = $request->cek_fisik_inv;
        // $pembelian->no_po = $request->no_po;
        // $pembelian->no_wo = $request->no_wo;
        // $pembelian->no_polisi = $request->no_polisi;
        // $pembelian->status = $request->status;
        // $pembelian->keterangan_status = $request->keterangan_status;

        // $pembelian->BillFrom = $request->BillFrom;
        // $pembelian->SellFrom = $request->SellFrom;
        $pembelian->PaymentTerm = $request->PaymentTerm;
        // $pembelian->mata_uang = $request->mata_uang;
        $pembelian->Kurs = $request->Kurs;

        $pembelian->Keterangan = $request->Keterangan;
        // $pembelian->dpp = $request->dpp;
        // $pembelian->ppn_persen = $request->ppn_persen;
        $pembelian->DiUbahOleh = $this->user->Kode;
        $pembelian->PPnPersenManual = $request->PPnPersen;

        $last = ItemsPembelians::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        $barang_new = collect($request->new_items)->map(function ($item,$key) use($last){
            $item['NoUrut'] = $key+1+$last['NoUrut'];
            $item['Diskon1'] = $item['Diskon'];
            $item['NoPO'] = isset($item['KodeNota'],$item) ? $item['KodeNota'] : NULL;
            return $item;
        });
        if($pembelian->save()){
            for ($i=0; $i < count($request->items); $i++) { 
                $oldVal = ItemsPembelians::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('Barang',$request->items[$i]['Barang'])
                ->get();
                $newVal = [
                    'Gudang' => $request->items[$i]['Gudang'], 'Keterangan' => $request->items[$i]['Keterangan'], 
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                ];
                ItemsPembelians::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('Barang',$request->items[$i]['Barang'])
                ->update([
                    'Gudang' => $request->items[$i]['Gudang'], 'Keterangan' => $request->items[$i]['Keterangan'], 
                    'Jumlah' => $request->items[$i]['Jumlah'],
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPembelians",
                    'auditable_id' => $request->items[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }
            // $pembelian->items()->createMany($barang_new);
            for ($i=0; $i < count($request->itemsJasa); $i++) { 
                $oldVal = ItemsPembeliansJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsJasa[$i]['NoUrut'])
                ->where('Perkiraan',$request->itemsJasa[$i]['Perkiraan'])
                ->get();
                $newVal = [
                    'Perkiraan' => $request->itemsJasa[$i]['Perkiraan'],
                    'Keterangan' => $request->itemsJasa[$i]['Keterangan'], 'Jumlah' => $request->itemsJasa[$i]['Jumlah'],
                    'Harga' => $request->itemsJasa[$i]['Harga'], 'Diskon1' => $request->itemsJasa[$i]['Diskon'],
                ];
                ItemsPembeliansJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsJasa[$i]['NoUrut'])
                ->where('Perkiraan',$request->itemsJasa[$i]['Perkiraan'])
                ->update([
                    'Perkiraan' => $request->itemsJasa[$i]['Perkiraan'],
                    'Keterangan' => $request->itemsJasa[$i]['Keterangan'], 
                    'Jumlah' => $request->itemsJasa[$i]['Jumlah'],
                    'Harga' => $request->itemsJasa[$i]['Harga'], 'Diskon1' => $request->itemsJasa[$i]['Diskon'],
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPembeliansJasa",
                    'auditable_id' => $request->itemsJasa[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }
            if (!empty($request->hapus_items)) {
                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = ItemsPembelians::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->where('Barang',$request->hapus_items[$i]['Barang'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsPembelians",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
            }
            if (!empty($request->hapus_itemsJasa)) {
                for ($i=0; $i < count($request->hapus_itemsJasa); $i++) { 
                    $hps = ItemsPembeliansJasa::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_itemsJasa[$i]['NoUrut'])
                    // ->where('Perkiraan',$request->hapus_itemsJasa[$i]['Perkiraan'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsPembeliansJasa",
                        'auditable_id' => $request->hapus_itemsJasa[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_itemsJasa[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
            }
            return response()->json([
                "status"=>true,
                "pembelians"=>$pembelian,
                "items"=>$request->items,
            ],200);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
    }

    public function updateJasa(Request $request, Pembelians $pembelians, $id)
    {
        $pembelian = Pembelians::find($id);
        $pembelian->Tanggal = $request->Tanggal;
        $pembelian->TanggalPengiriman = $request->TanggalPengiriman;
        $pembelian->Referensi = $request->Referensi;
        $pembelian->PaymentTerm = $request->PaymentTerm;
        $pembelian->Kurs = $request->Kurs;

        $pembelian->Keterangan = $request->Keterangan;
        $pembelian->DiUbahOleh = $this->user->Kode;
        $pembelian->PPnPersenManual = $request->PPnPersen;

        $last = ItemsPembeliansJasa::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        $jasa_new = collect($request->new_items)->map(function ($item,$key) use($last){
            $item['NoUrut'] = $key+1+$last['NoUrut'];
            $item['Diskon1'] = $item['Diskon'];
            $item['NoPO'] = isset($item['KodeNota'],$item) ? $item['KodeNota'] : NULL;
            return $item;
        });
        if($pembelian->save()){
            for ($i=0; $i < count($request->items); $i++) { 
                $oldVal = ItemsPembeliansJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('JenisPekerjaan',$request->items[$i]['JenisPekerjaan'])
                ->get();
                $newVal = [
                    'Perkiraan' => $request->items[$i]['Perkiraan'],
                    'Keterangan' => $request->items[$i]['Keterangan'], 'Jumlah' => $request->items[$i]['Jumlah'],
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                ];
                ItemsPembeliansJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('JenisPekerjaan',$request->items[$i]['JenisPekerjaan'])
                ->update([
                    'Perkiraan' => $request->items[$i]['Perkiraan'],
                    'Keterangan' => $request->items[$i]['Keterangan'], 
                    'Jumlah' => $request->items[$i]['Jumlah'],
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPembeliansJasa",
                    'auditable_id' => $request->items[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }
            $pembelian->itemsJasa()->createMany($jasa_new);
            if (!empty($request->hapus_items)) {
                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = ItemsPembeliansJasa::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->where('JenisPekerjaan',$request->hapus_items[$i]['JenisPekerjaan'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsPembeliansJasa",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
                return response()->json([
                    "status"=>true,
                    "pembelians"=>$pembelian,
                    "items"=>$request->items,
                    'new item'=>$jasa_new,
                    "delet item"=>$request->hapus_items
                ],200);
            }
            return response()->json([
                "status"=>true,
                "pembelians"=>$pembelian,
                "items"=>$request->items,
            ],200);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
    }

    public function batalin(Request $request, Pembelians $pembelians, $id)
    {
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->kode . " - " . $this->user->name . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $pembelian = $pembelians::find($id);
        $pembelian->Status = 'BATAL';
        $pembelian->KeteranganStatus = $keterangan;
        $pembelian->DiUbahOleh = $this->user->Kode;
        ItemsPembeliansJasa::where('KodeNota',$pembelian->KodeNota)->update(['Jumlah' => 0]);
        ItemsPembelians::where('KodeNota',$pembelian->KodeNota)->update(['Jumlah' => 0]);
        // if (str_contains($pembelian->KodeNota,'FC')) {
        //     ItemsPembeliansJasa::where('KodeNota',$pembelian->KodeNota)->update(['Jumlah' => 0]);
        // } else {
        //     ItemsPembelians::where('KodeNota',$pembelian->KodeNota)->update(['Jumlah' => 0]);
        // }
        
        $pembelian->save();
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    public function cekPelunasan($id)
    {
        // $data = Pembelians::find($id);
        $detailHutang = ItemsHutangPembelian::where('Faktur',function ($q) use($id){
            $q->select('KodeNota')
            ->from('MasterBeli')
            ->where('id', $id);
        })->first();
        return response()->json([
            'data' => $detailHutang,
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Pembelians  $pembelians
     * @return \Illuminate\Http\Response
     */
    public function destroy(Pembelians $pembelians, $id)
    {
        // $pembelians = Pembelians::find($id);
        // $item = $pembelians->items()->get('id');
        // $x = [];
        // foreach ($item as $key => $value) {
        //     array_push($x,$value['id']);
        // }
        // if ($pembelians->delete()){
        //     ItemsPembelians::destroy($x);
        //     return response()->json([
        //         "status"=> true,
        //         "pembelians"=> $pembelians
        //     ],200);
        // } else {
        //     return response()->json([
        //         "status"=> false,
        //         "Message"=> "gagal delete"
        //     ],500);
        // }
    }

    public function report($id)
    {
        $data = Pembelians::with('supplier:Kode,Nama,Alamat,Kota',
        'items:KodeNota,Barang,Jumlah,Rasio,Harga,Diskon,SubTotal,SubDiskon,NoPO',
        'items.detailPo:KodeNota,Barang,NoPR',
        'items.barang:Kode,Nama,PartNumber1,PartNumber2',
        'items.satuan:Barang,Rasio,Nama',
        'itemsJasa:KodeNota,Perkiraan,JenisPekerjaan,Keterangan,Jumlah,Harga,Diskon,NoPO,SubTotal,SubDiskon',
        'itemsJasa.perkiraan:Kode,Nama')
        ->select('KodeNota','Supplier','Tanggal','NoWorkOrder','NoPO','PPn','DPP',
        'TotalBayar','Keterangan')->find($id);
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

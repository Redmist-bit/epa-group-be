<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Barangs;
use App\Models\WorkOrder;
use App\Models\MataUangs;
use App\Models\PurchaseOrders;
use App\Models\ItemsPurchaseOrders;
use App\Models\ItemsPurchaseOrdersJasa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseOrdersController extends Controller
{
    protected $user;
    protected $primaryKey = 'po_id';
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    // public function index()
    // {  
    //      $data = collect();
    //     PurchaseOrders::with('items')->orderBy('id')->chunk(50000, function($datas) use ($data) {
    //         foreach ($datas as $d) {
    //             $data->push($d);
    //         }
    //     });
    //     return response()->json($data);
    //     // return PurchaseOrders::all();
    // }
    public function index($from,$to)
    {
        $data = PurchaseOrders::with('supplier:Kode,Nama','wo:KodeNota,NomorRangka,NomorPolisi,Lokasi')
        ->where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->where('KodeNota','Like','%PO%')
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
        $data = PurchaseOrders::with('supplier:Kode,Nama','wo:KodeNota,NomorRangka,NomorPolisi,Lokasi')
        ->where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->where('KodeNota','Like','%PJ%')
        // ->whereBetween('DiBuatTgl',[$from,$to])
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        ->get();
        return response()->json([
            'data' => $data,
        ],200);
    }

    public function rwl(Request $request)
    {
        $nomorWO = $request->query('wo');
        $dataPj = PurchaseOrders::with('itemsJasa:KodeNota,JenisPekerjaan,Jumlah')->where('NomorWO',$nomorWO)->where('KodeNota','Like','%PJ%')->whereNull('Status')->orderByDesc('KodeNota')->first(['KodeNota','Status','NomorWO']);
        $rwl = DB::table('DetailWorkOrderPekerjaan')
        ->join('JenisPekerjaan','JenisPekerjaan.Kode','=','DetailWorkOrderPekerjaan.JenisPekerjaan')
        ->where('KodeNota',$nomorWO)
        ->get(['KodeNota','DetailWorkOrderPekerjaan.JenisPekerjaan','JenisPekerjaan.Nama','Jumlah','Harga','Diskon1','Keterangan','NoUrut']);
        return response()->json([
            'data' => $rwl,
            'dataPj' => $dataPj
        ],200);
    }
    
    public function rpl(Request $request)
    {
        $nomorWO = $request->query('wo');
        $noPartOrder = $request->query('ipo');
        if ($noPartOrder != null) {
            # code...
            $rpl = DB::table('DetailWorkOrder')
            ->join('Barang','Barang.Kode','=','DetailWorkOrder.Barang')
            ->join('Satuan', function ($join){
                $join->on('Satuan.Barang','=','DetailWorkOrder.Barang')->on('Satuan.Rasio','=','DetailWorkOrder.Rasio');
            })
            ->where('KodeNota',$nomorWO)->where('NoPartOrder',$noPartOrder)
            ->get(['KodeNota','NoPartOrder','DetailWorkOrder.Barang','TanggalKirim','DetailWorkOrder.Gudang','Jumlah','DetailWorkOrder.Rasio','Harga','Keterangan','Diskon1',
            'Barang.Nama','Barang.PartNumber1','Barang.Merk','Barang.Kendaraan','Satuan.Nama as Satuan','TerpenuhiPO','Terpenuhi','Status']);
        } else {
            $rpl = DB::table('DetailWorkOrder')->select('NoPartOrder')->where('KodeNota',$nomorWO)->get();
        }
        // $data = DB::table('DetailWorkOrder')->select('KodeNota','NoPartOrder')->where('KodeNota',$nomorWO)->orderByDesc('NoPartOrder')->take(1)->first();
        return response()->json([
            'data' => $rpl
        ]);
    }
    public function wo(Request $request)
    {
        $skip = $request->query('skip');
        $take = $request->query('take');
        $search = $request->query('search');
        if ($search != null) {
            $allColumns = ['KodeNota','Pelanggan','NomorRangka','NomorMesin','NomorPolisi','Pemilik','Pelanggan.Nama','Pemilik.Nama'];
            $data = WorkOrder::with('uang:Kode,Nama')->join('Pelanggan','Pelanggan.Kode','=','MasterWorkOrder.Pelanggan')
            // ->join('Pelanggan as Pemilik','Pemilik.Kode','=','MasterWorkOrder.Pemilik')
            ->select('KodeNota','NomorRangka','NomorMesin','NomorPolisi','Pelanggan','Pemilik','Pelanggan.Nama as NamaPelanggan','Odometer',
            'PaymentTerm','Kurs','MataUang','ReserveOutcome','ReserveOutcomeJasa','AvailableBudget','AvailableBudgetJasa','Coding','KeteranganWIP');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $data = $data->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->whereNull('Status')->where('IsClose',0);
            $count = $data->count();
            $data = $data->offset($skip)->limit($take)->orderByDesc('Tanggal')->get();
        } else {
            $count = WorkOrder::where('KodeNota','like',substr($this->user->Kode,0,2).'%')->whereNull('Status')->where('IsClose',0)->count();
            $data = WorkOrder::where('KodeNota','like',substr($this->user->Kode,0,2).'%')->with('uang:Kode,Nama')->join('Pelanggan','Pelanggan.Kode','=','MasterWorkOrder.Pelanggan')
            // ->join('Pelanggan as Pemilik','Pemilik.Kode','=','MasterWorkOrder.Pemilik')
            ->whereNull ('Status')->where('IsClose',0)
            ->select('KodeNota','NomorRangka','NomorMesin','NomorPolisi','Pelanggan','Pemilik','Pelanggan.Nama as NamaPelanggan','Odometer',
            'PaymentTerm','Kurs','MataUang','ReserveOutcome','ReserveOutcomeJasa','AvailableBudget','AvailableBudgetJasa','Coding','KeteranganWIP')
            ->offset($skip)->limit($take)->orderByDesc('Tanggal')
            ->get();
        }
        return response()->json([
            'result' => $data,
            'count' => $count
        ]);
    }

    public function uang()
    {
        $data = MataUangs::where('Aktif',true)->get(['Kode','Nama','Aktif']);
        return response()->json(['mataUang' => $data]);
    }

    public function supplier()
    {
        $data = collect();
        DB::table('Supplier')
        ->where('Kode','like',substr($this->user->Kode,0,2).'%')
        ->select('Kode','Nama','BillFrom','SellFrom','BadanHukum','Kota','Alamat','Telp','ContactPerson','Aktif')->orderBy('id')->chunk(500, function($datas) use ($data) {
            foreach ($datas as $d) {
                $data->push($d);
            }
        });
        return response()->json([
            "data" => $data
        ]);
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
            $count = $data->where('Barang.Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Barang.Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } else {
            $count = Barangs::where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            // $hrg = DB::table('Barang')->join('HrgJual','Barang.Kode',)
            $data = DB::table('HrgJual')->rightJoin('Satuan',function($join){
                $join->on('HrgJual.Barang','=','Satuan.Barang')->on('HrgJual.Rasio','=','Satuan.Rasio');
            })->join('Barang','Satuan.Barang','=','Barang.Kode','full')
            ->where('Barang.Kode','like',substr($this->user->Kode,0,2).'%')
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

    public function aplly(Request $request, $id)
    {
        $purchaseOrder =  PurchaseOrders::find($id);
        $purchaseOrder->Apply =  $request->apply;

        if($this->user->PurchaseOrders()->save($purchaseOrder)){
            return response()->json([
                "status"=>true,
                "penjualan"=>$purchaseOrder
            ], 200);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
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
        $last = PurchaseOrders::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','like','%PO%')->orderByDesc('KodeNota')->first('KodeNota');
        $kode = substr($this->user->Kode,0,4).'/PO/';
        date_default_timezone_set('Asia/Makassar');
        $periode = date('ym');
        // return substr($last->kode_nota,0,13).str_pad(substr($last->kode_nota,13)+1,6,'0',STR_PAD_LEFT);
        if (!$last) {
            $kode = $kode.$periode.'/000001';
            return $kode;
        } elseif (substr($last->KodeNota,8,4) === $periode) {
            $nomer = substr($last->KodeNota,13);
            $kode = substr($last->KodeNota,0,13).str_pad($nomer+1, 6, '0', STR_PAD_LEFT);;
        } else {
            $kode = $kode.$periode.'/000001';
        }
        // return $kode;
        // return response()->json([
        //     'data' => $request->All(),
        //     'kode' => $kode
        // ]);
        $purchaseOrder = new PurchaseOrders;
        $purchaseOrder->KodeNota = $kode;
        $purchaseOrder->Tanggal = $request->Tanggal;
        $purchaseOrder->Supplier = $request->Supplier;
        $purchaseOrder->BillFrom = $request->BillFrom;
        $purchaseOrder->SellFrom = $request->SellFrom;
        $purchaseOrder->PaymentTerm = $request->PaymentTerm;
        $purchaseOrder->MataUang = $request->MataUang;
        $purchaseOrder->Kurs = $request->Kurs;
        $purchaseOrder->TanggalKirim = $request->TanggalKirim;
        $purchaseOrder->Referensi = $request->Referensi;
        $purchaseOrder->NomorWO = $request->NomorWO;
        // $purchaseOrder->NomorRangka = $request->NomorRangka;
        $purchaseOrder->PPnPersenManual = $request->PPnPersen;
        $purchaseOrder->Keterangan = $request->Keterangan;
        $purchaseOrder->Gudang = substr($this->user->Kode,0,4).'/0001';
        $purchaseOrder->DiUbahOleh = $this->user->Kode;

        // $purchaseOrder->KodeNota = $kode;
        // $purchaseOrder->Tanggal = $request->tanggal;
        // $purchaseOrder->Supplier = $request->supplier;
        // $purchaseOrder->tanggal_pengiriman = $request->tanggal_pengiriman;
        // $purchaseOrder->status = $request->status;
        // $purchaseOrder->total = $request->total;
        // $purchaseOrder->diskon = $request->diskon;
        // $purchaseOrder->dpp = $request->dpp;
        // $purchaseOrder->ppn = $request->ppn;
        // $purchaseOrder->ppn_persen = $request->ppn_persen;
        // $purchaseOrder->total_bayar = $request->total_bayar;
        // $purchaseOrder->referensi = $request->referensi;
        // $purchaseOrder->no_rangka = $request->no_rangka;
        // $purchaseOrder->no_polisi = $request->no_polisi;
        // $purchaseOrder->no_wo = $request->no_wo;
        // $purchaseOrder->BillFrom = $request->bill_from;
        // $purchaseOrder->SellFrom = $request->sell_from;
        // $purchaseOrder->payment_term = $request->payment_term;
        // $purchaseOrder->mata_uang = $request->mata_uang;
        // $purchaseOrder->kurs = $request->kurs;
        // $purchaseOrder->apply = $request->apply;
        // $purchaseOrder->updated_by = $this->user->id;
        if ($this->user->PurchaseOrders()->save($purchaseOrder)){
            // for ($i=0; $i < count($request->items); $i++) { 
                // $urutan = $i; 
                // $item = new ItemsPurchaseOrders;
                // $item->gudang = $request->items[$i]['gudang'];
                // $item->barang = $request->items[$i]['barang']['Kode'];
                // $item->nourut = $urutan+1;
                // $item->jumlah = $request->items[$i]['jumlah'];
                // $item->harga = $request->items[$i]['harga'];
                // $item->satuan = $request->items[$i]['satuan'];
                // $item->diskon1 = $request->items[$i]['diskon1'];
                // $item->subtotal = $request->items[$i]['subtotal'];
                // $purchaseOrder->items()->save($item);
            // }
            $barang = collect($request->items)->map(function ($item,$key) {
                $item['NoUrut'] = $key+1;
                // $item['Gudang'] = '0101/0001';
                // $item['NoPR'] = isset($item['NoPartOrder'],$item) ? $item['NoPartOrder'] : NULL;
                // $item['ETA'] = isset($item['TanggalKirim']) ? $item['TanggalKirim'] : NULL;
                $item['Diskon1'] = $item['Diskon'];
                return $item;
            });
            $jasa = collect($request->itemsJasa)->map(function ($item,$key) {
                $item['Perkiraan'] = $item['KodePerkiraan'];
                $item['Diskon1'] = $item['Diskon'];
                return $item;
            });
            $purchaseOrder->itemsJasa()->createMany($jasa);
            $purchaseOrder->items()->createMany($barang);

            return response()->json([
                "status" => true,
                "purchaseOrder" => $purchaseOrder,
                "items"=> $barang
            ],200);
        // post tanpa jwt
        // if ($customer->save()){
        //     return response()->json([
        //         "status" => true,
        //         "customer" => $customer
        //     ]);
        // }
        }else {
            return response()->json([
                "status" => false,
                "message" => "gagal save"
            ], 500);
        };
    }

    public function storeJasa(Request $request)
    {
        $last = PurchaseOrders::where('KodeNota','like','%PJ%')->latest()->first();
        $kode = '0101/PJ/';
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
        $purchaseOrder = new PurchaseOrders;
        $purchaseOrder->KodeNota = $kode;
        $purchaseOrder->Tanggal = $request->Tanggal;
        $purchaseOrder->Supplier = $request->Supplier;
        $purchaseOrder->BillFrom = $request->BillFrom;
        $purchaseOrder->SellFrom = $request->SellFrom;
        $purchaseOrder->PaymentTerm = $request->PaymentTerm;
        $purchaseOrder->MataUang = $request->MataUang;
        $purchaseOrder->Kurs = $request->Kurs;
        $purchaseOrder->TanggalKirim = $request->TanggalKirim;
        $purchaseOrder->Referensi = $request->Referensi;
        $purchaseOrder->NomorWO = $request->NomorWO;
        $purchaseOrder->NomorRangka = $request->NomorRangka;
        $purchaseOrder->Keterangan = $request->Keterangan;
        $purchaseOrder->Gudang = '0101/0001';
        $purchaseOrder->DiUbahOleh = $this->user->Kode;
        $purchaseOrder->PPnPersenManual = $request->PPnPersen;

        if ($this->user->PurchaseOrders()->save($purchaseOrder)){
            $jasa = collect($request->items)->map(function ($item,$key) {
                $item['NoUrut'] = $key+1;
                $item['Diskon1'] = $item['Diskon'];
                return $item;
            });
            $purchaseOrder->itemsJasa()->createMany($jasa);

            return response()->json([
                "status" => true,
                "purchaseOrder" => $purchaseOrder,
                "items"=> $jasa
            ],200);
        }else {
            return response()->json([
                "status" => false,
                "message" => "gagal save"
            ], 500);
        };
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PurchaseOrders  $purchaseOrders
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrders $purchaseOrders, $id)
    {
        $purchaseOrders = PurchaseOrders::with('supplier:id,Kode,Nama')->with('uang:Kode,Nama')->with('wo:KodeNota,ReserveOutcome,AvailableBudget,ReserveOutcomeJasa,AvailableBudgetJasa')
        ->with('BillFrom:id,Kode,Nama')->with('SellFrom:id,Kode,Nama')->with(['items' => function ($q){
            return $q->with('barang:id,Kode,Nama,Merk,PartNumber1,Kendaraan','gudang:Kode,Nama','satuan:Barang,Rasio,Nama'
                // ,'rpl:NoPartOrder,Barang,Jumlah,TerpenuhiPO'
            );
        }])->with(['itemsJasa' => function ($q){
            return $q->with('perkiraan:Kode,Nama');
        }])->find($id);
        return response()->json([
            'data' => $purchaseOrders,
            'status' => true
        ],200);
    }

    public function showJasa($id)
    {
        $purchaseOrders = PurchaseOrders::with('supplier:id,Kode,Nama')->with('uang:Kode,Nama')->with('wo:KodeNota,ReserveOutcome,AvailableBudget,ReserveOutcomeJasa,AvailableBudgetJasa')
        ->with('BillFrom:id,Kode,Nama')->with('SellFrom:id,Kode,Nama')->with(['itemsJasa' => function ($q){
            return $q->with('perkiraan:Kode,Nama','pekerjaan:Kode,Nama');
        }])->find($id);
        $rwl = DB::table('DetailWorkOrderPekerjaan')
        ->join('JenisPekerjaan','JenisPekerjaan.Kode','=','DetailWorkOrderPekerjaan.JenisPekerjaan')
        ->where('KodeNota',$purchaseOrders->NomorWO)
        ->get(['KodeNota','DetailWorkOrderPekerjaan.JenisPekerjaan','JenisPekerjaan.Nama','Jumlah','Harga','Diskon1','Keterangan']);
        return response()->json([
            'data' => $purchaseOrders,
            'rwl' => $rwl,
            'status' => true
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PurchaseOrders  $purchaseOrders
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseOrders $purchaseOrders)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PurchaseOrders  $purchaseOrders
     * @return \Illuminate\Http\Response
     */
    public function changeSupplier(Request $request,$id)
    {
        $data = PurchaseOrders::find($id);
        $data->Supplier = $request->Supplier;
        $data->BillFrom = $request->BillFrom;
        $data->SellFrom = $request->SellFrom;
        $data->DiUbahOleh = $this->user->Kode;
        $cekReceive = ItemsPurchaseOrders::where('KodeNota',$data->KodeNota)->where('Terpenuhi','>',0)->first();
        $cekReceiveJasa = ItemsPurchaseOrdersJasa::where('KodeNota',$data->KodeNota)->where('Terpenuhi','>',0)->first();
        if (!empty($cekReceive) || !empty($cekReceiveJasa)) {
            return response()->json([
                "status"=>true,
                "message"=>"received"
            ],200);
        }
        $data->save();
        return response()->json([
            'status' => true,
            "message" => "changed"
        ],200);
    }
    public function update(Request $request, PurchaseOrders $purchaseOrders, $id)
    {
        // return $request->all();
        // $this->validate($request, [
            //     "kode_nota" => "required",
        // ]);
        // return $request->kode_nota;
        $purchaseOrder = PurchaseOrders::find($id);
        // $purchaseOrder->kode_nota = $request->kode_nota;
        $purchaseOrder->Tanggal = $request->Tanggal;
        // $purchaseOrder->supplier = $request->supplier;
        $purchaseOrder->PaymentTerm = $request->PaymentTerm;
        // $purchaseOrder->status = $request->status;
        // $purchaseOrder->total = $request->total;
        // $purchaseOrder->diskon = $request->diskon;
        // $purchaseOrder->dpp = $request->dpp;
        // $purchaseOrder->ppn = $request->ppn;
        $purchaseOrder->PPnPersenManual = $request->PPnPersen;
        // $purchaseOrder->total_bayar = $request->total_bayar;
        $purchaseOrder->TanggalKirim = $request->TanggalKirim;
        $purchaseOrder->Referensi = $request->Referensi;
        // $purchaseOrder->no_rangka = $request->no_rangka;
        // $purchaseOrder->no_polisi = $request->no_polisi;
        // $purchaseOrder->no_wo = $request->no_wo;
        // $purchaseOrder->BillFrom = $request->bill_from;
        // $purchaseOrder->SellFrom = $request->sell_from;
        $purchaseOrder->Keterangan = $request->Keterangan;
        // $purchaseOrder->mata_uang = $request->mata_uang;
        $purchaseOrder->Kurs = $request->Kurs;
        // $purchaseOrder->apply = $request->apply;
        $purchaseOrder->DiUbahOleh = $this->user->Kode;
        $last = ItemsPurchaseOrders::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        // $barang = collect($request->items)->map(function ($item,$key) {
        //     // $item['ETA'] = isset($item['TanggalKirim']) ? $item['TanggalKirim'] : NULL;
        //     // $item['Diskon1'] = $item['Diskon'];
        //     return [
        //         'KodeNota' => $item['KodeNota'],
        //         'Barang' => $item['Barang'],
        //         'NoUrut' => $item['NoUrut'],
        //         'ETA' => isset($item['TanggalKirim']) ? $item['TanggalKirim'] : NULL,
        //         'Diskon1' => $item['Diskon'],
        //         'Jumlah' => $item['Jumlah'],
        //         'Gudang' => $item['Gudang'],
        //         'Harga' => $item['Harga'],
        //         'Keterangan' => $item['Keterangan']
        //     ];
        // });
        // return $barang;
        // $d = [];
        // foreach ($barang as $key => $value) {
        //     array_push($d,$value);
        // }
        // return print_r($d);
        // return $barang->dd();
        $barang_new = collect($request->new_items)->map(function ($item,$key) use($last){
            $item['NoUrut'] = $key+1+$last['NoUrut'];
            // $item['Gudang'] = '0101/0001';
            // $item['NoPR'] = isset($item['NoPartOrder'],$item) ? $item['NoPartOrder'] : NULL;
            // $item['ETA'] = isset($item['TanggalKirim']) ? $item['TanggalKirim'] : NULL;
            $item['Diskon1'] = $item['Diskon'];
            return $item;
        });
        $jasa_new = collect($request->new_itemsJasa)->map(function ($item,$key) use($last){
            // $item['NoUrut'] = $key+1+$last['NoUrut'];
            $item['Perkiraan'] = $item['KodePerkiraan'];
            $item['Diskon1'] = $item['Diskon'];
            return $item;
        });
        if($purchaseOrder->save()){
            for ($i=0; $i < count($request->items); $i++) { 
                $oldVal = ItemsPurchaseOrders::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('Barang',$request->items[$i]['Barang'])
                ->get();
                $newVal = [
                    'Gudang' => $request->items[$i]['Gudang'], 'Keterangan' => $request->items[$i]['Keterangan'], 
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                    // 'ETA' => isset($request->items[$i]['TanggalKirim']) ? $request->items[$i]['TanggalKirim'] : NULL
                ];
                ItemsPurchaseOrders::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('Barang',$request->items[$i]['Barang'])
                ->update([
                    'Gudang' => $request->items[$i]['Gudang'], 'Keterangan' => $request->items[$i]['Keterangan'], 
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                    'Jumlah' => $request->items[$i]['Jumlah'],
                    // 'ETA' => isset($request->items[$i]['TanggalKirim']) ? $request->items[$i]['TanggalKirim'] : NULL
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPurchaseOrders",
                    'auditable_id' => $request->items[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }
            for ($i=0; $i < count($request->itemsJasa); $i++) { 
                $oldVal = ItemsPurchaseOrdersJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsJasa[$i]['NoUrut'])
                // ->where('JenisPekerjaan',$request->itemsJasa[$i]['JenisPekerjaan'])
                ->get();
                $newVal = [
                    'Perkiraan' => $request->itemsJasa[$i]['KodePerkiraan'], 'Keterangan' => $request->itemsJasa[$i]['Keterangan'], 
                    'Harga' => $request->itemsJasa[$i]['Harga'], 'Diskon1' => $request->itemsJasa[$i]['Diskon'],
                    'Jumlah' => $request->itemsJasa[$i]['Jumlah'],
                    // 'ETA' => isset($request->items[$i]['TanggalKirim']) ? $request->items[$i]['TanggalKirim'] : NULL
                ];
                ItemsPurchaseOrdersJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsJasa[$i]['NoUrut'])
                // ->where('JenisPekerjaan',$request->itemsJasa[$i]['JenisPekerjaan'])
                ->update([
                    'Perkiraan' => $request->itemsJasa[$i]['KodePerkiraan'], 'Keterangan' => $request->itemsJasa[$i]['Keterangan'], 
                    'Harga' => $request->itemsJasa[$i]['Harga'], 'Diskon1' => $request->itemsJasa[$i]['Diskon'],
                    'Jumlah' => $request->itemsJasa[$i]['Jumlah'],
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPurchaseOrdersJasa",
                    'auditable_id' => $request->itemsJasa[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }
            $purchaseOrder->items()->createMany($barang_new);
            $purchaseOrder->itemsJasa()->createMany($jasa_new);
            // $barang_update = $barang->toJson();
            if (!empty($request->hapus_items)) {
                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = ItemsPurchaseOrders::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->where('Barang',$request->hapus_items[$i]['Barang'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsPurchaseOrders",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
            }
            if (!empty($request->hapus_itemsJasa)) {
                for ($i=0; $i < count($request->hapus_itemsJasa); $i++) { 
                    $hps = ItemsPurchaseOrdersJasa::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_itemsJasa[$i]['NoUrut'])
                    // ->where('JenisPekerjaan',$request->hapus_itemsJasa[$i]['JenisPekerjaan'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsPurchaseOrdersJasa",
                        'auditable_id' => $request->hapus_itemsJasa[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_itemsJasa[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
            }
            return response()->json([
                "status"=>true,
                "data"=>$purchaseOrder,
                "items"=>$request->items,
                'new item'=>$barang_new,
                "delet item"=>$request->hapus_items
            ],200);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
    }

    public function updateJasa(Request $request, PurchaseOrders $purchaseOrders, $id)
    {
        $purchaseOrder = PurchaseOrders::find($id);
        $purchaseOrder->Tanggal = $request->Tanggal;
        $purchaseOrder->PaymentTerm = $request->PaymentTerm;
        $purchaseOrder->TanggalKirim = $request->TanggalKirim;
        $purchaseOrder->Referensi = $request->Referensi;
        $purchaseOrder->Keterangan = $request->Keterangan;
        $purchaseOrder->Kurs = $request->Kurs;
        $purchaseOrder->PPnPersenManual = $request->PPnPersen;
        $purchaseOrder->DiUbahOleh = $this->user->Kode;
        $last = ItemsPurchaseOrdersJasa::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');

        $jasa_new = collect($request->new_items)->map(function ($item,$key) use($last){
            $item['NoUrut'] = $key+1+$last['NoUrut'];
            $item['Diskon1'] = $item['Diskon'];
            return $item;
        });
        if($purchaseOrder->save()){
            for ($i=0; $i < count($request->items); $i++) { 
                $oldVal = ItemsPurchaseOrdersJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('JenisPekerjaan',$request->items[$i]['JenisPekerjaan'])
                ->get();
                $newVal = [
                    'Perkiraan' => $request->items[$i]['Perkiraan'], 'Keterangan' => $request->items[$i]['Keterangan'], 
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                    'Jumlah' => $request->items[$i]['Jumlah'],
                    // 'ETA' => isset($request->items[$i]['TanggalKirim']) ? $request->items[$i]['TanggalKirim'] : NULL
                ];
                ItemsPurchaseOrdersJasa::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->items[$i]['NoUrut'])
                ->where('JenisPekerjaan',$request->items[$i]['JenisPekerjaan'])
                ->update([
                    'Perkiraan' => $request->items[$i]['Perkiraan'], 'Keterangan' => $request->items[$i]['Keterangan'], 
                    'Harga' => $request->items[$i]['Harga'], 'Diskon1' => $request->items[$i]['Diskon'],
                    'Jumlah' => $request->items[$i]['Jumlah'],
                    // 'ETA' => isset($request->items[$i]['TanggalKirim']) ? $request->items[$i]['TanggalKirim'] : NULL
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPurchaseOrdersJasa",
                    'auditable_id' => $request->items[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }
            $purchaseOrder->itemsJasa()->createMany($jasa_new);
            if (!empty($request->hapus_items)) {
                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = ItemsPurchaseOrdersJasa::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->where('JenisPekerjaan',$request->hapus_items[$i]['JenisPekerjaan'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsPurchaseOrdersJasa",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
                return response()->json([
                    "status"=>true,
                    "data"=>$purchaseOrder,
                    "items"=>$request->items,
                    'new item'=>$jasa_new,
                    "delet item"=>$request->hapus_items
                ],200);
            }
        }
        else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\PurchaseOrders  $purchaseOrders
     * @return \Illuminate\Http\Response
     */
    public function batalin(Request $request, $id)
    {
        $data = PurchaseOrders::find($id);
        $cekReceive = ItemsPurchaseOrders::where('KodeNota',$data->KodeNota)->where('Terpenuhi','>',0)->first();
        $cekReceiveJasa = ItemsPurchaseOrdersJasa::where('KodeNota',$data->KodeNota)->where('Terpenuhi','>',0)->first();
        if (!empty($cekReceive) || !empty($cekReceiveJasa)) {
            return response()->json([
                "status"=>true,
                "message"=>"received"
            ],200);
        } else {
            $oldVal = ItemsPurchaseOrders::where('KodeNota',$data->KodeNota)->get(['Barang','Jumlah']);
            if (!empty($oldVal)) {
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPurchaseOrders",
                    'auditable_id' => 0,
                    'old_values' => $oldVal,
                    'new_values' => 'BatalPO, Jumlah jadi 0'
                ]);
                ItemsPurchaseOrders::where('KodeNota',$data->KodeNota)
                ->update([
                    'Jumlah' => 0,
                ]);
            }

            $oldValJasa = ItemsPurchaseOrdersJasa::where('KodeNota',$data->KodeNota)->get(['KodeNota','Perkiraan','NoUrut','Jumlah']);
            if (!empty($oldValJasa)) {
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPurchaseOrdersJasa",
                    'auditable_id' => 0,
                    'old_values' => $oldValJasa,
                    'new_values' => 'BatalPO, Jumlah jadi 0'
                ]);
                ItemsPurchaseOrdersJasa::where('KodeNota',$data->KodeNota)
                ->update([
                    'Jumlah' => 0,
                ]);
            }
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
        
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }
    public function destroy(PurchaseOrders $purchaseOrders, $id)
    {
        // $purchaseOrders = PurchaseOrders::find($id);
        // $item = $purchaseOrders->items()->get('id');
        // $x = [];
        // foreach ($item as $key => $value) {
        //     array_push($x,$value['id']);
        // }
        // if ($purchaseOrders->delete()){
        //     ItemsPurchaseOrders::destroy($x);
        //     return response()->json([
        //         "status"=> true,
        //         "purchaseOrders"=> $purchaseOrders
        //     ]);
        // } else {
        //     return response()->json([
        //         "status"=> false,
        //         "Message"=> "gagal delete"
        //     ]);
        // }
    }

    public function report(Request $request,$id)
    {
        // $uri = $request->path();
        if ($request->is('api/report-po/tmj/*')) {
            $data = PurchaseOrders::with('supplier:Kode,Nama,Alamat,Kota',
            'wo:KodeNota,NomorRangka,NomorMesin,Pemilik,NomorPolisi',
            'wo.rangka:NomorRangka,Kendaraan,Tahun',
            'wo.rangka.kendaraan:Kode,Nama,Merk',
            'wo.pemilik:Kode,Nama',
            'items:KodeNota,Barang,Jumlah,Rasio,Harga,Diskon1,SubTotal,SubDiskon',
            'items.barang:Kode,Nama,PartNumber1,PartNumber2',
            'items.satuan:Barang,Rasio,Nama')
            ->select('KodeNota','Supplier','Tanggal','NomorWO','PPn','DPP','TotalBayar','Keterangan')->find($id);
        } else if ($request->is('api/report-po/no-disc/*')) {
            // po no disc
            $data = PurchaseOrders::with('supplier:Kode,Nama,Alamat,Kota',
            'wo:KodeNota,NomorRangka,NomorMesin,Pemilik,Lokasi,NomorPolisi',
            'wo.rangka:NomorRangka,Kendaraan,Tahun',
            'wo.rangka.kendaraan:Kode,Nama,Merk,IsiSilinder',
            'wo.pemilik:Kode,Nama',
            'items:KodeNota,Barang,Jumlah,Rasio,Harga,SubTotal',
            'items.barang:Kode,Nama,PartNumber1,PartNumber2',
            'items.satuan:Barang,Rasio,Nama')
            ->select('KodeNota','Supplier','Tanggal','NomorWO','PPn','Keterangan')->find($id);
        } else if ($request->is('api/report-po/jasa/*')) {
            $data = PurchaseOrders::with('supplier:Kode,Nama,Alamat,Kota',
            'wo:KodeNota,NomorRangka,NomorMesin,Pemilik,Lokasi,NomorPolisi',
            'wo.rangka:NomorRangka,Kendaraan,Tahun',
            'wo.rangka.kendaraan:Kode,Nama,Merk,IsiSilinder',
            'wo.pemilik:Kode,Nama',
            'itemsJasa:KodeNota,Keterangan,Jumlah,Rasio,Harga,Diskon1,SubTotal,SubDiskon')
            ->select('KodeNota','Supplier','Tanggal','NomorWO','PPn','DPP','TotalBayar','Keterangan')->find($id);
        } else {
            // po biasa
            $data = PurchaseOrders::with('supplier:Kode,Nama,Alamat,Kota',
            'wo:KodeNota,NomorRangka,NomorMesin,Pemilik,Lokasi,NomorPolisi',
            'wo.rangka:NomorRangka,Kendaraan,Tahun',
            'wo.rangka.kendaraan:Kode,Nama,Merk,IsiSilinder',
            'wo.pemilik:Kode,Nama',
            'items:KodeNota,Barang,Jumlah,Rasio,Harga,Diskon1,SubTotal,SubDiskon',
            'items.barang:Kode,Nama,PartNumber1,PartNumber2',
            'items.satuan:Barang,Rasio,Nama')
            ->select('KodeNota','Supplier','Tanggal','NomorWO','PPn','DPP','TotalBayar','Keterangan')->find($id);
        }
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

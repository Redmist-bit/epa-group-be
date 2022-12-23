<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Coa;
use App\Models\Estimasi;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\ItemsPiutangInvoice;
use App\Models\InvoiceDetailPekerjaan;
use App\Models\InvoiceDetailDeductible;
use App\Models\MataUangs;
use App\Models\WorkOrder;
use App\Models\BarangSP;
use App\Models\Barangs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
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
    public function indexDeductible($from,$to)
    {
        $data = Invoice::where('KodeNota','like','%FD%')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)->get();
        return response()->json([
            'data' => $data
        ],200);
    }
    
    public function index($from,$to)
    {
        $data = Invoice::where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->with('wo','pelanggan:id,Kode,Nama','wo.pemilik:Kode,Nama')->where('KodeNota','like','%FW%')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)->get();
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

    public function perkiraan()
    {
        return Coa::where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('Kode')->get();
    }

    public function load(Request $request)
    {
        $nomorWo = $request->query('wo');
        $data = Estimasi::where('NomorWO',$nomorWo)->orderByDesc('id')->first('id');
        $barang = Estimasi::with('pekerjaan','pekerjaan.kerja','barang:KodeNota,Barang,Jumlah,Rasio,Keterangan,Harga,BarangBekas,Diskon,Diskon1,SubTotal',
        'barang.satuan:Barang,Rasio,Nama',
        'barang.barang:Kode,Nama,PartNumber1,Merk,Kendaraan')
        ->select('id','KodeNota')->find($data->id);
        return response()->json([
            "status" => true,
            "data" => $barang
        ],200);
    }
    public function barangs(Request $request)
    {
        $skip = $request->query('skip');
        $take = $request->query('take');
        $search = $request->query('search');

        
        // if ($search != null) {
        //     $allColumns = ['Kode','Nama','PartNumber1','Kendaraan','Merk'];
        //     $data = BarangSP::select('Kode','Nama','PartNumber1','Kendaraan','Merk','Satuan','HargaJual','HargaBeli');
        //     foreach ($allColumns as $key => $value) {
        //         $data = $data->orWhere($value,'like','%'.$search.'%');
        //     }
        //     $count = $data->count();
        //     $data = $data->offset($skip)->limit($take)->get();
        // } else {
        //     $count = BarangSP::count();
        //     $data = BarangSP::select('Kode','Nama','PartNumber1','Kendaraan','Merk','Satuan','HargaJual','HargaBeli')
        //     ->orderBy('Kode')->skip($skip)->take($take)->get();
        // }

        if ($search != null) {
            $allColumns = ['Kode','Barang.Nama','PartNumber1'];
            $data = DB::table('HrgJual')->rightJoin('Satuan',function($join){
                $join->on('HrgJual.Barang','=','Satuan.Barang')->on('HrgJual.Rasio','=','Satuan.Rasio');
            })->join('Barang','Satuan.Barang','=','Barang.Kode','full')
            ->select('Barang.Kode','Barang.Nama','Barang.PartNumber1','Barang.Kendaraan','Barang.Merk','Satuan.Rasio','Satuan.Nama as Satuan','HrgJual.Harga');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('Barang.Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Barang.Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } else {
            $count = Barangs::where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = DB::table('HrgJual')->rightJoin('Satuan',function($join){
                $join->on('HrgJual.Barang','=','Satuan.Barang')->on('HrgJual.Rasio','=','Satuan.Rasio');
            })->join('Barang','Satuan.Barang','=','Barang.Kode','full')
            ->where('Barang.Kode','like',substr($this->user->Kode,0,2).'%')
            ->select('Barang.Kode','Barang.Nama','Barang.PartNumber1','Barang.Kendaraan','Barang.Merk','Satuan.Rasio','Satuan.Nama as Satuan','HrgJual.Harga')
            ->orderBy('Barang.Kode')->skip($skip)->take($take)->get();
        }

        return response()->json([
            'result' => $data,
            'count' => $count
        ]);
    }
    public function wo(Request $request)
    {
        $skip = $request->query('skip');
        $take = $request->query('take');
        $search = $request->query('search');
        if ($search != null) {
            $allColumns = ['KodeNota','Pelanggan','NomorRangka','NomorMesin','NomorPolisi','Pemilik'];
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama','uang:Kode,Nama');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get(['KodeNota','NomorRangka','NomorMesin','NomorPolisi','Pelanggan','Pemilik','Kurs','MataUang']);
        } else {
            $count = WorkOrder::where('KodeNota','like',substr($this->user->Kode,0,2).'%')->count();
            $data = WorkOrder::with('pelanggan:Kode,Nama','pemilik:Kode,Nama','uang:Kode,Nama')
            // ->whereNull ('Status')
            ->where('KodeNota','like',substr($this->user->Kode,0,2).'%')
            ->offset($skip)->limit($take)
            ->get(['KodeNota','NomorRangka','NomorMesin','NomorPolisi','Pelanggan','Pemilik','Kurs','MataUang']);
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

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeDeductible(Request $request)
    {
        $last = Invoice::where('KodeNota','like','%FD%')->latest()->first();
        $kode = '0101/FD/';
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
        $master = new Invoice;
        $master->KodeNota = $kode;
        $master->Ddtb = $request->Deductible;
        $master->MataUang = $request->MataUang;
        $master->Pelanggan = $request->Pelanggan;
        $master->Tanggal = $request->Tanggal;

        $master->SellTo = $request->Pelanggan;
        $master->NomorWO = $request->NomorWO;
        $master->NomorRangka = $request->NomorRangka;
        $master->NomorMesin = $request->NomorMesin;
        $master->NomorPolisi = $request->NomorPolisi;
        $master->Odometer = $request->Odometer;
        $master->PaymentTerm = $request->PaymentTerm;
        $master->Referensi = $request->Referensi;
        $master->Keterangan = $request->Keterangan;
        $master->PPnPersenManual = $request->PPnPersen;
        $master->OnRisk = $request->OnRisk;
        if ($this->user->invoice()->save($master)) {
            $data = new InvoiceDetailDeductible;
            // $data->KodeNota = $kode;
            $data->Deductible = $request->Deductible;
            $data->Surcharge = $request->Surcharge;
            $data->SelisihProrata = $request->SelisihProrata;
            $data->SelisihDepresiasi = $request->SelisihDepresiasi;
            $data->SelisihUnderInsured = $request->SelisihUnderInsured;
            $data->Diskon = $request->Diskon;
            $master->deductible()->save($data);
            return response()->json([
                'status' => 'success',
                'dataDeductible' => $data,
                'master' => $master,
                // 'pekerjaan' => $pekerjaan
            ],200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "failed to save"
            ],500);
        }
    }

    public function store(Request $request)
    {
        $last = Invoice::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->where('KodeNota','like','%FW%')->orderByDesc('KodeNota')->first('KodeNota');
        $kode = substr($this->user->Kode,0,4).'/FW/';
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
        $data = new Invoice;
        $data->KodeNota = $kode;
        $data->Tanggal = $request->Tanggal;
        $data->Kurs = $request->Kurs;
        $data->Pelanggan = $request->Pelanggan;
        $data->SellTo = $request->Pelanggan;
        $data->BillTo = $request->Pelanggan;
        $data->Referensi = $request->Referensi;
        $data->NomorWO = $request->NomorWO;
        // $data->NomorRangka = $request->NomorRangka;
        // $data->NomorMesin = $request->NomorMesin;
        // $data->NomorPolisi = $request->NomorPolisi;
        // $data->KAss = $request->KAss;
        $data->MataUang = $request->MataUang;
        // $data->KPpn = $request->KPpn;
        // $data->KTtg = $request->KTtg;
        // $data->Kund = $request->Kund;
        // $data->Ddtb = $request->Ddtb;
        // $data->Kexc = $request->Kexc;
        // $data->Odometer = $request->Odometer;
        $data->PaymentTerm = $request->PaymentTerm;
        $data->Keterangan = $request->Keterangan;
        // $data->DPP = $request->DPP;
        // $data->PPnPersen = $request->PPnPersen;
        $data->PPnPersenManual = $request->PPnPersen;
        // $data->Total = $request->Total;
        // $data->Diskon = $request->DIskon;
        $data->DiUbahOleh = $this->user->Kode;

        $data->Gudang = substr($this->user->Kode,0,4).'/0001';

        $data->Salesperson = substr($this->user->Kode,0,4).'/0001';

        if ($this->user->invoice()->save($data)) {
            $barang = collect($request->Items['Barang'])->map(function ($item,$key) {
                $item['NoUrut'] = $key+1;
                $item['Gudang'] = substr($this->user->Kode,0,4).'/0001';
                // $item['Perkiraan'] = $item['KodePerkiraan'];
                $item['Diskon1'] = $item['Diskon'];
                return $item;
            });
            $pekerjaan = collect($request->Items['Pekerjaan'])->map(function ($item,$key) {
                // $item['NoUrut'] = $key+1;
                $item['Perkiraan'] = $item['KodePerkiraan'];
                $item['Diskon1'] = $item['Diskon'];
                // $item['JenisPekerjaan'] = $item['Pekerjaan'];
                return $item;
            });
            $barang = $barang->toArray();
            $pekerjaan = $pekerjaan->toArray();
            $data->barang()->createMany($barang);
            $data->pekerjaan()->createMany($pekerjaan);
            return response()->json([
                'status' => 'success',
                'data' => $data,
                'barang' => $barang,
                'pekerjaan' => $pekerjaan
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
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Invoice::with('pekerjaan.perkiraan',
        'pelanggan:Kode,Nama',
        'barang:KodeNota,NoUrut,Barang,Jumlah,Harga,Diskon,Diskon1,Rasio,SubTotal,Keterangan', 
        // 'barang.perkiraan:Kode,Nama',
        'barang.satuan','barang.barang:Kode,Nama,PartNumber1,Merk,Kendaraan',
        // 'barang.BarangSP:Kode,Nama,PartNumber1,Merk,Kendaraan,Satuan',
        // 'pekerjaan.kerja','wo.pelanggan:Kode,Nama',
        'uang:Kode,Nama')
        ->select('id','KodeNota',
        // 'Odometer',
        'Pelanggan','Keterangan','Tanggal',
        'Kurs','DPP','PPnPersen','PPnPersenManual','TotalBayar',
        // 'KAss','KTtg',
        'PaymentTerm',
        // 'NomorRangka','NomorMesin','NomorPolisi',
        // 'Ddtb','KPpn','Kexc','Kund',
        'Referensi','DiBuatTgl','MataUang','NomorWO')->find($id);
        return response()->json([
            "status" => true,
            "data" => $data
        ],200);
    }

    public function showDeductible($id)
    {
        $data = Invoice::with('deductible','wo.pelanggan:Kode,Nama','uang:Kode,Nama')->select('id','KodeNota','Odometer','Pelanggan','Keterangan','Tanggal',
        'Kurs','DPP','PPnPersen','PPnPersenManual','PPn','TotalBayar','KAss','KTtg','PaymentTerm',
        'NomorRangka','NomorMesin','NomorPolisi','OnRisk',
        'Ddtb','KPpn','Kexc','Kund','Referensi','DiBuatTgl','MataUang','NomorWO')->find($id);
        return response()->json([
            "status" => true,
            "data" => $data
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function edit(Invoice $invoice)
    {
        //
    }

    public function updateBatch(Request $request,$id)
    {
        $data = Invoice::findMany(explode(',',$id));
        $rowData = $request->rowData;
        $data->each(function ($item,$Key) use($rowData){
            $item->update([
                'TglKirim' => $rowData[$Key]['TglKirim'],
                'TglKonfirmasiTerima' => $rowData[$Key]['TglKonfirmasiTerima'],
                'NoResi' => $rowData[$Key]['NoResi'],
                'NoFakturPajak' => $rowData[$Key]['NoFakturPajak']
            ]);
        });
        return response()->json([
            "status" => true,
            "message" => 'update success'
        ],200);
    }

    public function updateDeductible(Request $request, $id)
    {
        $data = Invoice::find($id);
        $data->Tanggal = $request->Tanggal;
        $data->Odometer = $request->Odometer;
        $data->PaymentTerm = $request->PaymentTerm;
        $data->Referensi = $request->Referensi;
        $data->Keterangan = $request->Keterangan;
        $data->MataUang = $request->MataUang;
        $data->Kurs = $request->Kurs;
        $data->OnRisk = $request->OnRisk;
        $data->DiUbahOleh = $this->user->Kode;
        if ($data->save()) {
            $deductible = InvoiceDetailDeductible::find($data->KodeNota);
            $old = $deductible->toJson();
            // $deductible->Deductible = $request->Deductible;
            // $deductible->Surcharge = $request->Surcharge;
            // $deductible->SelisihProrata = $request->SelisihProrata;
            // $deductible->SelisihDepresiasi = $request->SelisihDepresiasi;
            // $deductible->SelisihUnderInsured = $request->SelisihUnderInsured;
            // $deductible->Diskon = $request->Diskon;
            // $deductible->save();
            DB::table('DetailInvoiceDeductible')->where('KodeNota',$data->KodeNota)->update([
                'Deductible' => $request->Deductible,
                'Surcharge' => $request->Surcharge,
                'SelisihProrata' => $request->SelisihProrata,
                'SelisihDepresiasi' => $request->SelisihDepresiasi,
                'SelisihUnderInsured' => $request->SelisihUnderInsured,
                'Diskon' => $request->Diskon
            ]);
            $new = [
                'KodeNota' => $data->KodeNota,
                'Deductible' => $request->Deductible,
                'Surcharge' => $request->Surcharge,
                'SelisihProrata' => $request->SelisihProrata,
                'SelisihDepresiasi' => $request->SelisihDepresiasi,
                'SelisihUnderInsured' => $request->SelisihUnderInsured,
                'Diskon' => $request->Diskon
            ];
            DB::table('audits')->insert([
                'event' => 'updated',
                'user_type' => 'App\Models\User',
                'user_id' => $this->user->id,
                'auditable_type' => "App\Models\InvoiceDetailDeductible",
                'auditable_id' => '0',
                'old_values' => $old,
                'new_values' => collect($new)->toJson()
            ]);
            return response()->json([
                'status' => 'success',
                'dataDeductible' => $deductible,
                'master' => $data,
            ],200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "failed to save"
            ],500);
        }
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Invoice::find($id);
        $data->Tanggal = $request->Tanggal;
        // $data->Odometer = $request->Odometer;
        $data->PaymentTerm = $request->PaymentTerm;
        $data->Referensi = $request->Referensi;
        $data->DiUbahOleh = $this->user->Kode;
        $data->Keterangan = $request->Keterangan;
        // $data->KPpn = $request->KPpn;
        // $data->KTtg = $request->KTtg;
        // $data->Kund = $request->Kund;
        // $data->Ddtb = $request->Ddtb;
        // $data->Kexc = $request->Kexc;
        // return $data;
        $lastBarang = InvoiceDetail::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        $barang = collect($request->new_itemsBarang)->map(function ($item,$key) use($lastBarang){
            $item['NoUrut'] = $key+1+$lastBarang['NoUrut'];
            $item['Gudang'] = substr($this->user->Kode,0,4).'/0001';
            // $item['Perkiraan'] = $item['KodePerkiraan'];
            $item['Diskon1'] = $item['Diskon'];
            return $item;
        });
        $pekerjaan = collect($request->new_itemsJasa)->map(function ($item,$key) {
            // $item['NoUrut'] = $key+1;
            $item['Perkiraan'] = $item['KodePerkiraan'];
            $item['Diskon1'] = $item['Diskon'];
            // $item['JenisPekerjaan'] = $item['Pekerjaan'];
            return $item;
        });
        $barang = $barang->toArray();
        $pekerjaan = $pekerjaan->toArray();
        if ($data->save()) {
            $data->barang()->createMany($barang);
            $data->pekerjaan()->createMany($pekerjaan);
            for ($i=0; $i < count($request->itemsBarang); $i++) { 
                $barang = InvoiceDetail::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsBarang[$i]['NoUrut'])
                ->where('Barang',$request->itemsBarang[$i]['Barang'])
                ->get();
                $newBarang = [
                    'Jumlah' => $request->itemsBarang[$i]['Jumlah'],
                    'Harga' => $request->itemsBarang[$i]['Harga'],
                    'Diskon1' => $request->itemsBarang[$i]['Diskon'],
                ];
                InvoiceDetail::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsBarang[$i]['NoUrut'])
                ->where('Barang',$request->itemsBarang[$i]['Barang'])
                ->update([
                    // 'BarangBekas' => $request->Items['Barang'][$i]['BarangBekas'],
                    'Jumlah' => $request->itemsBarang[$i]['Jumlah'],
                    'Harga' => $request->itemsBarang[$i]['Harga'],
                    'Diskon1' => $request->itemsBarang[$i]['Diskon'],
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\InvoiceDetail",
                    'auditable_id' => $request->itemsBarang[$i]['NoUrut'],
                    'old_values' => $barang->toJson(),
                    'new_values' => collect($newBarang)->toJson()
                ]);
            }
            for ($i=0; $i < count($request->itemsJasa); $i++) { 
                $pekerjaan = InvoiceDetailPekerjaan::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsJasa[$i]['NoUrut'])
                // ->where('JenisPekerjaan',$request->itemsJasa[$i]['Pekerjaan'])
                ->get();
                $newPekerjaan = [
                    'Keterangan' => $request->itemsJasa[$i]['Keterangan'],
                    'Diskon1' => $request->itemsJasa[$i]['Diskon'],
                    'Jumlah' => $request->itemsJasa[$i]['Jumlah'],
                    'Harga' => $request->itemsJasa[$i]['Harga'],
                ];
                InvoiceDetailPekerjaan::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->itemsJasa[$i]['NoUrut'])
                // ->where('JenisPekerjaan',$request->itemsJasa[$i]['Pekerjaan'])
                ->update([
                    'Keterangan' => $request->itemsJasa[$i]['Keterangan'],
                    'Diskon1' => $request->itemsJasa[$i]['Diskon'],
                    'Jumlah' => $request->itemsJasa[$i]['Jumlah'],
                    'Harga' => $request->itemsJasa[$i]['Harga'],
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\InvoiceDetailPekerjaan",
                    'auditable_id' => $request->itemsJasa[$i]['NoUrut'],
                    'old_values' => $pekerjaan,
                    'new_values' => collect($newPekerjaan)->toJson()
                ]);
            }
            if (!empty($request->hapus_items)) {
                for ($i=0; $i < count($request->hapus_items['Barang']); $i++) { 
                    $barang = InvoiceDetail::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items['Barang'][$i]['NoUrut'])
                    ->where('Barang',$request->hapus_items['Barang'][$i]['Barang'])
                    ->get();
                    $hps = InvoiceDetail::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items['Barang'][$i]['NoUrut'])
                    ->where('Barang',$request->hapus_items['Barang'][$i]['Barang'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\InvoiceDetail",
                        'auditable_id' => $request->hapus_items['Barang'][$i]['NoUrut'],
                        'old_values' => $barang,
                        'new_values' => '[]'
                    ]);
                }
                for ($i=0; $i < count($request->hapus_items['Pekerjaan']); $i++) { 
                    $pekerjaan = InvoiceDetailPekerjaan::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items['Pekerjaan'][$i]['NoUrut'])
                    // ->where('JenisPekerjaan',$request->hapus_items['Pekerjaan'][$i]['Pekerjaan'])
                    ->get();
                    $hps = InvoiceDetailPekerjaan::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items['Pekerjaan'][$i]['NoUrut'])
                    // ->where('JenisPekerjaan',$request->hapus_items['Pekerjaan'][$i]['Pekerjaan'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\InvoiceDetailPekerjaan",
                        'auditable_id' => $request->hapus_items['Pekerjaan'][$i]['NoUrut'],
                        'old_values' => $pekerjaan,
                        'new_values' => '[]'
                    ]);
                }
                return response()->json([
                    "status"=>true,
                    "data"=>$data,
                    "items"=>$request->Items,
                    "delet item"=>$request->hapus_items
                ],200);
            }
            return response()->json([
                "status"=>true,
                "data"=>$data,
                "items"=>$request->Items,
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
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function destroy(Invoice $invoice)
    {
        //
    }

    public function cekPelunasan($id)
    {
        $detailPiutang = ItemsPiutangInvoice::where('Faktur',function ($q) use($id){
            $q->select('KodeNota')
            ->from('MasterInvoice')
            ->where('id', $id);
        })->first();
        return response()->json([
            'data' => $detailPiutang,
        ],200);
    }

    public function batalin(Request $request, $id)
    {
        $data = Invoice::find($id);
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
        if ($request->Cetak === 'cetakInvoice') {
            $data = Invoice::with('barang:KodeNota,Barang,Jumlah,Harga,HargaIncludePPn,SubTotal,SubDiskon','pekerjaan:KodeNota,JenisPekerjaan,Jumlah,Harga,HargaIncludePPn,SubTotal,SubDiskon','pekerjaan.kerja:Kode,Nama,GrupPekerjaan','wo:KodeNota,Pemilik','wo.pemilik:Kode,Nama','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','NomorPolisi','NomorRangka','NomorMesin','NomorWO','KAss','KTtg','Tanggal','PPn','PPnPersen','DPP','TotalBayar')->find($id);
        } else if ($request->Cetak === 'cetakInvoiceVer2') {
            $data = Invoice::with('barang:KodeNota,Barang,Jumlah,Harga,HargaIncludePPn,SubTotal,SubDiskon','pekerjaan:KodeNota,JenisPekerjaan,Jumlah,Harga,HargaIncludePPn,SubTotal,SubDiskon','pekerjaan.kerja:Kode,Nama,GrupPekerjaan','wo:KodeNota,Pemilik','wo.pemilik:Kode,Nama','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','NomorPolisi','NomorRangka','NomorMesin','NomorWO','KAss','KTtg','Tanggal','PPn','PPnPersen','DPP','TotalBayar')->find($id);
        } else if ($request->Cetak === 'cetakInvoiceOr') {
            $data = Invoice::with('barang:KodeNota,Barang,Jumlah,Harga,HargaIncludePPn,SubTotal,SubDiskon','pekerjaan:KodeNota,JenisPekerjaan,Jumlah,Harga,HargaIncludePPn,SubTotal,SubDiskon','pekerjaan.kerja:Kode,Nama,GrupPekerjaan','wo:KodeNota,Pemilik','wo.pemilik:Kode,Nama','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','NomorPolisi','NomorRangka','NomorMesin','NomorWO','Tanggal','PPn','PPnPersen','DPP','TotalBayar')->find($id);
        } else if ($request->Cetak === 'cetakInvoiceBuma') {
            $data = Invoice::with('wo:KodeNota,Pemilik','wo.pemilik:Kode,Nama','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','NomorPolisi','NomorRangka','NomorMesin','NomorWO','Tanggal','DPP','PPn','TotalBayar')->find($id);
        } else if ($request->Cetak === 'cetakKuitansiSparePart') {
            $data = Invoice::with('barang:KodeNota,Barang,Jumlah,Harga,Diskon1,HargaIncludePPn,SubTotal,SubDiskon','barang.barang:Kode,Nama,PartNumber1','pekerjaan:KodeNota,JenisPekerjaan,Jumlah,Harga,Diskon1,HargaIncludePPn,SubTotal,SubDiskon,Keterangan','pekerjaan.kerja:Kode,Nama','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Tanggal','NomorWO','Pelanggan','NomorPolisi','NomorRangka','DPP','PPn','TotalBayar','Keterangan')->find($id);
        } else if ($request->Cetak === 'cetakKuitansiAsuransi') {
            $data = Invoice::with('wo:KodeNota,Pemilik','wo.pemilik:Kode,Nama','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','Tanggal','NomorWO','NomorRangka','KAss','NomorPolisi')->find($id);
        } else if ($request->Cetak === 'cetakKuitansiTertanggung') {
            $data = Invoice::with('wo:KodeNota,Pemilik','wo.pemilik:Kode,Nama','pelanggan:Kode,Nama','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama')->select('id','KodeNota','Pelanggan','Tanggal','NomorWO','NomorRangka','NomorPolisi','KTtg','Kexc','Kund','KPpn')->find($id);
        } else if ($request->Cetak === 'cetakInvoiceDeductible') {
            $data = Invoice::with('deductible','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama','pelanggan:Kode,Nama')->select('id','KodeNota','NomorWO','Pelanggan','NomorRangka','NomorPolisi','PPn','TotalBayar','Tanggal')->find($id);
        } else if ($request->Cetak === 'cetakInvoiceDeductibleBuma') {
            $data = Invoice::with('deductible:KodeNota,Deductible,SelisihProrata,SelisihDepresiasi,SelisihUnderInsured,SubTotal,Diskon','rangka:NomorRangka,Kendaraan','rangka.kendaraan:Kode,Merk,Nama','pelanggan:Kode,Nama')->select('id','KodeNota','NomorWO','Pelanggan','NomorRangka','NomorPolisi','PPn','TotalBayar','Tanggal')->find($id);
        }
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

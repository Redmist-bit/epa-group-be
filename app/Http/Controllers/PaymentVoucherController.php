<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\PaymentVoucher;
use App\Models\ItemsPaymentVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentVoucherController extends Controller
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
        $data = PaymentVoucher::
        where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        // whereBetween('DiBuatTgl',[$from,$to])
        ->get();
        return response()->json([
            'data' => $data
        ],200);
    }

    public function dataWo(Request $request)
    {
        $skip = $request->query('skip');
        $take = $request->query('take');
        $search = $request->query('search');

        if ($search != null) {
            $allColumns = ['KodeNota','Lokasi','JenisWorkOrder'];
            $data = DB::table('MasterWorkOrder')->join('MataUang','MataUang.Kode','=','MasterWorkOrder.MataUang')->select('KodeNota','Lokasi','JenisWorkOrder','Keterangan','MataUang.Nama as MataUang','MataUang as KodeUang','TotalBayar','Kurs');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->orderBy('MasterWorkOrder.KodeNota')->offset($skip)->limit($take)->get();
        } else {
            $count = DB::table('MasterWorkOrder')->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->count();
            $data = DB::table('MasterWorkOrder')->where('KodeNota','like',substr($this->user->Kode,0,2).'%')->join('MataUang','MataUang.Kode','=','MasterWorkOrder.MataUang')->select('KodeNota','Lokasi','JenisWorkOrder','Keterangan','MataUang.Nama as MataUang','MataUang as KodeUang','TotalBayar','Kurs')
            ->orderBy('MasterWorkOrder.KodeNota')->offset($skip)->limit($take)->get();
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
        $last = PaymentVoucher::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->orderByDesc('KodeNota')->first('KodeNota');
        $kode = substr($this->user->Kode,0,4).'/PV/';
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
        $data = new PaymentVoucher;
        $data->KodeNota = $kode;
        $data->Referensi = $request->Referensi;
        $data->Keterangan = $request->Keterangan;
        $data->Tanggal = $request->Tanggal;
        // $data->Total = $request->Total;
        $data->DiUbahOleh = $this->user->Kode;
        if ($this->user->pv()->save($data)) {
            $items = collect($request->items)->map(function ($itm) {
                $itm['Perkiraan'] = $itm['KodePerkiraan'];
                $itm['MataUang'] = $itm['KodeUang'];
                unset($itm['Kurs']);
                return $itm;
            });
            $data->detail()->createMany($items);
            return response()->json([
                "status" => true,
                "paymentVoucher" => $data,
                "items" => $items,
            ],200);
        } else {
            return response()->json([
                "status" => false,
                "message" => "gagal save"
            ], 500);
        }
        
    }

    public function batalin(Request $request, PaymentVoucher $PaymentVoucher, $id)
    {
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->Kode . " - " . $this->user->Nama . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $pembelian = $PaymentVoucher::find($id);
        $pembelian->Status = 'BATAL';
        $pembelian->KeteranganStatus = $keterangan;
        $pembelian->DiUbahOleh = $this->user->Kode;
        $pembelian->save();
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\PaymentVoucher  $paymentVoucher
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = PaymentVoucher::with('detail','detail.perkiraan:Kode,Nama','detail.mataUang:Kode,Nama')->find($id);
        return response()->json([
            'data' => $data,
            'status' => 'success'
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\PaymentVoucher  $paymentVoucher
     * @return \Illuminate\Http\Response
     */
    public function edit(PaymentVoucher $paymentVoucher)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\PaymentVoucher  $paymentVoucher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$id)
    {
        $data = PaymentVoucher::find($id);
        $data->Referensi = $request->Referensi;
        $data->Keterangan = $request->Keterangan;
        $data->Tanggal = $request->Tanggal;
        // $data->Total = $request->Total;
        $data->DiUbahOleh = $this->user->Kode;
        $last = ItemsPaymentVoucher::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        $items = collect($request->new_items)->map(function ($itm,$key) use($last){
            $itm['NoUrut'] = empty($last) ? $key+1 : $key+1+$last['NoUrut'];
            $itm['Perkiraan'] = $itm['KodePerkiraan'];
            $itm['MataUang'] = $itm['KodeUang'];
            unset($itm['Kurs']);
            return $itm;
        });
        if (!empty($request->hapus_items)) {
            for ($i=0; $i < count($request->hapus_items); $i++) { 
                $hps = ItemsPaymentVoucher::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                ->delete();
                DB::table('audits')->insert([
                    'event' => "deleted",
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPaymentVoucher",
                    'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                    'old_values' => collect($request->hapus_items[$i])->toJson(),
                    'new_values' => '[]'
                ]);
            }
        }
        if ($data->save()) {
            $data->detail()->createMany($items);
            if (!empty($request->hapus_items)) {
                return response()->json([
                    "status"=>true,
                    "pv"=>$data,
                    'new item'=>$items,
                    "delet item"=>$request->hapus_items
                ],200);
            }
            return response()->json([
                "status" => true,
                "paymentVoucher" => $data,
                "items" => $items,
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
     * @param  \App\Models\PaymentVoucher  $paymentVoucher
     * @return \Illuminate\Http\Response
     */
    public function destroy(PaymentVoucher $paymentVoucher)
    {
        //
    }

    public function report($id)
    {
        $data = PaymentVoucher::with('detail:KodeNota,Perkiraan,NoUrut,Lokasi,NomorWO,Keterangan,Jumlah','detail.perkiraan:Kode,Nama')->select('KodeNota','Tanggal','Total','Keterangan')->find($id);
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

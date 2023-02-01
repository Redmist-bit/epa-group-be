<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Invoice;
use App\Models\Penagihan;
use App\Models\Customers;
use Illuminate\Http\Request;
use App\Models\ItemsPenagihan;
use Illuminate\Support\Facades\DB;

class PenagihanController extends Controller
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
        $data = Penagihan::
        whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)->where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        // whereBetween('DiBuatTgl',[$from,$to])
        ->get();
        return response()->json(['data'=>$data],200);
    }
    public function customer(Request $request)
    {
        $skip = $request->query('skip');
        $take = $request->query('take');
        $search = $request->query('search');

        if ($search != null) {
            $allColumns = [
                'Kode',
                'Nama',
                'BadanHukum',
                'Alamat',
                'Kota',
                'KodePos',
                'Telp',
            ];
            $data = Customers::select(
                'Kode',
                'Nama',
                'BadanHukum',
                'Alamat',
                'Kota',
                'KodePos',
                'Telp',
            );
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } else {
            $count = Customers::where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = Customers::select(
                'Kode',
                'Nama',
                'BadanHukum',
                'Alamat',
                'Kota',
                'KodePos',
                'Telp',)->where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('id')->skip($skip)->take($take)->get();
        }

        return response()->json([
            'result' => $data,
            'count' => $count
        ]);
    }
    public function inv(Request $request)
    {
        $pelanggan = $request->query('pelanggan');
        $TglAwal = $request->query('TglAwal');
        $TglAkhir = $request->query('TglAkhir');

        // if ($search != null) {
        //     $allColumns = ['KodeNota','Tanggal','PaymentTerm','Pelanggan','TotalBayar','Terbayar','SisaBayar','OnRisk'];
        //     $data = Invoice::with('pelanggan:Kode,Nama,LamaKredit')->select('KodeNota','Tanggal','PaymentTerm','Pelanggan','TotalBayar','Terbayar','SisaBayar','OnRisk');
        //     foreach ($allColumns as $key => $value) {
        //         $data = $data->orWhere($value,'like','%'.$search.'%');
        //     }
        //     $count = $data->count();
        //     $data = $data->offset($skip)->limit($take)->get();
        // } else {
        //     $count = Invoice::count();
        //     $data = Invoice::with('pelanggan:Kode,Nama,LamaKredit')->select('KodeNota','Tanggal','PaymentTerm','Pelanggan','TotalBayar','Terbayar','SisaBayar','OnRisk')
        //     ->orderBy('id')->skip($skip)->take($take)->get();
        // }
        $data = Invoice::with('pelanggan:Kode,Nama,LamaKredit')
        ->where('Pelanggan',$pelanggan)->whereNull('Status')
        ->whereBetween('Tanggal',[$TglAwal,$TglAkhir])->get(['KodeNota','Tanggal','PaymentTerm','Pelanggan','TotalBayar','Terbayar','SisaBayar']);
        return response()->json([
            'result' => $data,
            // 'count' => $count
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
        // $last = Penagihan::latest()->first();
        $last = Penagihan::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->orderByDesc('KodeNota')->first('KodeNota');
        // $kode = '0101/NP/';
        $kode = substr($this->user->Kode,0,4).'/NP/';
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
        $data = new Penagihan;
        $data->KodeNota = $kode;
        $data->Tanggal = $request->Tanggal;
        $data->Collector = $request->Collector;
        // $data = $request->SudahKembali;
        // $data = $request->Status;
        // $data = $request->KeteranganStatus;
        $data->Keterangan = $request->Keterangan;
        $data->DiUbahOleh = $this->user->Kode;
        // if ($this->user->pembelians()->save($pembelian)){}
        if ($this->user->penagihan()->save($data)) {
            $detail = collect($request->items)->map(function ($item){
                $item['NoInvoice'] = $item['KodeNota'];
                return $item;
            });
            $data->items()->createMany($detail);
            return response()->json([
                'status' => true,
                'penagihan' => $data,
                'detail' => $detail,
            ],200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'gagal save'
            ],500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Penagihan  $penagihan
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Penagihan::with('items','items.invoice:KodeNota,Tanggal,PaymentTerm,Pelanggan,TotalBayar,SisaBayar,Terbayar,OnRisk',
        'items.invoice.pelanggan:Kode,Nama,LamaKredit','collector:Kode,Nama')->find($id);
        return response()->json([
            'data' => $data,
            'status' => 'success'
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Penagihan  $penagihan
     * @return \Illuminate\Http\Response
     */
    public function edit(Penagihan $penagihan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Penagihan  $penagihan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $data = Penagihan::find($id);
        if ($request->has('SudahKembali')) {
            $data->SudahKembali = $request->SudahKembali;
            if ($data->save()) {
                return response()->json(['status' => 'success']);
            }
        } else {
            $data->Tanggal = $request->Tanggal;
            $data->Collector = $request->Collector;
            $data->Keterangan = $request->Keterangan;
            $data->DiUbahOleh = $this->user->Kode;
            $items_new = collect($request->new_items)->map(function($item){
                $item['NoInvoice'] = $item['KodeNota'];
                return $item;
            });
            if ($data->save()) {
                $data->items()->createMany($items_new);
                if (!empty($request->hapus_items)) {
                    for ($i=0; $i < count($request->hapus_items); $i++) { 
                        ItemsPenagihan::where('KodeNota',$request->KodeNota)
                        ->where('NoInvoice',$request->hapus_items[$i]['NoInvoice'])
                        ->delete();
                        DB::table('audits')->insert([
                            'event' => "deleted",
                            'user_type' => 'App\Models\User',
                            'user_id' => $this->user->id,
                            'auditable_type' => "App\Models\ItemsPenagihan",
                            'auditable_id' => '0',
                            'old_values' => collect($request->hapus_items[$i])->toJson(),
                            'new_values' => '[]'
                        ]);
                    }
                    return response()->json([
                        'status' => 'success',
                        'penagihan' => $data,
                        'new item' => $items_new,
                        'deleted item' => $request->hapus_items
                    ],200);
                }
                return response()->json([
                    'status' => 'success',
                    'penagihan' => $data,
                    'new item' => $items_new,
                    // 'deleted item' => $request->hapus_items
                ],200);
            } else {
                return response()->json([
                    'status' => 'fail',
                    'Message' => 'gagal update',
                ],500);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Penagihan  $penagihan
     * @return \Illuminate\Http\Response
     */
    public function destroy(Penagihan $penagihan)
    {
        //
    }

    public function batalin(Request $request, $id)
    {
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->kode . " - " . $this->user->name . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $pembelian = Penagihan::find($id);
        $pembelian->Status = 'BATAL';
        $pembelian->KeteranganStatus = $keterangan;
        $pembelian->DiUbahOleh = $this->user->Kode;
        $pembelian->save();
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    public function report($id)
    {
        $data = Penagihan::with('items','items.invoice:KodeNota,Pelanggan,NomorRangka,NomorPolisi,NomorWO,TotalBayar','items.invoice.pelanggan:Kode,Nama','items.invoice.rangka.kendaraan:Kode,Nama','items.invoice.wo.pemilik:Kode,Nama')->select('KodeNota')->find($id);
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

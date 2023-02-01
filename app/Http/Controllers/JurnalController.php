<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Jurnal;
use App\Models\JurnalDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JurnalController extends Controller
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
        $data = Jurnal::
        whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)->where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        // whereBetween('DiBuatTgl', [$from, $to])
        ->get();
        return response()->json([
            "data" => $data
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
        // $last = Jurnal::latest()->first();
        $last = Jurnal::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->orderByDesc('KodeNota')->first('KodeNota');
        // $kode = '0101/JM/';
        $kode = substr($this->user->Kode,0,4).'/JM/';
        date_default_timezone_set('Asia/Makassar');
        $periode = date('ymd');
        if (!$last) {
            $kode = $kode.$periode.'/0001';
        } elseif (substr($last->KodeNota,8,6) === $periode) {
            $nomer = substr($last->KodeNota,15);
            $kode = substr($last->KodeNota,0,15).str_pad($nomer+1, 4, '0', STR_PAD_LEFT);;
        } else {
            $kode = $kode.$periode.'/0001';
        }
        // return $kode;
        $data = new Jurnal;
        $data->KodeNota = $kode;
        $data->Referensi = $request->Referensi;
        $data->Keterangan = $request->Keterangan;
        $data->Tanggal = $request->Tanggal;
        $data->Total = $request->Total;
        $data->DiUbahOleh = $this->user->Kode;
        $items = collect($request->items)->map(function ($item){
            $item['Perkiraan'] = $item['KodePerkiraan'];
            $item['MataUang'] = $item['KodeUang'];
            unset($item['Kurs']);
            return $item;
        });
        // $kredit = collect($request->itemsKredit)->map(function ($item){
        //     $item['Perkiraan'] = $item['KodePerkiraan'];
        //     $item['MataUang'] = $item['KodeUang'];
        //     unset($item['Kurs']);
        //     return $item;
        // });
        // $debit->merge($kredit);
        // return $items;
        if ($this->user->jurnal()->save($data)) {
            $data->detail()->createMany($items);
            return response()->json([
                "status" => true,
                "data" => $data,
                "items" => count($request->items),
                // "itemsK" => count($request->itemsKredit),
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
     * @param  \App\Models\Jurnal  $jurnal
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Jurnal::with('detail','detail.perkiraan:Kode,Nama','detail.mataUang:Kode,Nama')->find($id);
        return response()->json([
            'data' => $data,
            'status' => 'success'
        ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Jurnal  $jurnal
     * @return \Illuminate\Http\Response
     */
    public function edit(Jurnal $jurnal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Jurnal  $jurnal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $id)
    {
        $data = Jurnal::find($id);
        $data->Referensi = $request->Referensi;
        $data->Keterangan = $request->Keterangan;
        $data->Tanggal = $request->Tanggal;
        $data->Total = $request->Total;
        $data->DiUbahOleh = $this->user->Kode;
        $lastD = JurnalDetail::where('KodeNota',$request->KodeNota)->where('Sisi','D')->latest('NoUrut')->first('NoUrut');
        $lastK = JurnalDetail::where('KodeNota',$request->KodeNota)->where('Sisi','K')->latest('NoUrut')->first('NoUrut');
        $items = collect($request->new_itemsD)->map(function ($itm,$key) use($lastD){
            $itm['NoUrut'] = $key+1+$lastD['NoUrut'];
            $itm['Perkiraan'] = $itm['KodePerkiraan'];
            $itm['MataUang'] = $itm['KodeUang'];
            unset($itm['Kurs']);
            return $itm;
        });
        $itemsK = collect($request->new_itemsK)->map(function ($itm,$key) use($lastK){
            $itm['NoUrut'] = $key+1+$lastK['NoUrut'];
            $itm['Perkiraan'] = $itm['KodePerkiraan'];
            $itm['MataUang'] = $itm['KodeUang'];
            unset($itm['Kurs']);
            return $itm;
        });
        // return $items->merge($itemsK);
        if ($data->save()) {
            $data->detail()->createMany($items->merge($itemsK));
            if (!empty($request->hapus_items)) {
                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = JurnalDetail::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->delete();
                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\JurnalDetail",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
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
     * @param  \App\Models\Jurnal  $jurnal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Jurnal $jurnal)
    {
        //
    }

    public function batalin(Request $request, Jurnal $jurnal, $id)
    {
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->Kode . " - " . $this->user->Nama . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $data = $jurnal::find($id);
        $data->Status = 'BATAL';
        $data->KeteranganStatus = $keterangan;
        $data->DiUbahOleh = $this->user->Kode;
        JurnalDetail::where('KodeNota',$data->KodeNota)->update(['JumlahAsing' => '0', 'Jumlah' => '0']);
        $data->save();
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    public function report($id)
    {
        $data = Jurnal::with('detail:KodeNota,Perkiraan,Sisi,NoUrut,Keterangan,Jumlah','detail.perkiraan:Kode,Nama')->select('KodeNota','Tanggal','Total','Keterangan')->find($id);
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Piutang;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Models\ItemsPenjualans;
use Illuminate\Support\Facades\DB;
use App\Models\ItemsPiutangInvoice;
use App\Models\ItemsPiutangPerkiraan;

class PiutangController extends Controller
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
        $data = Piutang::where('KodeNota','like',substr($this->user->Kode,0,2).'%')
        ->with('customer:id,Kode,Nama')
        ->whereDate('DiBuatTgl','>=',$from)
        ->whereDate('DiBuatTgl','<=',$to)
        // whereBetween('DiBuatTgl',[$from,$to])
        ->get();
        return response()->json([
            'data' => $data
        ],200);
    }

    public function dataInvoice(Request $request,Invoice $invoice)
    {
        $data = $invoice::where('Pelanggan',$request->pelanggan)
        ->whereNull('Status')
        ->where('SisaBayar','<>',0)->with('wo:KodeNota,NomorPolisi')->get(['id','KodeNota','NomorWO',
        // 'OnRisk',
        'TotalBayar','Keterangan','Referensi','SisaBayar']);
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
        $last = Piutang::where('KodeNota','Like',substr($this->user->Kode,0,2).'%')->orderByDesc('KodeNota')->first('KodeNota');
        // latest()->first();
        $kode = substr($this->user->Kode,0,4).'/PI/';
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
        
        $data = new Piutang;
        $data->KodeNota = $kode;
        $data->Tanggal = $request->Tanggal;
        $data->Pelanggan = $request->Pelanggan;
        $data->MataUang = $request->MataUang;
        $data->Kurs = $request->Kurs;
        $data->Total = $request->Total;
        // $data->status = $request->status;
        // $data->keterangan_status = $request->keterangan_status;
        $data->Keterangan = $request->Keterangan;
        $data->Referensi = $request->Referensi;
        // $data->jumlah_cetak = $request->jumlah_cetak;
        $data->DiUbahOleh = $this->user->Kode;
        
        if ($this->user->piutang()->save($data)) {
            // for ($i=0; $i < count($request->itemspembayaran); $i++) { 
            //     $urutan = $i;
            //     $item = new ItemsPiutangPerkiraan;
            //     $item->perkiraan = $request->itemspembayaran[$i]['perkiraan']['Kode'];
            //     $item->keterangan = $request->itemspembayaran[$i]['keterangan'];                 
            //     $item->jumlah = $request->itemspembayaran[$i]['jumlah'];
            //     $data->itemspembayaran()->save($item);
            // }
            $invoice = collect($request->invoice)->map(function ($item){
                $item['Faktur'] = $item['KodeNota'];
                unset($item['KodeNota']);
                return $item;
            });
            $data->itemsinvoice()->createMany($invoice);
            $pembayaran = collect($request->pembayaran)->map(function ($item,$key){
                $item['NoUrut'] = $key+1;
                $item['Perkiraan'] = $item['Kode'];
                return $item;
            });
            $data->itemspembayaran()->createMany($pembayaran);
            return response()->json([
                "status" => true,
                "piutang" => $data,
                "invoice" => count($request->invoice),
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
     * @param  \App\Models\Piutang  $piutang
     * @return \Illuminate\Http\Response
     */
    public function show(Piutang $db, $piutang)
    {
        // $id = 1;
        $data = $db::with('customer:id,Kode,Nama')
            ->with(['itemspembayaran' => function ($q){return $q->with('perkiraan:id,Kode,Nama');}])
            ->with(['itemsinvoice' => function ($q){return $q->with('invoice:id,KodeNota,TotalBayar,SisaBayar'
                // ,OnRisk,NomorWO','invoice.wo:KodeNota,NomorPolisi'
            );}])
            ->find($piutang);
            return response()->json([
                'data' => $data,
                'status' => true
            ],200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Piutang  $piutang
     * @return \Illuminate\Http\Response
     */
    public function edit(Piutang $piutang)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Piutang  $piutang
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Piutang $db, $piutang)
    {
        $data = $db::find($piutang);
        // $data->tanggal = $request->tanggal;
        // $data->customer = $request->customer;
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

        // $last = ItemsPiutangPerkiraan::where('KodeNota',$request->KodeNota)->latest('NoUrut')->first('NoUrut');
        $pembayaran_new = collect($request->newItemsPembayaran)->map(function ($item,$key) {
            // $item['NoUrut'] = isset($last['NoUrut']) ? $key+1+$last['NoUrut'] : $key+1;
            // $retVal = (condition) ? a : b ;
            $item['Perkiraan'] = $item['Kode'];
            return $item;
        });
        // return $request->all();
        if ($data->save()) {
            for ($i=0; $i < count($request->pembayaran); $i++) { 
                // if (isset($request->itemspembayaran[$i]['id']) == false) {
                //     $last = ItemsPiutangPerkiraan::where('kodenota',$request->kode_nota)->latest('urutan')->first('urutan');
                //     $urutan = $last['urutan']+1;
                //     $item = new ItemsPiutangPerkiraan;
                //     $item->urutan = $urutan;
                //     $item->perkiraan = $request->itemspembayaran[$i]['perkiraan']['Kode'];
                //     $item->keterangan = $request->itemspembayaran[$i]['keterangan'];                 
                //     $item->jumlah = $request->itemspembayaran[$i]['jumlah'];
                //     $data->itemspembayaran()->save($item);
                // } else {
                //     $item = ItemsPiutangPerkiraan::find($request->itemspembayaran[$i]['id']);
                //     $item->perkiraan = $request->itemspembayaran[$i]['perkiraan']['Kode'];
                //     $item->keterangan = $request->itemspembayaran[$i]['keterangan'];                 
                //     $item->jumlah = $request->itemspembayaran[$i]['jumlah'];
                //     $item->save();
                // }
                $oldVal = ItemsPiutangPerkiraan::where('KodeNota',$request->KodeNota)
                ->where('NoUrut',$request->pembayaran[$i]['NoUrut'])
                ->where('Perkiraan',$request->pembayaran[$i]['Perkiraan'])
                ->get();
                $newVal = [
                    'Keterangan' => $request->pembayaran[$i]['Keterangan'],
                    'Jumlah' => $request->pembayaran[$i]['Jumlah']
                ];
                ItemsPiutangPerkiraan::where('KodeNota',$request->KodeNota)
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
                    'auditable_type' => "App\Models\ItemsPiutangPerkiraan",
                    'auditable_id' => $request->pembayaran[$i]['NoUrut'],
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }

            for ($i=0; $i < count($request->invoice); $i++) { 
                $oldVal = ItemsPiutangInvoice::where('KodeNota',$request->KodeNota)
                ->where('Faktur',$request->invoice[$i]['KodeNota'])
                ->get();
                $newVal = [
                    'Keterangan' => $request->invoice[$i]['Keterangan'],
                    'Jumlah' => $request->invoice[$i]['Jumlah']
                ];
                ItemsPiutangInvoice::where('KodeNota',$request->KodeNota)
                ->where('Faktur',$request->invoice[$i]['KodeNota'])
                ->update([
                    'Keterangan' => $request->invoice[$i]['Keterangan'],
                    'Jumlah' => $request->invoice[$i]['Jumlah']
                ]);
                DB::table('audits')->insert([
                    'event' => 'updated',
                    'user_type' => 'App\Models\User',
                    'user_id' => $this->user->id,
                    'auditable_type' => "App\Models\ItemsPiutangInvoice",
                    'auditable_id' => 0,
                    'old_values' => $oldVal->toJson(),
                    'new_values' => collect($newVal)->toJson()
                ]);
            }

            $data->itemspembayaran()->createMany($pembayaran_new);

            if (!empty($request->hapus_items)) {
                // $hps = ItemsPiutangPerkiraan::destroy($request->hapus_items);

                for ($i=0; $i < count($request->hapus_items); $i++) { 
                    $hps = ItemsPiutangPerkiraan::where('KodeNota',$request->KodeNota)
                    ->where('NoUrut',$request->hapus_items[$i]['NoUrut'])
                    ->where('Perkiraan',$request->hapus_items[$i]['Perkiraan'])
                    ->delete();

                    DB::table('audits')->insert([
                        'event' => "deleted",
                        'user_type' => 'App\Models\User',
                        'user_id' => $this->user->id,
                        'auditable_type' => "App\Models\ItemsPiutangPerkiraan",
                        'auditable_id' => $request->hapus_items[$i]['NoUrut'],
                        'old_values' => collect($request->hapus_items[$i])->toJson(),
                        'new_values' => '[]'
                    ]);
                }
                return response()->json([
                    "status"=>true,
                    "piutang"=>$data,
                    "itemspembayaran"=>$request->pembayaran,
                    'new_pembayaran'=>$pembayaran_new,
                    'itemsinvoice'=>$request->invoice,
                    "delet item"=>$hps
                ],200);
                
            }
            return response()->json([
                "status"=>true,
                "piutang"=>$data,
                "itemspembayaran"=>$request->pembayaran,
                'itemsinvoice'=>$request->invoice,
                'new_pembayaran'=>$pembayaran_new,
            ],200);
        } else {
            return response()->json([
                "status"=>false,
                "Message"=>"gagal update"
            ], 500);
        }
    }

    // public function batalin(Request $request, Piutang $piutang, $id)
    // {

    // }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Piutang  $piutang
     * @return \Illuminate\Http\Response
     */
    public function destroy(Piutang $db,$piutang)
    {
        $data = $db::find($piutang);
        $item = $data->itemspembayaran()->get('id');
        $x = [];
        foreach ($item as $key => $value) {
            array_push($x,$value['id']);
        }
        if ($data->delete()) {
            ItemsPiutangPerkiraan::destroy($x);
            return response()->json([
                "status" => true,
                "message" => "deleted"
            ],200);
        } 
        else {
            return response()->json([
                "status" => false,
                "message" => "gagak delete"
            ],500);
        }
        
    }

    public function batalin(Request $request, Piutang $piutang, $id)
    {
        date_default_timezone_set('Asia/Makassar');
        $tgl = date('d/m/y H:i:s A');
        $keterangan = "Batal oleh ". $this->user->Kode . " - " . $this->user->Nama . " Pada ". $tgl . " dengan alasan: " . $request->keterangan;
        $data = $piutang::find($id);
        $data->Status = 'BATAL';
        $data->KeteranganStatus = $keterangan;
        $data->DiUbahOleh = $this->user->Kode;
        $data->save();
        ItemsPiutangInvoice::where('KodeNota',$data->KodeNota)->update(['Jumlah' => 0]);
        ItemsPiutangPerkiraan::where('KodeNota',$data->KodeNota)->update(['Jumlah' => 0]);
        return response()->json([
            "status"=>true,
            "message"=>"berhasil batalin"
        ],200);
    }

    public function report($id)
    {
        $data = Piutang::with('customer:Kode,Nama','itemsinvoice','itemsinvoice.invoice:KodeNota,NomorWO','itemsinvoice.invoice.wo:KodeNota,NomorPolisi')->select('KodeNota','Tanggal','Total','Keterangan','Pelanggan')->find($id);
        return response()->json([
            "data"=>$data,
            "status"=>"success"
        ],200);
    }
}

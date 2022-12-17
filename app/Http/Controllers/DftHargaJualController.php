<?php

namespace App\Http\Controllers;
use JWTAuth;
// use App\Models\Barangs;
// use App\Models\BrgSatuan;
// use App\Models\BrgHargaJual;
// use App\Models\DftHargaJual;
use App\Models\HargaJual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DftHargaJualController extends Controller
{
    protected $user;
    protected $primaryKey = 'satuan_id';

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $data = collect();
        // Barangs::with('satuan','hrgjual')->orderBy('id')->chunck(50000, function($datas) use ($data) {
        //     foreach ($datas as $d) {
        //         $data->push($d);
        //     }
        // });
        // return response()->json($data);
        $skip = $request->query('skip');
        $take = $request->query('take');
        $sort = $request->query('sort');
        $filter = $request->query('filter');
        $field = strtok($sort, ' ');
        $updown = substr($sort, strrpos($sort, ' '));
        $dist = $request->query('dist');
        $search = $request->query('search');
        $count = DB::table('HrgJual')->where('Barang','like',substr($this->user->Kode,0,2).'%')->count();

        if ($search != null) {
            $allColumns = DB::getSchemaBuilder()->getColumnListing('HrgJual');
            // $data = HargaJual::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang');
            $data = DB::table('HrgJual')->join('Barang','Barang.Kode','=','HrgJual.Barang')
                ->join('Satuan','Satuan.Barang','=','HrgJual.Barang')
                ->join('MataUang','MataUang.Kode','=','HrgJual.MataUang')
                ->select('HrgJual.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere('HrgJual.'.$value,'like','%'.$search.'%');
            }
            $data = $data->orWhere('Barang.Nama','like','%'.$search.'%')
                ->orWhere('MataUang.Nama','like','%'.$search.'%')
                ->orWhere('Satuan.Nama','like','%'.$search.'%');
            $count = $data->where('HrgJual.Barang','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('HrgJual.Barang','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } elseif ($dist != null) {
            // $data = DB::table('HrgJual');
            $data = HargaJual::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')
            ->where('HrgJual.Barang','like',substr($this->user->Kode,0,2).'%');
            $fieldDistinct = strtok($dist,';');
            $conditional = $dist;
            for ($i=0; $i < substr_count($dist,';'); $i++) { 
                if ($i+1 == substr_count($dist,';')) {
                    if (substr_count($dist,';') == 1) {
                        $sub = ltrim(substr($conditional,strrpos($conditional,';')),';');
                        $data = $data->whereIn(strtok($sub,'='),explode(',',ltrim(substr($sub,strrpos($sub,'=')),'=')));
                    } else {
                        $data = $data->whereIn(strtok($conditional,'='),explode(',',ltrim(substr($conditional,strrpos($conditional,'=')),'=')));
                    }
                } else {
                    $conditional = substr($conditional,strpos($conditional,';')+1);
                    $sub = strtok($conditional,';');
                    $data = $data->whereIn(strtok($sub,'='),explode(',',ltrim(substr($sub,strrpos($sub,'=')),'=')));
                    $conditional = str_replace($sub.';', "", $conditional);
                }
            }
            $data = $data->distinct()->get($fieldDistinct);
        } elseif ($filter != 'undefined') {
            // $data = DB::table('Pelanggan');
            $data = HargaJual::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')
            ->where('HrgJual.Barang','like',substr($this->user->Kode,0,2).'%');
            $conditional = $filter;
            for ($i=0; $i <= substr_count($filter,';'); $i++) { 
                if ($i == substr_count($filter,';')) {
                    $data = $data->whereIn(strtok($conditional,'='),explode(',',ltrim(substr($conditional,strrpos($conditional,'=')),'=')));
                } else {
                    $data = $data->whereIn(strtok(strtok($conditional,';'),'='),explode(',',ltrim(substr(strtok($conditional,';'),strrpos(strtok($conditional,';'),'=')),'=')));
                    $conditional = str_replace(strtok($conditional,';').';', "", $conditional);
                }
            }
            $count = $data->count();
            $data = $data->offset($skip)->limit($take)->get();
        } elseif ($sort != 'undefined') {
            // $data = DB::table('Pelanggan');
            // $data = HargaJual::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang');
            $data = DB::table('HrgJual')->join('Barang','Barang.Kode','=','HrgJual.Barang')
                ->join('Satuan','Satuan.Barang','=','HrgJual.Barang')
                ->join('MataUang','MataUang.Kode','=','HrgJual.MataUang')
                ->where('HrgJual.Barang','like',substr($this->user->Kode,0,2).'%')
                ->select('HrgJual.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan');
            if (strpos($sort,'=')) {
                $ListSort = explode(',',ltrim(substr($sort, strpos($sort,'=')),'='));
                foreach (array_reverse($ListSort) as $key => $value) {
                    $field = strtok($value, ' ');
                    $updown = substr($value, strrpos($value, ' '));
                    $data = $data->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc');
                }
                $data = $data->orderBy('Barang')->offset($skip)->limit($take)->get();
            } else {
                // $data = DB::table('Pelanggan')->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')->orderBy('id')->offset($skip)->limit($take)->get();
                // $data = HargaJual::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')->orderBy('Barang')->offset($skip)->limit($take)->get();
                $data = DB::table('HrgJual')->join('Barang','Barang.Kode','=','HrgJual.Barang')
                ->join('Satuan','Satuan.Barang','=','HrgJual.Barang')
                ->join('MataUang','MataUang.Kode','=','HrgJual.MataUang')
                ->select('HrgJual.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan')
                ->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')
                ->orderBy('HrgJual.Barang')->offset($skip)->limit($take)->get();
            }
        } else {
            // $data = DB::table('Pelanggan')->orderBy('Kode')->offset($skip)->limit($take)->get();
            // $data = HargaJual::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')->orderBy('Barang')->offset($skip)->limit($take)->get();
            $data = DB::table('HrgJual')->join('Barang','Barang.Kode','=','HrgJual.Barang')
            ->join('Satuan','Satuan.Barang','=','HrgJual.Barang')
            ->join('MataUang','MataUang.Kode','=','HrgJual.MataUang')
            ->select('HrgJual.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan')
            ->where('HrgJual.Barang','like',substr($this->user->Kode,0,2).'%')
            ->orderBy('HrgJual.Barang')->offset($skip)->limit($take)->get();
        }

        return response()->json(["result"=>$data,"count" => $count]);
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
    
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DftHargaJual  $DftHargaJual
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $barangs = Barangs::find($id);
        $satuan['satuan'] = BrgSatuan::where('barangs_id',$id)->get();
        $hrgjual['hrgjual'] = BrgHargaJual::where('barangs_id',$id)->get();
        $x = array();
        array_push($x,$barangs, $satuan, $hrgjual);
        return $x;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DftHargaJual  $DftHargaJual
     * @return \Illuminate\Http\Response
     */
    public function edit(DftHargaJual $DftHargaJual)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DftHargaJual  $DftHargaJual
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $this->validate($request,[
        //     "kode" => "required",
        // ]);

        $hrgjual = BrgHargaJual::find($id);
        $hrgjual->barangs_id = $request->barangs_id;
        $hrgjual->Harga = $request ->Harga;
        $hrgjual->Tanggal = $request ->Tanggal;
        $hrgjual->MataUang = $request ->MataUang;
        $hrgjual->updated_by = $this->user->id;
        $hrgjual ->save();
        return response()->json([
            "status" => true,
            "message" => "update daftar jual barang id".$request->barang_id
        ], 200);
        // $barang->Nama =  $request->Nama;
        // $barang->Gudang = $request->Gudang;
        // if ($this->user->barangs()->save($barang)){
        //     for ($i=0; $i < count($request->satuan); $i++) {
        //         if (isset($request->satuan[$i]['id']) == false) {
        //         $satuan = new BrgSatuan;
        //         $satuan->barangs_id = $barang->id;
        //         $satuan->Rasio = $request->satuan[$i]['Rasio'];
        //         $satuan->save();
        //         }else{
        //         $satuan = BrgSatuan::find($request->satuan[$i]['id']);
        //         $satuan->barangs_id = $barang->id;
        //         $satuan->Rasio = $request->satuan[$i]['Rasio'];
        //         $satuan->save();
        //         }
        
        //     }
        //     for ($x=0; $x < count($request->hrgjual); $x++) { 
        //         if (isset($request->hrgjual[$i]['id']) == false) {
        //         $hrgjual= new BrgHargaJual;
        //         $hrgjual->barangs_id = $barang->id;
        //         $hrgjual->Harga = $request ->hrgjual[$x]['Harga'];
        //         $hrgjual->Tanggal = $request ->hrgjual[$x]['Tanggal'];
        //         $hrgjual->MataUang = $request ->hrgjual[$x]['MataUang'];
        //         $hrgjual ->save();
        //         }else{
        //         $hrgjual->barangs_id = $barang->id;
        //         $hrgjual->Harga = $request ->hrgjual[$x]['Harga'];
        //         $hrgjual->Tanggal = $request ->hrgjual[$x]['Tanggal'];
        //         $hrgjual->MataUang = $request ->hrgjual[$x]['MataUang'];
        //         $hrgjual ->save();
        //         }
                
        //     }
        //     return response()->json([
        //         "status" => true,
        //         "barang" => $barang,
        //         "satuan" => $satuan,
        //         "hrgjual" => $hrgjual
        //     ]);
        // }
        // else {
        //     return response()->json([
        //         "status" => false,
        //         "message" => "gagal update"
        //     ], 500);
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DftHargaJual  $DftHargaJual
     * @return \Illuminate\Http\Response
     */
    public function destroy(DftHargaJual $DftHargaJual, $id)
    {
        $hrgjual = DftHargaJual::find($id);
        if ($hrgjual->delete()){
            return response()->json([
                "status"=> true,
                "hrgjual"=> $hrgjual
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;
use JWTAuth;
// use App\Models\Barangs;
// use App\Models\BrgSatuan;
// use App\Models\BrgHargaBeli;
// use App\Models\DftHargaBeli;
use App\Models\HargaBeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DftHargaBeliController extends Controller
{
    protected $user;
    protected $primaryKey = 'satuan_id';

    public function __construct()
    {
        @$this->user = JWTAuth::parseToken()->authenticate();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $data = collect();
        // Barangs::with('satuan','hrgbeli')->orderBy('id')->chunck(50000, function($datas) use ($data) {
        //     foreach ($datas as $d) {
        //         $data->push($d);
        //     }
        // });
        $skip = $request->query('skip');
        $take = $request->query('take');
        $sort = $request->query('sort');
        $filter = $request->query('filter');
        $field = strtok($sort, ' ');
        $updown = substr($sort, strrpos($sort, ' '));
        $dist = $request->query('dist');
        $search = $request->query('search');
        $count = DB::table('HrgBeli')->where('Barang','like',substr($this->user->Kode,0,2).'%')->count();

        if ($search != null) {
            $allColumns = DB::getSchemaBuilder()->getColumnListing('HrgBeli');
            // $data = HargaBeli::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang');
            $data = DB::table('HrgBeli')->join('Barang','Barang.Kode','=','HrgBeli.Barang')
                ->join('Satuan','Satuan.Barang','=','HrgBeli.Barang')
                ->join('MataUang','MataUang.Kode','=','HrgBeli.MataUang')
                ->select('HrgBeli.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere('HrgBeli.'.$value,'like','%'.$search.'%');
            }
            $data = $data->orWhere('Barang.Nama','like','%'.$search.'%')
                ->orWhere('MataUang.Nama','like','%'.$search.'%')
                ->orWhere('Satuan.Nama','like','%'.$search.'%');
            $count = $data->where('HrgBeli.Barang','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('HrgBeli.Barang','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } elseif ($dist != null) {
            // $data = DB::table('HrgBeli');
            $data = HargaBeli::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')
            ->where('HrgBeli.Barang','like',substr($this->user->Kode,0,2).'%');
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
            $data = HargaBeli::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')
            ->where('HrgBeli.Barang','like',substr($this->user->Kode,0,2).'%');
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
            // $data = HargaBeli::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang');
            $data = DB::table('HrgBeli')->join('Barang','Barang.Kode','=','HrgBeli.Barang')
                ->join('Satuan','Satuan.Barang','=','HrgBeli.Barang')
                ->join('MataUang','MataUang.Kode','=','HrgBeli.MataUang')
                ->where('HrgBeli.Barang','like',substr($this->user->Kode,0,2).'%')
                ->select('HrgBeli.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan');
            if (strpos($sort,'=')) {
                $ListSort = explode(',',ltrim(substr($sort, strpos($sort,'=')),'='));
                foreach (array_reverse($ListSort) as $key => $value) {
                    $field = strtok($value, ' ');
                    $updown = substr($value, strrpos($value, ' '));
                    $data = $data->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc');
                }
                $data = $data->orderBy('HrgBeli.Barang')->offset($skip)->limit($take)->get();
            } else {
                // $data = DB::table('Pelanggan')->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')->orderBy('id')->offset($skip)->limit($take)->get();
                // $data = HargaBeli::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')->orderBy('Barang')->offset($skip)->limit($take)->get();
                $data = DB::table('HrgBeli')->join('Barang','Barang.Kode','=','HrgBeli.Barang')
                ->join('Satuan','Satuan.Barang','=','HrgBeli.Barang')
                ->join('MataUang','MataUang.Kode','=','HrgBeli.MataUang')
                ->select('HrgBeli.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan')
                ->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')
                ->orderBy('HrgBeli.Barang')->offset($skip)->limit($take)->get();
            }
        } else {
            // $data = DB::table('Pelanggan')->orderBy('Kode')->offset($skip)->limit($take)->get();
            // $data = HargaBeli::with('barang:Kode,Nama','mataUang:Kode,Nama','satuan:Rasio,Nama,Barang')->orderBy('Barang')->offset($skip)->limit($take)->get();
            $data = DB::table('HrgBeli')->join('Barang','Barang.Kode','=','HrgBeli.Barang')
            ->join('Satuan','Satuan.Barang','=','HrgBeli.Barang')
            ->join('MataUang','MataUang.Kode','=','HrgBeli.MataUang')
            ->select('HrgBeli.*','Barang.Nama as NamaBarang','MataUang.Nama as NamaUang','Satuan.Nama as NamaSatuan')
            ->where('HrgBeli.Barang','like',substr($this->user->Kode,0,2).'%')
            ->orderBy('HrgBeli.Barang')->offset($skip)->limit($take)->get();
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
     * @param  \App\Models\DftHargaBeli  $DftHargaBeli
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $barangs = Barangs::find($id);
        $satuan['satuan'] = BrgSatuan::where('barangs_id',$id)->get();
        $hrgbeli['hrgbeli'] = BrgHargaBeli::where('barangs_id',$id)->get();
        $x = array();
        array_push($x,$barangs, $satuan, $hrgbeli);
        return $x;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DftHargaBeli  $DftHargaBeli
     * @return \Illuminate\Http\Response
     */
    public function edit(DftHargaBeli $DftHargaBeli)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DftHargaBeli  $DftHargaBeli
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // $this->validate($request,[
        //     "kode" => "required",
        // ]);

        $hrgbeli = BrgHargaBeli::find($id);
        $hrgbeli->barangs_id = $request->barangs_id;
        $hrgbeli->Harga = $request ->Harga;
        $hrgbeli->Tanggal = $request ->Tanggal;
        $hrgbeli->MataUang = $request ->MataUang;
        $hrgbeli->Diskon = $request ->Diskon;
        $hrgbeli->updated_by = $this->user->id;
        $hrgbeli ->save();
        return response()->json([
            "status" => true,
            "message" => "update daftar beli barang id".$request->barang_id
        ],200);

        // $barang = new Barangs;
        // $barang->Nama = $request->Nama;
        // $barang->Gudang = $request->Gudang;
        // if ($this->user->barangs()->save($barang)) {
        //     for ($i=0; $i < count($request->satuan) ; $i++) { 
        //         $satuan = new BrgSatuan;
        //         $satuan->Rasio = $request->satuan[$i]['Rasio'];
        //         $satuan->save();
        //     }
        //     for ($x=0; $x < count($request->hrgbeli); $x++) { 
        //         $hrgbeli= new BrgHargaBeli;
        //         $hrgbeli->Harga = $request ->hrgbeli[$x]['Harga'];
        //         $hrgbeli->Tanggal = $request ->hrgbeli[$x]['Tanggal'];
        //         $hrgbeli->MataUang = $request ->hrgbeli[$x]['MataUang'];
        //         $hrgbeli->Diskon = $request ->hrgbeli[$x]['Diskon';]
        //         $hrgbeli ->save();
        //     }
        //     return response()->json([
        //         "status" => true,
        //         "barang" => $barang,
        //         "satuan" => $satuan,
        //         "hrgbeli" => $hrgbeli
        //     ])
        // }
        // else {
        //     return response()->json([
        //         "status" => false,
        //         "message" => "gagal save"
        //     ])
        // }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DftHargaBeli  $DftHargaBeli
     * @return \Illuminate\Http\Response
     */
    public function destroy(DftHargaBeli $DftHargaBeli, $id)
    {
        $hrgbeli = DftHargaBeli::find($id);
        if ($hrgbeli->delete()){
            return response()->json([
                "status"=> true,
                "hrgbeli"=> $hrgbeli
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }
}

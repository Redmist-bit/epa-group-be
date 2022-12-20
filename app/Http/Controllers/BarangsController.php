<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Barangs;
use App\Models\Satuan;
use App\Models\StokLimit;
use App\Models\BrgHargaJual;
use App\Models\BrgHargaBeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BarangsController extends Controller
{
    protected $user;
    // protected $primaryKey = 'satuan_id';
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
        // return Barangs::all();
        // return Barangs::with('satuan','hrgjual','hrgbeli')->get();
        // $data = collect();
        // return DB::table('barangs')->orderBy('id')->paginate(100);
        // DB::select('select * from barangs')->chunk(1000, function($datas) use ($data) {
        //     foreach ($datas as $value) {
        //         $data->push($value);
        //     }
        // });
        // DB::table('Barang')->
        // select('Barang.*')->orderBy('id')->chunk(2000, function($datas) use ($data) {
        //     foreach ($datas as $d) {
        //         $data->push($d);
        //     }
        // });
        // $data = DB::table('Barang')->orderBy('id','asc')->offset(0)->limit(100)->get();
        // return response()->json($data);
        // Barangs::orderBy('id')->chunk(50000, function($datas) use ($data) {
        //     foreach ($datas as $d) {
        //         $data->push($d);
        //     }
        // });
        // return response()->json($data);
        // return Barangs::with('hrgjual')->get();
        // return Barangs::with('hrgbeli')->get();
        $data = collect();
        $skip = $request->query('skip');
        $take = $request->query('take');
        $sort = $request->query('sort');
        $filter = $request->query('filter');
        $field = strtok($sort, ' ');
        $updown = substr($sort, strrpos($sort, ' '));
        $dist = $request->query('dist');
        $search = $request->query('search');

        if ($search != null) {
            $allColumns = DB::getSchemaBuilder()->getColumnListing('Barang');
            $data = DB::table('Barang');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } elseif ($dist != null) {
            $data = DB::table('Barang')->where('Kode','like',substr($this->user->Kode,0,2).'%');
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
            return response()->json(["result"=>$data]);
        } elseif ($filter != 'undefined') {
            $data = DB::table('Barang')->where('Kode','like',substr($this->user->Kode,0,2).'%');
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
            $data = DB::table('Barang')->where('Kode','like',substr($this->user->Kode,0,2).'%');
            if (strpos($sort,'=')) {
                $ListSort = explode(',',ltrim(substr($sort, strpos($sort,'=')),'='));
                foreach (array_reverse($ListSort) as $key => $value) {
                    $field = strtok($value, ' ');
                    $updown = substr($value, strrpos($value, ' '));
                    $data = $data->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc');
                }
                $data = $data->orderBy('id')->offset($skip)->limit($take)->get();
            } else {
                $data = DB::table('Barang')->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')->orderBy('id')->offset($skip)->limit($take)->get();
            }
            $count = DB::table('Barang')->count();
        } else {
            $count = DB::table('Barang')->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = DB::table('Barang')->where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('Kode')->offset($skip)->limit($take)->get();
        }
        return response()->json([
            "result" => $data,
            "count" => $count,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function merk()
    {
        $data = DB::table('Barang')->select('Merk')->distinct()->pluck('Merk');
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $this->validate($request, [
        //     "Kode" => "required",
            
        // ]);
        // dd( !empty($request->StokLimit));
        //01/0000004
        $last = Barangs::where('Kode','like',substr($this->user->Kode,0,2).'%')->orderByDesc('Kode')->first('Kode');
        // return $last;
        // $last = DB::table('audits')->select('Kode')->get();
        // $last = DB::select('select top 1 [id] from [audits] order by [id] desc');
        // return is_null($last);
        // return is_null($last);
        if (is_null($last)) {
            // return isset($last);
            $kode = substr($this->user->Kode,0,2).'/0000001';
        } else {
            $nmr = substr($last->Kode, -7);
            $kode = substr($this->user->Kode,0,2).'/'.str_pad($nmr + 1, 7, 0, STR_PAD_LEFT);
        }
        // return $kode;
        $barang = new Barangs;
        $barang->Kode = $kode;
        $barang->Nama = $request->Nama;
        $barang->Merk = $request->Merk;
        $barang->Kategori = $request->Kategori;
        $barang->PartNumber1 = $request->PartNumber1;
        $barang->PartNumber2 = $request->PartNumber2;
        $barang->Kendaraan = $request->Kendaraan;
        $barang->KodeSupplier = $request->KodeSupplier;
        $barang->Dimensi = $request->Dimensi;
        $barang->Aktif = $request->Aktif;
        $barang->Gudang = $request->Gudang;
        $barang->Memo = $request->Memo;
        // $barang->StokMin = $request->StokMin;
        $barang->Posting = '0101/01';
        // $barang->StokMaks = $request->StokMaks;
        $barang->DiUbahOleh = $this->user->Kode;

        if ($this->user->barangs()->save($barang)){
            if (!empty($request->satuan)) {
                for ($i=0; $i < count($request->satuan); $i++) {
                    $satuan = new Satuan;
                    $satuan->Barang = $kode;
                    $satuan->Rasio = $request->satuan[$i]['Rasio'];
                    $satuan->Nama = $request->satuan[$i]['Nama'];
                    $satuan->DiUbahOleh = $this->user->Kode;
                    $this->user->satuan()->save($satuan);
                }
                // for ($k=0; $k < count($request->hrgbeli); $k++) { 
                //     $hrgbeli= new BrgHargaBeli;
                //     $hrgbeli->barangs_id = $barang->id;
                //     $hrgbeli->Rasio = $request->hrgbeli[$k]['Rasio'];
                //     $hrgbeli->MataUang = $request->hrgbeli[$k]['MataUang'];
                //     $hrgbeli->Tanggal = $request->hrgbeli[$k]['Tanggal'];
                //     $hrgbeli->Diskon = $request->hrgbeli[$k]['Diskon'];
                //     $hrgbeli->Harga = $request->hrgbeli[$k]['Harga'];
                //     $hrgbeli->updated_by = $this->user->id;
                //     $hrgbeli->save();
                // }
                // for ($x=0; $x < count($request->hrgjual); $x++) { 
                //     $hrgjual= new BrgHargaJual;
                //     $hrgjual->barangs_id = $barang->id;
                //     $hrgjual->Rasio = $request->hrgjual[$x]['Rasio'];
                //     $hrgjual->MataUang = $request->hrgjual[$x]['MataUang'];
                //     $hrgjual->Tanggal = $request->hrgjual[$x]['Tanggal'];
                //     $hrgjual->Harga = $request->hrgjual[$x]['Harga'];
                //     $hrgjual->updated_by = $this->user->id;
                //     $hrgjual->save();
                // }
            }
            // if (!empty($request->StokLimit)) {
            //     $st = new StokLimit;
            //     $st->Barang = $kode;
            //     $st->StokMinimum = $request->StokLimit['StokMinimum'];
            //     $st->StokMaksimum = $request->StokLimit['StokMaksimum'];
            //     $st->DiUbahOleh = $this->user->Kode;
            //     $this->user->StokLimit()->save($st);
            // }
            return response()->json([
                "status" => true,
                "barang" => $barang,
                "satuan" => $satuan,
                // "stok_limit" => $st
                // "hrgbeli" => $hrgbeli,
                // "hrgjual" => $hrgjual
            ], 200);
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
     * @param  \App\Models\Barangs  $barangs
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $barang = Barangs::with(
            // 'stok:Barang,Gudang,StokAkhir','stoklimit:Barang,StokMinimum,StokMaksimum',
            'satuan:Barang,Rasio,Nama','hrgjual:Barang,Rasio,MataUang,Tanggal,Harga','hrgbeli:Barang,Rasio,MataUang,Tanggal,Harga,Diskon')->find($id);
        // $satuan['satuan'] = BrgSatuan::where('barangs_id',$id)->get();
        // $hrgjual['hrgjual'] = BrgHargaJual::where('barangs_id',$id)->get();
        // $hrgbeli['hrgbeli'] = BrgHargaBeli::where('barangs_id',$id)->get();
        // $x = array();
        // array_push($x,$barangs, $satuan, $hrgbeli, $hrgjual);
        return $barang;
        // return $barangs;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Barangs  $barangs
     * @return \Illuminate\Http\Response
     */
    public function edit(Barangs $barangs)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Barangs  $barangs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            "Kode" => "required", 
        ]);
        $barang = Barangs::find($id);
        // $barang->Kode = $request->Kode;
        $barang->Nama = $request->Nama;
        $barang->Merk = $request->Merk;
        $barang->Kategori = $request->Kategori;
        $barang->PartNumber1 = $request->PartNumber1;
        $barang->PartNumber2 = $request->PartNumber2;
        $barang->Kendaraan = $request->Kendaraan;
        $barang->KodeSupplier = $request->KodeSupplier;
        $barang->Dimensi = $request->Dimensi;
        $barang->Aktif = $request->Aktif;
        $barang->Gudang = $request->Gudang;
        $barang->Memo = $request->Memo;
        // $barang->StokMin = $request->StokMin;
        // $barang->StokMaks = $request->StokMaks;
        $barang->DiUbahOleh = $this->user->Kode;
        if($barang->save()){
            // if (!empty($request->StokLimit)) {
            //     // $st = new StokLimit;
            //     // $st->Barang = $kode;
            //     // $st->StokMinimum = $request->StokLimit['StokMinimum'];
            //     // $st->StokMaksimum = $request->StokLimit['StokMaksimum'];
            //     // $st->DiUbahOleh = $this->user->Kode;
            //     // $this->user->StokLimit()->save($st);
            //     $this->user->StokLimit()->updateOrCreate(
            //         ['Barang' => $request->Kode],
            //         ['StokMinimum' => $request->StokLimit['StokMinimum'], 'StokMaksimum' => $request->StokLimit['StokMaksimum'], 'DiUbahOleh' => $this->user->Kode]
            //     );
            // }
            for ($i=0; $i < count($request->satuan); $i++) {
                $this->user->satuan()->updateOrCreate(
                    ['Barang' => $request->Kode, 'Rasio' => $request->satuan[$i]['Rasio']],
                    ['Nama' => $request->satuan[$i]['Nama'], 'DiUbahOleh' => $this->user->Kode]
                );
                // if (isset($request->satuan[$i]['id']) == false) {
                // $satuan = new BrgSatuan;
                // $satuan->barangs_id = $barang->id;
                // $satuan->Rasio = $request->satuan[$i]['Rasio'];
                // $satuan->NamaSatuan = $request->satuan[$i]['NamaSatuan'];
                // $satuan->save();
                // }else{
                // $satuan = BrgSatuan::find($request->satuan[$i]['id']);
                // $satuan->barangs_id = $barang->id;
                // $satuan->Rasio = $request->satuan[$i]['Rasio'];
                // $satuan->NamaSatuan = $request->satuan[$i]['NamaSatuan'];
                // $satuan->save();
                // }
            }
            for ($k=0; $k < count($request->hrgbeli); $k++) {
                $this->user->HargaBeli()->updateOrCreate(
                    [
                        'Barang' => $request->Kode, 
                        'Rasio' => $request->hrgbeli[$k]['Rasio'],
                        'MataUang' => $request->hrgbeli[$k]['MataUang'],
                        'Tanggal' => $request->hrgbeli[$k]['Tanggal'],
                        'Cabang' => '0101'
                    ],
                    [
                        'Harga' => $request->hrgbeli[$k]['Harga'],
                        'Diskon' => $request->hrgbeli[$k]['Diskon'],
                        'DiUbahOleh' => $this->user->Kode
                    ]
                );
                // if (isset($request->hrgbeli[$k]['id']) == false) {
                // $hrgbeli= new BrgHargaBeli;
                // $hrgbeli->barangs_id = $barang->id;
                // $hrgbeli->Rasio = $request->hrgbeli[$k]['Rasio'];
                // $hrgbeli->MataUang = $request->hrgbeli[$k]['MataUang'];
                // $hrgbeli->Tanggal = $request->hrgbeli[$k]['Tanggal'];
                // $hrgbeli->Diskon = $request->hrgbeli[$k]['Diskon'];
                // $hrgbeli->Harga = $request->hrgbeli[$k]['Harga'];
                // $hrgbeli->updated_by = $this->user->id;
                // $hrgbeli->save();
                // }else{
                // $hrgbeli = BrgHargaBeli::find($request->hrgbeli[$k]['id']);
                // $hrgbeli->barangs_id = $barang->id;
                // $hrgbeli->Rasio = $request->hrgbeli[$k]['Rasio'];
                // $hrgbeli->MataUang = $request->hrgbeli[$k]['MataUang'];
                // $hrgbeli->Tanggal = $request->hrgbeli[$k]['Tanggal'];
                // $hrgbeli->Diskon = $request->hrgbeli[$k]['Diskon'];
                // $hrgbeli->Harga = $request->hrgbeli[$k]['Harga'];
                // $hrgbeli->updated_by = $this->user->id;
                // $hrgbeli->save();
                // }
            }
            for ($x=0; $x < count($request->hrgjual); $x++) {
                $this->user->HargaJual()->updateOrCreate(
                    [
                        'Barang' => $request->Kode, 
                        'Rasio' => $request->hrgbeli[$x]['Rasio'],
                        'MataUang' => $request->hrgbeli[$x]['MataUang'],
                        'Tanggal' => $request->hrgbeli[$x]['Tanggal'],
                        'Cabang' => '0101'
                    ],
                    [
                        'Harga' => $request->hrgbeli[$x]['Harga'],
                        'DiUbahOleh' => $this->user->Kode
                    ]
                );
                // if (isset($request->hrgjual[$x]['id']) == false) { 
                // $hrgjual= new BrgHargaJual;
                // $hrgjual->barangs_id = $barang->id;
                // $hrgjual->Rasio = $request->hrgjual[$x]['Rasio'];
                // $hrgjual->MataUang = $request->hrgjual[$x]['MataUang'];
                // $hrgjual->Tanggal = $request->hrgjual[$x]['Tanggal'];
                // $hrgjual->Harga = $request->hrgjual[$x]['Harga'];
                // $hrgbeli->updated_by = $this->user->id;
                // $hrgjual->save();
                // }else{
                // $hrgjual = BrgHargaJual::find($request->hrgjual[$x]['id']);
                // $hrgjual->barangs_id = $barang->id;
                // $hrgjual->Rasio = $request->hrgjual[$x]['Rasio'];
                // $hrgjual->MataUang = $request->hrgjual[$x]['MataUang'];
                // $hrgjual->Tanggal = $request->hrgjual[$x]['Tanggal'];
                // $hrgjual->Harga = $request->hrgjual[$x]['Harga'];
                // $hrgbeli->updated_by = $this->user->id;
                // $hrgjual->save();  
                // }
            }
            return response()->json([
                "status"=>true,
                "barang" => $barang,
                "satuan" => $request->satuan,
                "hrgbeli" => $request->hrgbeli,
                "hrgjual" => $request->hrgjual,
                "stoklimit" => $request->StokLimit
            ]);
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
     * @param  \App\Models\Barangs  $barangs
     * @return \Illuminate\Http\Response
     */
    public function destroy(Barangs $barangs, $id)
    {
        $barangs = Barangs::find($id);
        $barangs->Aktif = false;
        $barangs->DiUbahOleh = $this->user->Kode;
        if ($barangs->save()){
            return response()->json([
                "status"=> true,
                "barangs"=> $barangs
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }
}

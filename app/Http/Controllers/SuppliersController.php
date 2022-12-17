<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Suppliers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuppliersController extends Controller
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
    public function index(Request $request)
    {
        // return Suppliers::all();
        $data = collect();
        // DB::table('Supplier')->orderBy('id')->chunk(100, function($datas) use ($data) {
        //     // dd($datas);
        //     foreach ($datas as $d) {
        //         $data->push($d);
        //     }
        // });
        // return response()->json(['data'=>$data]);
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
            $allColumns = DB::getSchemaBuilder()->getColumnListing('Supplier');
            $data = DB::table('Supplier');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } elseif ($dist != null) {
            $data = DB::table('Supplier')->where('Kode','like',substr($this->user->Kode,0,2).'%');
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
            $data = DB::table('Supplier');
            $conditional = $filter;
            for ($i=0; $i <= substr_count($filter,';'); $i++) { 
                if ($i == substr_count($filter,';')) {
                    $data = $data->whereIn(strtok($conditional,'='),explode(',',ltrim(substr($conditional,strrpos($conditional,'=')),'=')));
                } else {
                    $data = $data->whereIn(strtok(strtok($conditional,';'),'='),explode(',',ltrim(substr(strtok($conditional,';'),strrpos(strtok($conditional,';'),'=')),'=')));
                    $conditional = str_replace(strtok($conditional,';').';', "", $conditional);
                }
            }
            $count = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
        } elseif ($sort != 'undefined') {
            $data = DB::table('Supplier')->where('Kode','like',substr($this->user->Kode,0,2).'%');
            if (strpos($sort,'=')) {
                $ListSort = explode(',',ltrim(substr($sort, strpos($sort,'=')),'='));
                foreach (array_reverse($ListSort) as $key => $value) {
                    $field = strtok($value, ' ');
                    $updown = substr($value, strrpos($value, ' '));
                    $data = $data->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc');
                }
                $data = $data->orderBy('id')->offset($skip)->limit($take)->get();
            } else {
                $data = DB::table('Supplier')->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')->orderBy('id')->offset($skip)->limit($take)->get();
            }
            $count = DB::table('Supplier')->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
        } else {
            $count = DB::table('Supplier')->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = DB::table('Supplier')->where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('Kode')->offset($skip)->limit($take)->get();
        }
        return response()->json([
            "result" => $data,
            "count" => $count,
        ]);
    }

    public function grup()
    {
        $data = DB::table('GrupSupplier')->get(['Kode','Nama']);
        return $data;
    }

    public function suppliers(){
        // $data = DB::table('Pelanggan')->select('Kode','Nama','BadanHukum','Kota','Alamat','Telp','ContactPerson')->get();
        $data = collect();
        DB::table('Supplier')->
        where('Kode','like',substr($this->user->Kode,0,2).'%')->
        select('Kode','Nama','BadanHukum','Kota','Alamat','Telp','ContactPerson','Aktif')->orderBy('id')->chunk(500, function($datas) use ($data) {
            foreach ($datas as $d) {
                $data->push($d);
            }
        });
        return response()->json([
            "data" => $data
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $last = Suppliers::where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('id','desc')->first('Kode');
        if (is_null($last)) {
            $kode = substr($this->user->Kode,0,4).'/0001';
        } else {
            $nmr = substr($last->Kode, -4);
            $kode = substr($this->user->Kode,0,4).'/'.str_pad($nmr + 1, 4, 0, STR_PAD_LEFT);
        }
        $this->validate($request, [
            "Nama" => "required",
            
            
        ]);

        $supplier = new Suppliers;
        $supplier->Kode = $kode;
        $supplier->Nama = $request->Nama;
        $supplier->BadanHukum = $request->BadanHukum;
        $supplier->Alamat = $request->Alamat;
        $supplier->Kota = $request->Kota;
        $supplier->KodePos = $request->KodePos;
        $supplier->Negara = $request->Negara;
        $supplier->Telp = $request->Telp;
        $supplier->Fax = $request->Fax;
        $supplier->Email = $request->Email;
        $supplier->ContactPerson = $request->ContactPerson;
        $supplier->GrupSupplier = $request->GrupSupplier;
        $supplier->KreditLimit = $request->KreditLimit;
        $supplier->LamaKredit = $request->LamaKredit;
        $supplier->Memo = $request->Memo;
        // $supplier->NPWP = $request->NPWP;
        // $supplier->NPPKP = $request->NPPKP;
        // $supplier->TglPengukuhan = $request->TglPengukuhan;
        $supplier->Aktif = $request->Aktif;

        $supplier->BillFrom = empty($request->BillFrom) ? $kode : $request->BillFrom;
        $supplier->SellFrom = empty($request->SellFrom) ? $kode : $request->SellFrom;

        $supplier->DiUbahOleh = $this->user->Kode;
        $supplier->Posting = substr($this->user->Kode,0,4).'/01';

        if ($this->user->suppliers()->save($supplier)){
            return response()->json([
                "status" => true,
                "supplier" => $supplier
            ]);
        }
        // post tanpa jwt
        // if ($customer->save()){
        //     return response()->json([
        //         "status" => true,
        //         "customer" => $customer
        //     ]);
        // }
        else {
            return response()->json([
                "status" => false,
                "message" => "gagal save"
            ], 500);
        };
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Suppliers  $suppliers
     * @return \Illuminate\Http\Response
     */
    public function show(Suppliers $suppliers, $id)
    {
        $suppliers = Suppliers::find($id);
        return $suppliers;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Suppliers  $suppliers
     * @return \Illuminate\Http\Response
     */
    public function edit(Suppliers $suppliers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Suppliers  $suppliers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Suppliers $suppliers, $id)
    {
        $this->validate($request, [
            "Kode" => "required",
            
        ]);

        $suppliers = Suppliers::find($id);
        $suppliers->Kode = $request->Kode;
        $suppliers->Nama = $request->Nama;
        $suppliers->BadanHukum = $request->BadanHukum;
        $suppliers->Alamat = $request->Alamat;
        $suppliers->Kota = $request->Kota;
        $suppliers->KodePos = $request->KodePos;
        $suppliers->Negara = $request->Negara;
        $suppliers->Telp = $request->Telp;
        $suppliers->Fax = $request->Fax;
        $suppliers->Email = $request->Email;
        $suppliers->ContactPerson = $request->ContactPerson;
        $suppliers->GrupSupplier = $request->GrupSupplier;
        $suppliers->KreditLimit = $request->KreditLimit;
        $suppliers->LamaKredit = $request->LamaKredit;
        $suppliers->Memo = $request->Memo;
        // $suppliers->NPWP = $request->NPWP;
        // $suppliers->NPPKP = $request->NPPKP;
        // $suppliers->TglPengukuhan = $request->TglPengukuhan;
        $suppliers->Aktif = $request->Aktif;

        $suppliers->BillFrom = $request->BillFrom;
        $suppliers->SellFrom = $request->SellFrom;

        $suppliers->DiUbahOleh = $this->user->Kode;

        if($suppliers->save()){
            return response()->json([
                "status"=>true,
                "suppliers"=>$suppliers
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
     * @param  \App\Models\Suppliers  $suppliers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Suppliers $suppliers, $id)
    {
        $suppliers = Suppliers::find($id);
        $suppliers->Aktif = false;
        $suppliers->DiUbahOleh = $this->user->Kode;
        if ($suppliers->save()){
            return response()->json([
                "status"=> true,
                "suppliers"=> $suppliers
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }
}

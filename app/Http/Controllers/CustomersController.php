<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Customers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomersController extends Controller
{   
    protected $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }
    
    public function grup()
    {
        $data = DB::table('GrupPelanggan')->get(['Kode','Nama']);
        return $data;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function search(){

        $city = Customers::distinct()->whereNotNull('Kota')->get(['Kota']);
        // return $kota;
        $country = Customers::distinct()->whereNotNull('Negara')->get(['Negara']);
        $kota = collect();
        $negara = collect();
        foreach ($city as $c => $val )  {
            $kota->push($val['Kota']);
        }
        foreach ($country as $key => $value) {
            $negara->push($value['Negara']);
        }
        return response()->json([
            "kota" => $kota,
            "negara" => $negara
        ]);
    }

    public function asuransi(){
        // $data = DB::table('Pelanggan')->select('Kode','Nama','BadanHukum','Kota','Alamat','Telp','ContactPerson')->get();
        $data = collect();
        DB::table('Pelanggan')->where('Kode','like',substr($this->user->Kode,0,2).'%')->
        select('Kode','Nama','BadanHukum','Kota','Alamat','Telp','ContactPerson','Aktif')->orderBy('id')->chunk(500, function($datas) use ($data) {
            foreach ($datas as $d) {
                $data->push($d);
            }
        });
        return response()->json([
            "data" => $data
        ]);
    }
    
    public function index(Request $request)
    {
        // return DB::table('customers')->where($allColumns,'like','PT%')->get();
        // return gettype($allColumns);
        
        $data = collect();
        $skip = $request->query('skip');
        $take = $request->query('take');
        $sort = $request->query('sort');
        $filter = $request->query('filter');
        $field = strtok($sort, ' ');
        $updown = substr($sort, strrpos($sort, ' '));
        $dist = $request->query('dist');
        $search = $request->query('search');
        // return substr_count($dist,';');
        // return strpos($updown,'asc');
        // return gettype($updown);
        // query builder
        // DB::table('customers')->
        // select('customers.*')->orderBy('id')->chunk(2000, function($datas) use ($data) {
            //     foreach ($datas as $d) {
                //         $data->push($d);
                //     }
                // });
                $count = DB::table('Pelanggan')->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
                
        if ($search != null) {
            $allColumns = DB::getSchemaBuilder()->getColumnListing('Pelanggan');
            $data = DB::table('Pelanggan');
            foreach ($allColumns as $key => $value) {
                $data = $data->orWhere($value,'like','%'.$search.'%');
            }
            $count = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
            // return $data;
        } elseif ($dist != null) {
            $data = DB::table('Pelanggan')->where('Kode','like',substr($this->user->Kode,0,2).'%');
            $fieldDistinct = strtok($dist,';');
            // return $fieldDistinct;
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
                    // return $sub;
                    $data = $data->whereIn(strtok($sub,'='),explode(',',ltrim(substr($sub,strrpos($sub,'=')),'=')));
                    $conditional = str_replace($sub.';', "", $conditional);
                    // echo $i.'else';
                }
                // echo substr_count($dist,';');
            }
            $data = $data->distinct()->get($fieldDistinct);
            // $count = count($data);
        } elseif ($filter != 'undefined') {
            // return $filter;
            $data = DB::table('Pelanggan');
            $conditional = $filter;
            for ($i=0; $i <= substr_count($filter,';'); $i++) { 
                if ($i == substr_count($filter,';')) {
                    // return explode(',',ltrim(substr($filter,strrpos($filter,'=')),'='));
                    // return explode(',',ltrim(substr($conditional,strrpos($conditional,'=')),'='));
                    $data = $data->whereIn(strtok($conditional,'='),explode(',',ltrim(substr($conditional,strrpos($conditional,'=')),'=')));
                } else {
                    $data = $data->whereIn(strtok(strtok($conditional,';'),'='),explode(',',ltrim(substr(strtok($conditional,';'),strrpos(strtok($conditional,';'),'=')),'=')));
                    $conditional = str_replace(strtok($conditional,';').';', "", $conditional);
                }
            }
            $count = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->count();
            $data = $data->where('Kode','like',substr($this->user->Kode,0,2).'%')->offset($skip)->limit($take)->get();
            // if (strrpos($filter,'=')) {
            //     $field = strtok($filter,'=');
            //     $val = ltrim(substr($filter, strrpos($filter,'=')),'=');
            //     # code...
            //     // return $val;
            //     $data = DB::table('customers')->whereIn($field,explode(',',$val))->get();
            //     $count = count($data);
            // } else {
            //     // return $filter;
            //     $data = DB::table('customers')->distinct()->get($filter);
            // }
        } elseif ($sort != 'undefined') {
            // return $sort;
            $data = DB::table('Pelanggan')->where('Kode','like',substr($this->user->Kode,0,2).'%');
            if (strpos($sort,'=')) {
                $ListSort = explode(',',ltrim(substr($sort, strpos($sort,'=')),'='));
                foreach (array_reverse($ListSort) as $key => $value) {
                    $field = strtok($value, ' ');
                    $updown = substr($value, strrpos($value, ' '));
                    $data = $data->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc');
                }
                $data = $data->orderBy('id')->offset($skip)->limit($take)->get();
            } else {
                $data = DB::table('Pelanggan')->where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy($field, strpos($updown,'asc') ? 'asc' : 'desc')->orderBy('id')->offset($skip)->limit($take)->get();
            }
        } else {
            $data = DB::table('Pelanggan')->where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('Kode')->offset($skip)->limit($take)->get();
        }
        
        // $datas = Customers::skip($skip)->take($take)->get();
        // $data = json_decode($datas,true);
        
        // $data = $data->sortBy($field);
        // return $data;
        

        // $data = Customers::orderBy('id')->paginate(100);

        // $d = $data->groupBy('BadanHukum');
        // return $d;
        // return($d->getCollection());
        // foreach ($data as $key => $value) {
        //     echo $value;
        // }
        // dd($data);
        // $data['items'] = $data->data;
        // $data['count'] = $data->total;
        // unset($data['data']);
        // unset($data['total']);
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
        $last = Customers::where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('id', 'desc')->first('Kode');
        if (is_null($last)) {
            $kode = substr($this->user->Kode,0,4).'/0001';
        } else {
            $nmr = substr($last->Kode, -4);
            $kode = substr($this->user->Kode,0,4).'/'.str_pad($nmr + 1, 4, 0, STR_PAD_LEFT);
        }
        $this->validate($request, [
            "Nama" => "required",
            "GrupPelanggan" => "required",
        ]);

        $customer = new Customers;
        $customer->Kode = $kode;
        $customer->BillTo = empty($request->BillTo) ? $kode : $request->BillTo;
        $customer->SellTo = empty($request->SellTo) ? $kode : $request->SellTo;
        $customer->Nama = $request->Nama;
        $customer->BadanHukum = $request->BadanHukum;
        $customer->Alamat = $request->Alamat;
        $customer->Kota = $request->Kota;
        $customer->KodePos = $request->KodePos;
        $customer->Negara = $request->Negara;
        $customer->Telp = $request->Telp;
        $customer->Fax = $request->Fax;
        $customer->Email = $request->Email;
        $customer->ContactPerson = $request->ContactPerson;
        $customer->SalesPerson = substr($this->user->Kode,0,4).'/0000';
        $customer->GrupPelanggan = $request->GrupPelanggan;
        $customer->Aktif = $request->Aktif;
        $customer->KreditLimit = $request->KreditLimit;
        $customer->LamaKredit = $request->LamaKredit;
        $customer->CustSince = $request->CustSince;
        $customer->Memo = $request->Memo;
        $customer->RegularDisc = 0;
        $customer->KreditLimit = 0;
        $customer->LamaKredit = 0;
        // $customer->Asuransi = empty($request->Asuransi) ? $kode : $request->Asuransi;
        $customer->DiUbahOleh = $this->user->Kode;
        $customer->Gudang = substr($this->user->Kode,0,4).'/0001';
        $customer->Posting = substr($this->user->Kode,0,4).'/01';

        if ($this->user->customers()->save($customer)){
            return response()->json([
                "status" => true,
                "customer" => $customer
            ]);
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
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function show(Customers $customers, $id)
    {
        $customers = Customers::find($id);
        return $customers;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function edit(Customers $customers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customers $customers, $id)
    {
        $this->validate($request, [
            "Nama" => "required",
            "GrupPelanggan" => "required",
        ]);
        $customers = Customers::find($id);
        $customers->Kode = $request->Kode;
        $customers->Nama = $request->Nama;
        $customers->BadanHukum = $request->BadanHukum;
        $customers->Alamat = $request->Alamat;
        $customers->Kota = $request->Kota;
        $customers->KodePos = $request->KodePos;
        $customers->Negara = $request->Negara;
        $customers->Telp = $request->Telp;
        $customers->Fax = $request->Fax;
        $customers->Email = $request->Email;
        $customers->ContactPerson = $request->ContactPerson;
        // $customers->SalesPerson = $request->SalesPerson;
        $customers->GrupPelanggan = $request->GrupPelanggan;
        $customers->Aktif = $request->Aktif;
        $customers->KreditLimit = $request->KreditLimit;
        $customers->LamaKredit = $request->LamaKredit;
        $customers->CustSince = $request->CustSince;
        $customers->Memo = $request->Memo;
        // $customers->Asuransi = $request->Asuransi;
        $customers->BillTo = $request->BillTo;
        $customers->SellTo = $request->SellTo;
        $customers->DiUbahOleh = $this->user->Kode;
        if($customers->save()){
            return response()->json([
                "status"=>true,
                "customers"=>$customers
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
     * @param  \App\Models\Customers  $customers
     * @return \Illuminate\Http\Response
     */
    public function destroy(Customers $customers, $id)
    {
        $customers = Customers::find($id);
        $customers->Aktif = false;
        $customers->DiUbahOleh = $this->user->Kode;
        if ($customers->save()){
            return response()->json([
                "status"=> true,
                "customers"=> $customers
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }
}

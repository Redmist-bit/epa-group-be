<?php

namespace App\Http\Controllers;
use JWTAuth;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class UserController extends Controller
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
    public function index()
    {
        $data = collect();
        
        // query builder
        User::where('Kode','like',substr($this->user->Kode,0,2).'%')->orderBy('id')->chunk(2000, function($datas) use ($data) {
            foreach ($datas as $d) {
                $data->push($d);
            }
        });
        return response()->json($data);
        
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        $user->NamaLengkap = $request->NamaLengkap;
        $user->Alamat = $request->Alamat;
        $user->Telp = $request->Telp;
        $user->Email = $request->Email;
        $user->Jabatan = $request->Jabatan;
        $user->Departemen = $request->Departemen;
        $user->Aktif = $request->Aktif;
        $user->JenisKelamin = $request->JenisKelamin;
        $user->TanggalLahir = $request->TanggalLahir;
        $user->TanggalMulaiKerja = $request->TanggalMulaiKerja;
        $user->TanggalBerhentiKerja = $request->TanggalBerhentiKerja;
        $user->Keterangan = $request->Keterangan;
        $user->DiUbahOleh = $this->user->Kode;
        // $user->Gudang = $request->Gudang;
        $user->save();

        return response()->json([
            'status' => true,
            'data' => $user
        ],200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->Aktif = false;
        if ($user->save()){
            return response()->json([
                "status"=> true,
                "user"=> $user
            ]);
        } else {
            return response()->json([
                "status"=> false,
                "Message"=> "gagal delete"
            ]);
        }
    }

    public function resetPwd($id)
    {
        $user = User::find($id);
        $newPass = $user->Nama.rand(1000,9999);
        $user->password = bcrypt($newPass);
        if ($user->save()) {
            return response()->json([
                "status"=> true,
                "new_password"=> $newPass
            ]);
        }
    }
}

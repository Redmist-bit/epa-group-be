<?php

namespace App\Http\Controllers;

use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Jabatan;

class AuthController extends Controller
{
    public $loginAfterSignUp = true;

    public function login(Request $request)
    {
        $credentials = $request->only("Nama","password");
        // return $credentials;
        $token = null;
        if(!$token = JWTAuth::attempt($credentials)){
            return response()->json([
                "status" => false,
                "message" => "Unauthorized"
            ]);
        }
       $currentUser = JWTAuth::user();
        return response()->json([
            "status"=>true,
            "token"=>$token,
            "user"=>$currentUser
        ]);
    }

    
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),[
            "Nama" => "required|string|unique:User,Nama",
            // "Email" => "required|email|unique:User,Email",
            "password" => "required|string|min:4|max:20"
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = new User();
        $user->Kode = $request->Kode;
        $user->Nama = $request->Nama;
        $user->NamaLengkap = $request->nama_lengkap;
        $user->Alamat = $request->alamat;
        $user->Telp = $request->telp;
        $user->Email = $request->Email;
        $user->Jabatan = $request->Jabatan;
        $user->password = bcrypt($request->password);
        $user->Aktif = $request->Aktif;
        $user->JenisKelamin = $request->jenis_kelamin;
        $user->TanggalLahir = $request->tanggal_lahir;
        $user->TanggalMulaiKerja = $request->tanggal_mulai_kerja;
        $user->TanggalBerhentiKerja = $request->tanggal_berhenti_kerja;
        $user->Keterangan = $request->keterangan;
        $user->DiBuatOleh = $request->DiBuatOleh;
        $user->DiUbahOleh = $request->DiUbahOleh;
        $user->save();
        
        if ($this->loginAfterSignUp) {
            return $this->login($request);
        }

        return response()->json([
            "status"=> true,
            "user" => $user
        ]);
    }

    public function logout(Request $request)
    {
        $this->validate($request, [
            "token" => "required"
        ]);

        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                "status" => true,
                "message" => "User logged out berhasil"
            ]);
        } catch (JWTEXeception $exception) {
            return response()->json([
                "status" => false,
                "message" => "user tidak gagal logout"
            ]);
        }
    }

    public function ResetPassword(Request $req)
    {
        
        $validator = Validator::make($req->all(),[
            'Nama' => 'required',
            'password' => 'required',
            'new_password' => 'required|min:6|different:password',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 500);
        }
        $name = $req->Nama;
        $user = User::where('Nama', '=', $name)->first();
        if ($user == null) {
            return response()->json([
                'status'=> false,
                'message'=> 'username tidak ditemukan'
            ],500);
        }
        // Cek old password same or not
        if (Hash::check($req->password, $user->password)) {
            // return 'true';
            $user->password = bcrypt($req->new_password);
            
            if ($user->save()) {
                return response()->json([
                    "message" => "berhasil mengubah kata sandi"
                ],200);
            } else {
                return response()->json([
                    "message" => "gagal mengubah kata sandi"
                ],500);
            }
        } else {
            return response()->json([
                "message"=>'email dan kata sandi tidak cocok'
            ],500);
        }
    }
}

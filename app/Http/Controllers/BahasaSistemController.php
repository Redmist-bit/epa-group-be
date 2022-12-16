<?php

namespace App\Http\Controllers;
use App\Models\BahasaSistem;
use Illuminate\Http\Request;

class BahasaSistemController extends Controller
{
    public function index(Request $req, $bahasa)
    {
        // $all= BahasaSistem::where('bahasa', $bahasa)->get();
        $a= BahasaSistem::where('bahasa', $bahasa)->get(['item','text','komponen']);
        $a = collect($a)->unique('komponen')->flatten();
        foreach ($a as $key => $value) {
            $value->MainTitle = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','MainTitle')->get('text')[0]['text'] ?? null;
            $value->MainTitle2 = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','MainTitle2')->get('text')[0]['text'] ?? null;
            $value->LoginTitle = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','LoginTitle')->get('text')[0]['text'] ?? null;
            $value->BtnTambah = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','BtnTambah')->get('text')[0]['text'] ?? null;
            $value->DialogTitleTambah = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','DialogTitleTambah')->get('text')[0]['text'] ?? null;
            $value->DialogTitleEdit = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','DialogTitleEdit')->get('text')[0]['text'] ?? null;
            $value->BtnSimpan = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','BtnSimpan')->get('text')[0]['text'] ?? null;
            $value->BtnBatal = $value->where('bahasa',$bahasa)->where('komponen',$value->komponen)->where('item','BtnBatal')->get('text')[0]['text'] ?? null;
            unset($value->item,$value->text);
        }
        return $a;
    }

    // public function bahasa(Request $req, $bahasa){
    //     // $a = BahasaSistem::where('bahasa', $bahasa)->get();

    //     //translate Gudang
    //     $GudangMainTitle = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Gudang')->where('item','MainTitle')->get();
    //     $GudangBtnTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Gudang')->where('item','BtnTambah')->get();
    //     $GudangDialogTitleTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Gudang')->where('item','DialogTitleTambah')->get();
    //     $GudangDialogTitleEdit = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Gudang')->where('item','DialogTitleEdit')->get();

    //     //translate Pelanggan
    //     $PelangganMainTitle = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Pelanggan')->where('item','MainTitle')->get();
    //     $PelangganBtnTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Pelanggan')->where('item','BtnTambah')->get();
    //     $PelangganDialogTitleTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Pelanggan')->where('item','DialogTitleTambah')->get();
    //     $PelangganDialogTitleEdit = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Pelanggan')->where('item','DialogTitleEdit')->get();

    //     //translate Supplier
    //     $SupplierMainTitle = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Supplier')->where('item','MainTitle')->get();
    //     $SupplierBtnTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Supplier')->where('item','BtnTambah')->get();
    //     $SupplierDialogTitleTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Supplier')->where('item','DialogTitleTambah')->get();
    //     $SupplierDialogTitleEdit = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Supplier')->where('item','DialogTitleEdit')->get();
        
    //     //translate Mekanik
    //     $MekanikMainTitle = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Mekanik')->where('item','MainTitle')->get();
    //     $MekanikBtnTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Mekanik')->where('item','BtnTambah')->get();
    //     $MekanikDialogTitleTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Mekanik')->where('item','DialogTitleTambah')->get();
    //     $MekanikDialogTitleEdit = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Mekanik')->where('item','DialogTitleEdit')->get();

    //     //translate Kendaraan
    //     $KendaraanMainTitle = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Kendaraan')->where('item','MainTitle')->get();
    //     $KendaraanBtnTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Kendaraan')->where('item','BtnTambah')->get();
    //     $KendaraanDialogTitleTambah = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Kendaraan')->where('item','DialogTitleTambah')->get();
    //     $KendaraanDialogTitleEdit = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Kendaraan')->where('item','DialogTitleEdit')->get();
        
    //     //translate Login
    //     $LoginMainTitle = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Login')->where('item','MainTitle')->get();
    //     $LoginMainTitle2 = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Login')->where('item','Maintitle2')->get();
    //     $LoginTitle = BahasaSistem::where('bahasa', $bahasa)->where('komponen','Login')->where('item','Logintitle')->get();

    //     return response()->json(
    //             //translate Gudang
    //             ["Gudang" => ["MainTitle" => $GudangMainTitle[0]['text'],
    //                          "BtnTambah" => $GudangBtnTambah[0]['text'],
    //                          "DialogTitleTambah" => $GudangDialogTitleTambah[0]['text'],
    //                          "DialogTitleEdit" => $GudangDialogTitleEdit[0]['text'],
    //                         ],
                
    //             //translate Pelanggan
    //             "Pelanggan" => ["MainTitle" => $PelangganMainTitle[0]['text'],
    //                         "BtnTambah" => $PelangganBtnTambah[0]['text'],
    //                         "DialogTitleTambah" => $PelangganDialogTitleTambah[0]['text'],
    //                         "DialogTitleEdit" => $PelangganDialogTitleEdit[0]['text'],
    //                         ],

    //             //translate Supplier
    //             "Supplier" => ["MainTitle" => $SupplierMainTitle[0]['text'],
    //                         "BtnTambah" => $SupplierBtnTambah[0]['text'],
    //                         "DialogTitleTambah" => $SupplierDialogTitleTambah[0]['text'],
    //                         "DialogTitleEdit" => $SupplierDialogTitleEdit[0]['text'],
    //                         ],

    //             //translate Mekanik
    //             "Mekanik" => ["MainTitle" => $MekanikMainTitle[0]['text'],
    //                         "BtnTambah" => $MekanikBtnTambah[0]['text'],
    //                         "DialogTitleTambah" => $MekanikDialogTitleTambah[0]['text'],
    //                         "DialogTitleEdit" => $MekanikDialogTitleEdit[0]['text'],
    //                         ],

    //             //translate Kendaraan
    //             "Kendaraan" => ["MainTitle" => $KendaraanMainTitle[0]['text'],
    //                         "BtnTambah" => $KendaraanBtnTambah[0]['text'],
    //                         "DialogTitleTambah" => $KendaraanDialogTitleTambah[0]['text'],
    //                         "DialogTitleEdit" => $KendaraanDialogTitleEdit[0]['text'],
    //                         ],

    //             //translate Login
    //             "Login" => ["MainTitle" => $LoginMainTitle[0]['text'],
    //                         "MainTitle2" => $LoginMainTitle2[0]['text'],
    //                         "LoginTitle" => $LoginTitle[0]['text'],
    //                         ],
    //             ]);
    // }
}

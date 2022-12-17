<?php

namespace App\Http\Controllers;
use JWTAuth;
use App\Models\Menu;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class MenuController extends Controller
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
        return Menu::all();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function parent()
    {
        $data = Menu::with('children:id,parent,nama')->where('parent',0)->get(['id','parent','nama']);
        return response()->json(['data' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = new Menu;
        $data->parent = $request->parent;
        $data->nama = $request->nama;
        $data->link = $request->link;
        $data->NamaComponent = $request->NamaComponent;
        $data->crudAction = $request->crudAction;
        $data->contextMenuAction = $request->contextMenuAction;
        $data->printAction = $request->printAction;
        $data->icon = $request->icon;
        $data->idn = $request->idn;
        $data->eng = $request->eng;
        $data->save();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function show(Menu $menu,$id)
    {
        $akses = Jabatan::with('Menus')->find($id);
        return response()->json([
            'status' => 'success',
            'data' => $akses
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function edit(Menu $menu)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Menu $menu,$id)
    {
        $m=[];
        $menu = $request->Menu;
        foreach ($menu as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $key => $val) {
                    $m[$key]=[
                        "permission" =>implode(',',array_filter($val, function($v){ return strlen($v) == 1; })),
                        "context_menu" =>implode(',',array_filter($val, function($v){ return strlen($v) > 1 && !str_contains($v,'cetak'); })),
                        "print_out" =>implode(',',array_filter($val, function($v){ return str_contains($v,'cetak'); })),
                    ];
                }
            } else {
                $m[$value]=["permission"=>""];
            }
        }
        // return $m;
        $jabatan = Jabatan::find($id);
        $jabatan->Menus()->sync($m);
        return 'success';
    }

    public function updateMenu(Request $request,$id)
    {
        $data = Menu::find($id);
        $data->parent = $request->parent;
        $data->nama = $request->nama;
        $data->link = $request->link;
        $data->NamaComponent = $request->NamaComponent;
        $data->crudAction = $request->crudAction;
        $data->contextMenuAction = $request->contextMenuAction;
        $data->printAction = $request->printAction;
        $data->icon = $request->icon;
        $data->idn = $request->idn;
        $data->eng = $request->eng;
        // return 'disini';
        $data->save();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function destroy(Menu $menu)
    {
        //
    }
}

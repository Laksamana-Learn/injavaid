<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Menu $menu)
    {
        $user = Auth::user();

        if ($user->level_id === 2 || $user->level_id === 3) {
            return redirect()->back();
        }

        // Mengambil kategori unik dari menu
        $categories = $menu->select('category')->distinct()->pluck('category');

        return view('menu.index', [
            'categories' => $categories,
            'foods' => $menu->where('category', 'food')->latest()->get(),
            'drinks' => $menu->where('category', 'drink')->latest()->get(),
            'souvenirs' => $menu->where('category', 'souvenir')->latest()->get(),
            'packagings' => $menu->where('category', 'packaging')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user = Auth::user();

        if ($user->level_id === 2 || $user->level_id === 3) {
            return redirect()->back();
        }

        // Mengambil kategori unik
        $categories = Menu::select('category')->distinct()->pluck('category');

        return view('menu.add', [
            'categories' => $categories
        ]);
    }
    public function destroy($id)
{
    $menu = Menu::findOrFail($id); // Cari menu berdasarkan ID
    $menu->delete(); // Hapus menu dari database

    return redirect()->route('menu.index')->with('success', 'Menu berhasil dihapus!');
}


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validateddata = $request->validate([
           'name' => 'required|min:3',
           'modal' => 'required|regex:/([0-9]+[.,]*)+/',
           'price' => 'required|regex:/([0-9]+[.,]*)+/|gte:modal',
           'category' => 'required',
           'image' => 'required|image|file|max:3048',
           'description' => 'required'
        ]);

        $validateddata["modal"] = filter_var($request->modal, FILTER_SANITIZE_NUMBER_INT);
        $validateddata["price"] = filter_var($request->price, FILTER_SANITIZE_NUMBER_INT);
        $validateddata["picture"] = $request->file('image')->store('menu', 'public');


        Menu::create($validateddata);

        $activity = [
            'user_id' => Auth::id(),
            'action' => 'added a menu ' . strtolower($request->name)
        ];

        ActivityLog::create($activity);
        return redirect('/menu')->with('success', 'Produk Berhasil Di Tambahkan !');

        if ($validator->fails()) {
    return redirect()->back()->withErrors($validator)->withInput();
}

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Menu  $menu
     * @return \Illuminate\Http\Response
     */
    public function edit(Menu $menu)
    {
        $user = Auth::user();

        if ($user->level_id === 2 || $user->level_id === 3) {
            return redirect()->back();
        }

        // Mengambil kategori unik
        $categories = Menu::select('category')->distinct()->pluck('category');

        return view('menu.edit', [
            'menu' => $menu,
            'categories' => $categories
        ]);
    }
}



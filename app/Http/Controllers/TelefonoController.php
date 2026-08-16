<?php

namespace App\Http\Controllers;

use App\Models\Telefono;
use Illuminate\Routing\Controller;

class TelefonoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Ver telefonos')->only('index');
    }

    public function index()
    {
        $telefonos = Telefono::all();
        return view('telefonos.index', compact('telefonos'));
    }
}

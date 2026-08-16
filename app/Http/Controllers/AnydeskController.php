<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Anydesk;

class AnydeskController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:Ver anydesks')->only('index');
        $this->middleware('permission:Crear anydesks')->only('store');
        $this->middleware('permission:Editar anydesks')->only('update');
        $this->middleware('permission:Eliminar anydesks')->only('destroy');
    }

    public function index()
    {
        $anydesks = Anydesk::orderBy('torre')->get();
        return view('anydesks.index', compact('anydesks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'required|string|max:50|unique:anydesks,codigo',
            'contrasena' => 'required|string|max:100',
            'torre' => 'required|string|max:100',
        ]);

        Anydesk::create($request->all());

        return redirect()->route('anydesks.index')->with('success', 'AnyDesk registrado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $anydesk = Anydesk::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:100',
            'codigo' => 'required|string|max:50|unique:anydesks,codigo,' . $id,
            'contrasena' => 'required|string|max:100',
            'torre' => 'required|string|max:100',
        ]);

        $anydesk->update($request->all());

        return redirect()->route('anydesks.index')->with('success', 'AnyDesk actualizado correctamente.');
    }

    public function destroy($id)
    {
        Anydesk::findOrFail($id)->delete();

        return redirect()->route('anydesks.index')->with('success', 'AnyDesk eliminado correctamente.');
    }
}

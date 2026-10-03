<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Anydesk;
use App\Models\AnydeskImagen;

class AnydeskController extends Controller
{
    /** Disco privado: las imagenes solo salen por el metodo imagen(), detras del permiso. */
    private const DISCO = 'local';

    public function __construct()
    {
        $this->middleware('permission:Ver anydesks')->only(['index', 'imagen']);
        $this->middleware('permission:Crear anydesks')->only('store');
        $this->middleware('permission:Editar anydesks')->only('update');
        $this->middleware('permission:Eliminar anydesks')->only('destroy');
    }

    public function index()
    {
        $anydesks = Anydesk::with('imagenes')->orderBy('torre')->get();
        return view('anydesks.index', compact('anydesks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->reglas(), $this->mensajes());

        $anydesk = Anydesk::create([
            'codigo' => $data['codigo'],
            'contrasena' => $data['contrasena'],
            'torre' => $data['torre'],
        ]);

        $this->guardarImagenes($request, $anydesk);

        return redirect()->route('anydesks.index')->with('success', 'AnyDesk registrado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $anydesk = Anydesk::findOrFail($id);

        $data = $request->validate(
            $this->reglas($id) + [
                'eliminar_imagenes' => 'array',
                'eliminar_imagenes.*' => 'integer',
            ],
            $this->mensajes()
        );

        $anydesk->update([
            'codigo' => $data['codigo'],
            'contrasena' => $data['contrasena'],
            'torre' => $data['torre'],
        ]);

        // Acotado a este acceso: evita borrar imagenes de otro registro manipulando los ids.
        if (!empty($data['eliminar_imagenes'])) {
            foreach ($anydesk->imagenes()->whereIn('id', $data['eliminar_imagenes'])->get() as $imagen) {
                Storage::disk(self::DISCO)->delete($imagen->ruta);
                $imagen->delete();
            }
        }

        $this->guardarImagenes($request, $anydesk);

        return redirect()->route('anydesks.index')->with('success', 'AnyDesk actualizado correctamente.');
    }

    public function destroy($id)
    {
        $anydesk = Anydesk::with('imagenes')->findOrFail($id);

        foreach ($anydesk->imagenes as $imagen) {
            Storage::disk(self::DISCO)->delete($imagen->ruta);
        }

        // La llave foranea borra las filas en cascada.
        $anydesk->delete();

        Storage::disk(self::DISCO)->deleteDirectory("anydesks/{$anydesk->id}");

        return redirect()->route('anydesks.index')->with('success', 'AnyDesk eliminado correctamente.');
    }

    /**
     * Entrega el archivo de una imagen. Viven en un disco privado, asi que esta
     * es la unica via de acceso y queda detras del permiso "Ver anydesks".
     */
    public function imagen(AnydeskImagen $imagen)
    {
        abort_unless(Storage::disk(self::DISCO)->exists($imagen->ruta), 404);

        return Storage::disk(self::DISCO)->response($imagen->ruta);
    }

    private function reglas($ignorarId = null): array
    {
        $unico = 'unique:anydesks,codigo' . ($ignorarId ? ',' . $ignorarId : '');

        return [
            'codigo' => 'required|string|max:50|' . $unico,
            'contrasena' => 'required|string|max:100',
            'torre' => 'required|string|max:100',
            'imagenes' => 'array|max:8',
            'imagenes.*' => 'image|mimes:jpg,jpeg,png,webp|max:4096',
        ];
    }

    private function mensajes(): array
    {
        return [
            'imagenes.max' => 'Puedes subir como maximo 8 imagenes por envio.',
            'imagenes.*.image' => 'Cada archivo debe ser una imagen.',
            'imagenes.*.mimes' => 'Formatos permitidos: JPG, PNG o WEBP.',
            'imagenes.*.max' => 'Cada imagen debe pesar menos de 4 MB.',
        ];
    }

    private function guardarImagenes(Request $request, Anydesk $anydesk): void
    {
        if (!$request->hasFile('imagenes')) {
            return;
        }

        foreach ($request->file('imagenes') as $archivo) {
            $nombre = Str::slug(pathinfo($archivo->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'imagen';

            $nombreFinal = time() . '_' . Str::random(6) . '_' . $nombre . '.' . $archivo->getClientOriginalExtension();

            $anydesk->imagenes()->create([
                'ruta' => $archivo->storeAs("anydesks/{$anydesk->id}", $nombreFinal, self::DISCO),
            ]);
        }
    }
}

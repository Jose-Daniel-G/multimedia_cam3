<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Evento;
use Illuminate\Http\Request;
use Carbon\Carbon;

class EventoController extends Controller
{
    public function index()
    {
        return view('eventos.index');
    }
    public function create()
    {
        //
    }
    public function store(Request $request)
    {
        $request->validate(['title' => 'required', 'descripcion' => 'required', 'start' => 'required|date', 'end' => 'required|date',]);
        Evento::create($request->all());

        return response()->json(['message' => 'Evento creado correctamente']);
    }
    public function show(Evento $evento)
    {
        $evento = Evento::all();
        return response()->json($evento);
    }
    public function edit($id)
    {
        $evento = Evento::find($id);
        // $evento->start = Carbon::createFromFormat('Y-m-d H:i:s',$evento->start)->format('Y-m-d');
        // $evento->end = Carbon::createFromFormat('Y-m-d H:i:s',$evento->end)->format('Y-m-d');
        return response()->json($evento);
    }
    public function update(Request $request, Evento $evento)
    {
        $validatedData = $request->validate(['title' => 'required','descripcion' => 'required','start' => 'required|date','end' => 'required|date',]);

        $evento->update($validatedData);

        return response()->json(['message' => 'Evento actualizado correctamente']);
    }

    public function destroy($id)
    {
        $evento = Evento::find($id);
        $evento->delete();
    
        return response()->json(['message' => 'Evento eliminado exitosamente']);
    }
}

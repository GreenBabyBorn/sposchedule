<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\Request;

class BuildingController extends Controller
{
    // Получение всех корпусов
    public function index()
    {
        return Building::orderBy('name', )->get();
    }

    // Создание нового корпуса
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:buildings,name',
            'location' => 'nullable|string|max:255',
        ]);

        $building = Building::create($validatedData);
        return response()->json($building, 201);
    }

    // Получение корпуса по имени (name)
    public function show($name)
    {
        $building = Building::findOrFail($name);
        return response()->json($building);
    }

    // Обновление корпуса по имени (name)
    public function update(Request $request, $name)
    {
        $building = Building::findOrFail($name);

        $validatedData = $request->validate([
            'location' => 'required|string|max:255',
        ]);

        $building->update($validatedData);
        return response()->json($building);
    }

    // Удаление корпуса по имени (name)
    public function destroy($name)
    {
        $building = Building::findOrFail($name);
        $building->delete();
        return response()->json(null, 204);
    }
}

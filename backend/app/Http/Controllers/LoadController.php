<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreLoadRequest;
use App\Http\Requests\UpdateLoadRequest;
use App\Http\Resources\LoadResource;
use App\Models\Load;

class LoadController extends Controller
{
    /**
     * Display a listing of the resource.
     */public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Load::with(['semester', 'teacher', 'subject', 'group']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('semester', function ($subQuery) use ($search) {
                    $subQuery->where('years', 'ilike', "%{$search}%");
                })->orWhereHas('teacher', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'ilike', "%{$search}%");
                })->orWhereHas('subject', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'ilike', "%{$search}%");
                })->orWhereHas('group', function ($subQuery) use ($search) {
                    $subQuery->where('name', 'ilike', "%{$search}%");
                })->orWhere('hours', 'like', "%{$search}%");
            });
        }

        $query = $query->orderBy('id', 'desc')->get();

        return LoadResource::collection($query);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLoadRequest $request)
    {
        $load = Load::create($request->validated());
        return response()->json($load, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Load $load)
    {
        $load->load(['semester','teacher', 'subject', 'group']);
        return response()->json($load);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLoadRequest $request, Load $load)
    {
        $load->update($request->validated());
        return response()->json($load);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Load $load)
    {
        $load->delete();
        return response()->noContent();
    }
}

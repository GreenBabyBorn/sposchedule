<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Group\StoreGroupRequest;
use App\Http\Requests\Group\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Models\Group;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return GroupResource::collection(Group::all());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGroupRequest $request)
    {
        $name = $request->name;

        $group = Group::create([
            ...$request->all(),
            "name" => $name,

        ]);
        $group->refresh();
        return new GroupResource($group);
    }

    /**
     * Display the specified resource.
     */
    public function show(Group $group)
    {
        return new GroupResource($group);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $group->update($request->all());
        $group->refresh();
        return new GroupResource($group);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Group $group)
    {
        $group->delete();
        return response()->noContent();
    }
}

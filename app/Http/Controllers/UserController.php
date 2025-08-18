<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::all();
        return response()->json($user);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'=>'required|string|max:255|unique:users,name',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:8',
            'role'=>'required|in:admin,author,user',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        return response()->json($user, 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        
        if (auth()->id() !== $user->id && auth()->user()->role !== 'admin') 
        {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name'=>'string|max:255|unique:users,name',
            'email' => 'email|unique:users,email,' . $user->id, // dozvoli ovaj email ako pripada korisniku da ne javlja gresku
            'password'=>'nullable|string|min:8',
            'role'=>'in:admin,author,user',
        ]);


        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return response()->json($user);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);

        if (auth()->id() !== $user->id && auth()->user()->role !== 'admin') 
        {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $user->delete();

        return response()->json(null, 204);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Outlet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OutletController extends Controller
{
    public function index() {
        $outlets = Outlet::selectRaw('outlets.id, outlets.name, image, description, users.username as owner')
        ->join('users', 'users.id', '=', 'outlets.owner_id')->get();
        $data['outlets'] = $outlets;
        return response()->json([
            "status" => true,
            "message" => "Berhasil memuat gerai",
            "body" => $outlets,
        ], 200);
    }

    // Belum ada menu
    public function show($id) {
        $outlet = Outlet::find($id);
        if ($outlet) {
            return response()->json([
                "status" => true,
                "message" => "Berhasil memuat gerai",
                "body" => $outlet->getData(),
            ], 200);
        }
        return response()->json([
            "status" => false,
            "message" => "Gerai tidak ditemukan",
            "body" => [],
        ], 404);
    }

    public function store(Request $request) {
        $val = Validator::make($request->all(), [
            "name" => "required",
            "image" => "required|image",
            "description" => "required",
            "owner_id" => "required|exists:users,id",
        ]);
        if ($val->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Inputan tidak benar",
                "body" => $val->errors(),
            ], 403);
        }
        $filename = '';
        if ($file = $request->file('image')) {
            $dir = 'uploads/outlet';
            $filename = time().rand(1111,9999).'.'.$file->getClientOriginalExtension();
            // dd($filename);
            $file->move($dir, $filename);
        }
        $outlet = Outlet::create([
            "name" => $request->name,
            "image" => $filename,
            "description" => $request->description,
            "owner_id" => $request->owner_id,
        ]);
        return response()->json([
            "status" => true,
            "message" => "Berhasil menambah gerai",
            "body" => $outlet,
        ], 200);
    }

    public function update(Request $request, $id) {
        $val = Validator::make($request->all(), [
            "name" => "required",
            // "image" => "required|image",
            "description" => "required",
            "owner_id" => "required|exists:users,id",
        ]);
        if ($val->fails()) {
            return response()->json([
                "status" => false,
                "message" => "Inputan tidak benar",
                "body" => $val->errors(),
            ], 403);
        }
        $outlet = Outlet::find($id);
        $filename = $outlet->image;
        if ($file = $request->file('image')) {
            $dir = 'uploads/outlet';
            if (file_exists(public_path($dir.$filename))) {
                unlink(public_path($dir.$filename));
            }
            $filename = time().rand(1111,9999).'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
        }
        $outlet->update([
            "name" => $request->name,
            "image" => $filename,
            "description" => $request->description,
            "owner_id" => $request->owner_id,
        ]);
        return response()->json([
            "status" => true,
            "message" => "Berhasil mengubah data gerai",
            "body" => $outlet->getData(),
        ], 200);
    }

    public function destroy($id) {
        $outlet = Outlet::find($id);
        if ($outlet) {
            $dir = 'uploads/outlet/';
            $filename = $outlet->image;
            if (file_exists(public_path($dir.$filename))) {
                unlink(public_path($dir.$filename));
            }
            $outlet->delete();
            return response()->json([
                "status" => true,
                "message" => "Berhasil menghapus gerai",
                "body" => $outlet,
            ], 200);
        }
        return response()->json([
            "status" => false,
            "message" => "Gerai tidak ditemukan",
            "body" => [],
        ], 404);
    }
}

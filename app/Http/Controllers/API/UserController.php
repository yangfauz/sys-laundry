<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Http\Resources\UserCollection;
use DB;
use File;

class UserController extends Controller
{
    public function index()
	{
	    $users = User::with(['outlet'])->orderBy('created_at', 'DESC')->courier();
	    if (request()->q != '') {
	        $users = $users->where('name', 'LIKE', '%' . request()->q . '%');
	    }
	    $users = $users->paginate(10);
	    return new UserCollection($users);
	}

	public function store(Request $request)
	{
	    //VALIDASI
	    $this->validate($request, [
	        'name' => 'required|string|max:150',
	        'email' => 'required|email|unique:users,email',
	        'password' => 'required|min:6|string',
	        'outlet_id' => 'required|exists:outlets,id',
	        'photo' => 'required|image'
	    ]);

	    DB::beginTransaction();
	    try {
	        $name = NULL;
	        //APABILA ADA FILE YANG DIKIRIMKAN
	        if ($request->hasFile('photo')) {
	            //MAKA FILE TERSEBUT AKAN DISIMPAN KE STORAGE/APP/PUBLIC/COURIERS
	            $file = $request->file('photo');
	            $name = $request->email . '-' . time() . '.' . $file->getClientOriginalExtension();
	            $file->storeAs('public/couriers', $name);
	        }
	        //BUAT DATA BARUNYA KE DATABASE
	        User::create([
	            'name' => $request->name,
	            'email' => $request->email,
	            'password' => $request->password,
	            'role' => $request->role,
	            'photo' => $name,
	            'outlet_id' => $request->outlet_id,
	            'role' => 3
	        ]);
	        DB::commit();
	        return response()->json(['status' => 'success'], 200);
	    } catch (\Exception $e) {
	        DB::rollback();
	        return response()->json(['status' => 'error', 'data' => $e->getMessage()], 200);
	    }
	}

	public function edit($id)
	{
	    $user = User::find($id); //MENGAMBIL DATA BERDASARKAN ID
	    return response()->json(['status' => 'success', 'data' => $user], 200);
	}

	public function update(Request $request, $id)
	{
	    //VALIDASI DATA
	    $this->validate($request, [
	        'name' => 'required|string|max:150',
	        'email' => 'required|email',
	        'password' => 'nullable|min:6|string',
	        'outlet_id' => 'required|exists:outlets,id',
	        'photo' => 'nullable|image'
	    ]);

	    try {
	        $user = User::find($id); //MENGAMBIL DATA YANG AKAN DI UBAH
	        //JIKA FORM PASSWORD TIDAK DI KOSONGKAN, MAKA PASSWORD AKAN DIPERBAHARUI
	        $password = $request->password != '' ? bcrypt($request->password):$user->password;
	        $filename = $user->photo; //NAMA FILE FOTO SEBELUMNYA

	        //JIKA ADA FILE BARU YANG DIKIRIMKAN
	        if ($request->hasFile('photo')) {
	            //MAKA FOTO YANG LAMA AKAN DIGANTI
	            $file = $request->file('photo');
	            //DAN FILE FOTO YANG LAMA AKAN DIHAPUS
	            File::delete(storage_path('app/public/couriers/' . $filename));
	            $filename = $request->email . '-' . time() . '.' . $file->getClientOriginalExtension();
	            $file->storeAs('public/couriers', $filename);
	        }
	        
	        //PERBAHARUI DATA YANG ADA DI DATABASE
	        $user->update([
	            'name' => $request->name,
	            'password' => $password,
	            'photo' => $filename,
	            'outlet_id' => $request->outlet_id
	        ]);
	        return response()->json(['status' => 'success'], 200);
	    } catch (\Exception $e) {
	        return response()->json(['status' => 'error', 'data' => $e->getMessage()], 200);
	    }
	}

	public function destroy($id)
	{
	    $user = User::find($id); //MENGAMBIL DATA YANG AKAN DIHAPUS
	    File::delete(storage_path('app/public/couriers/' . $user->photo)); //MENGHAPUS FILE FOTO
	    $user->delete(); //MENGHAPUS DATANYA
	    return response()->json(['status' => 'success']);
	}
}

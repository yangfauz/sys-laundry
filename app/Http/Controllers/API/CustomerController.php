<?php

namespace App\Http\Controllers\API;
use App\Http\Resources\CustomerCollection;
use App\Customer;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function index()
	{
	    //GET CUSTOMER DENGAN MENGURUTKAN DATANYA BERDASARKAN CREATED_AT
	    $customers = Customer::with(['courier'])->orderBy('created_at', 'DESC');
	    if (request()->q != '') { //JIKA DATA PENCARIAN ADA
	        $customers = $customers->where('name', 'LIKE', '%' . request()->q . '%'); //MAKA BUAT FUNGSI FILTERING DATA BERDASARKAN NAME
	    }
	    return new CustomerCollection($customers->paginate(10));
	}

	public function store(Request $request)
	{
	    //BUAT VALIDASI DATA
	    $this->validate($request, [
        'nik' => 'required|string|unique:customers,nik',
        'name' => 'required|string|max:150',
        'address' => 'required|string',
        'phone' => 'required|string|max:15'
    ]);

    $user = $request->user();
    $request->request->add([
        'point' => 0,
        'deposit' => 0
    ]);
    if ($user->role == 3) {
        $request->request->add(['courier_id' => $user->id]);
    }
    $customer = Customer::create($request->all()); //MODIFIKASI BAGIAN INI
    return response()->json(['status' => 'success', 'data' => $customer]); //DAN PASSING DATANYA SEBAGAI RESPONSE
	}

	public function edit($id)
	{
	    $customer = Customer::find($id); //MELAKUKAN QUERY UNTUK MENGAMBIL DATA BERDASARKAN ID
	    return response()->json(['status' => 'success', 'data' => $customer]);
	}

	public function update(Request $request, $id)
	{
	    //VALIDASI DATA YANG DITERIMA
	    $this->validate($request, [
	        'name' => 'required|string|max:150',
	        'address' => 'required|string',
	        'phone' => 'required|string|max:15'
	    ]);

	    $customer = Customer::find($id); //QUERY UNTUK MENGAMBIL DATA BERDASARKAN ID
	    $customer->update($request->all()); //UPDATE DATA BERDASARKAN DATA YANG DITERIMA
	    return response()->json(['status' => 'success']);
	}

	public function destroy($id)
	{
	    $customer = Customer::find($id); //QUERY DATA BERDASARKAN ID
	    $customer->delete(); //KEMUDIAN HAPUS DATA TERSEBUT
	    return response()->json(['status' => 'success']);
	}
}

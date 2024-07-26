<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'method' => 'required|string',
            'amount' => 'required|numeric',
            'orderId' => 'required|string',
            'status' => 'required|string',
            'errorMessage' => 'nullable|string',
        ]);


        Payment::create([
            'method' => $validatedData['method'],
            'amount' => $validatedData['amount'],
            'orderId' => $validatedData['orderId'],
            'status' => $validatedData['status'],
            'errorMessage' => $validatedData['errorMessage'],
            'user_id' => Auth::id(), 
        ]);

        return response()->json(['message' => '결제 정보가 저장되었습니다.'], 200);
    }

    public function getUserPayments(Request $request)
    {
        $perPage = $request->input('per_page', 10); 
        $page = $request->input('page', 1);
    
        $payments = Payment::where('user_id', Auth::id())
                           ->paginate($perPage, ['*'], 'page', $page);
    
        return response()->json($payments);
    }
}

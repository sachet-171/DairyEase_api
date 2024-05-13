<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpensesDetail;
use App\Models\User;
use Illuminate\Validation\Rule;

class ExpensesDetailsController extends Controller
{
    public function createExpenses(Request $request)
{
    // Validate the input
    $request->validate([
        'user_id' => 'required|exists:users,id', 
        'date' => 'required|date',
        'product' => 'required',
        'shift' => ['required', Rule::in(['morning', 'evening'])],
        'quantity' => 'required|integer',
        'unit' => 'required',
        'per_quantity' => 'required|numeric',
    ]);

    // Check if the user is authorized to create expenses
    if (!auth()->check() || !auth()->user()->hasRole('admin')) {
        return response()->json([
            "status" => 0,
            "message" => "You are not authorized to create expenses details."
        ], 403); // 403 Forbidden status code
    }

    // Find the user by ID
    $user = User::find($request->user_id);

    if (!$user) {
        return response()->json([
            "status" => 0,
            "message" => "User not found."
        ], 404); // 404 Not Found status code
    }

    $expensesDetail = new ExpensesDetail();

    $expensesDetail->user_id = $request->user_id; // Use the provided user_id
    $expensesDetail->date = $request->date;
    $expensesDetail->product = $request->product;
    $expensesDetail->shift = $request->shift;
    $expensesDetail->quantity = $request->quantity;
    $expensesDetail->unit = $request->unit;
    $expensesDetail->per_quantity = $request->per_quantity;

    // Calculate total fat and total snf
    $total_price = $request->quantity * $request->per_quantity * $request->unit;
    $expensesDetail->total_price = $total_price;

    $expensesDetail->save();

    // response
    return response()->json([
        "status" => 1,
        "message" => "expenses details have been created",
        "total_price" => $total_price,
    ]);
}

// list of expenses details
    public function listExpenses()
{
    if (!auth()->check()) {
        return response()->json([
            "status" => 0,
            "message" => "Unauthorized. User is not logged in."
        ], 401); // 401 Unauthorized status code
    }

    $user = auth()->user();

    if ($user->hasRole('admin')) {
        // Admin can see all user's expenses
        $expensesDetails = ExpensesDetail::all();
    } else {
        // Regular user can only see their own expenses
        $expensesDetails = ExpensesDetail::where("user_id", $user->id)->get();
    }

    // Calculate the total balance
    $totalBalance = $expensesDetails->sum('total_price');

    return response()->json([
        "status" => 1,
        "message" => "expenses details",
        "data" => $expensesDetails,
        "total_balance" => number_format($totalBalance, 2)
    ]);
}

//delete expensesdetails 
public function deleteExpenses($user_id, $expenses_id)
{
    if (!auth()->check() || !auth()->user()->hasRole('admin')) {
        return response()->json([
            "status" => 0,
            "message" => "You are not authorized to delete expenses details."
        ], 403); // 403 Forbidden status code
    }
     // Find the user by ID
     $user = User::find($user_id);
 
     if (!$user) {
         return response()->json([
             "status" => 0,
             "message" => "User not found."
         ], 404); // 404 Not Found status code
     }
 
    // Find the expenses detail by ID
    $expensesDetail = $user->expensesDetails()->find($expenses_id);

    if (!$expensesDetail) {
        return response()->json([
            "status" => 0,
            "message" => "Expenses details not found."
        ], 404); // 404 Not Found status code
    }

    $expensesDetail->delete();

    return response()->json([
        "status" => 1,
        "message" => "Expenses details have been deleted successfully"
    ]);
}

//update
public function update(Request $request, $user_id, $expense_id)
{
    // Check if the user is authorized to update expenses
    if (!auth()->check() || !auth()->user()->hasRole('admin')) {
        return response()->json([
            "status" => 0,
            "message" => "You are not authorized to update expenses details."
        ], 403); // 403 Forbidden status code
    }

    $expensesDetail = ExpensesDetail::where([
        "id" => $expense_id,
        "user_id" => $user_id
    ])->first();

    if (!$expensesDetail) {
        return response()->json([
            "status" => 0,
            "message" => "Expenses details not found."
        ], 404); // 404 Not Found status code
    }

    $validatedData = $request->validate([
        'date' => 'required|date',
        'product' => 'required',
        'shift' => ['required', Rule::in(['morning', 'evening'])],
        'quantity' => 'required|integer',
        'unit' => 'required',
        'per_quantity' => 'required|numeric',
    ]);

    $validatedData['total_price'] = $validatedData['quantity'] * $validatedData['per_quantity']* $validatedData['unit'];

    $expensesDetail->update($validatedData);

    return response()->json(['message' => 'Expense updated successfully', 'expense' => $expensesDetail]);
}

    public function listShift(Request $request)
{
    // Check if the user is logged in
    if (!auth()->check()) {
        return response()->json([
            "status" => 0,
            "message" => "Unauthenticated. User is not logged in."
        ], 401); // 401 Unauthorized status code
    }

    // Get the authenticated user
    $user_id = auth()->user()->id;

    // Get the selected shift from the query parameter
    $shift = $request->query('shift', 'morning'); // Default to 'morning' if not specified

    // Query the Expense model to filter by the selected shift and the authenticated user's ID
    $expensesDetails = ExpensesDetail::where('shift', $shift)
        ->where("user_id", $user_id)
        ->get();

    // Return the filtered data as a JSON response
    return response()->json([
        "status" => 1,
        "messag" => "{$shift} expenses details",
        "data" => $expensesDetails,
    ]);
}

}

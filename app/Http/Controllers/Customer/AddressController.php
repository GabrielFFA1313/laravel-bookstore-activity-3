<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();
        return view('customer.addresses', compact('addresses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'label'         => 'required|string|max:50',
            'full_name'     => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'address_line_1'=> 'required|string|max:255',
            'address_line_2'=> 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'province'      => 'required|string|max:100',
            'postal_code'   => 'required|string|max:20',
            'is_default'    => 'boolean',
        ]);

        $validated['user_id'] = auth()->id();

        // If this is set as default, unset all others first
        if (!empty($validated['is_default'])) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        // If this is the first address, make it default automatically
        if (auth()->user()->addresses()->count() === 0) {
            $validated['is_default'] = true;
        }

        Address::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Address added successfully!');
    }

    public function update(Request $request, Address $address)
    {
        // Ensure user owns this address
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label'         => 'required|string|max:50',
            'full_name'     => 'required|string|max:255',
            'phone'         => 'required|string|max:20',
            'address_line_1'=> 'required|string|max:255',
            'address_line_2'=> 'nullable|string|max:255',
            'city'          => 'required|string|max:100',
            'province'      => 'required|string|max:100',
            'postal_code'   => 'required|string|max:20',
            'is_default'    => 'boolean',
        ]);

        if (!empty($validated['is_default'])) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        $address->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Address updated successfully!');
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, make the next one default
        if ($wasDefault) {
            $next = auth()->user()->addresses()->first();
            $next?->update(['is_default' => true]);
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Address deleted successfully!');
    }

    public function setDefault(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Default address updated!');
    }
}
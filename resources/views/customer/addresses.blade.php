@extends('layouts.app')

@section('title', 'My Addresses - PageTurner')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">My Addresses</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your saved delivery addresses.</p>
        </div>
        <button onclick="document.getElementById('addAddressModal').classList.remove('hidden')"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
            + Add Address
        </button>
    </div>

    {{-- Success / Error --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Address Cards --}}
    @forelse($addresses as $address)
        <div class="bg-white rounded-xl shadow p-5 flex items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-sm font-semibold bg-gray-100 text-gray-700 px-2 py-0.5 rounded">
                        {{ $address->label }}
                    </span>
                    @if($address->is_default)
                        <span class="text-xs bg-indigo-100 text-indigo-700 font-semibold px-2 py-0.5 rounded">
                            Default
                        </span>
                    @endif
                </div>
                <p class="font-medium text-gray-900">{{ $address->full_name }}</p>
                <p class="text-sm text-gray-500">{{ $address->phone }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ $address->formatted() }}</p>
            </div>

            <div class="flex flex-col gap-2 shrink-0">
                {{-- Set Default --}}
                @if(!$address->is_default)
                    <form action="{{ route('addresses.default', $address) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="text-xs text-indigo-600 hover:text-indigo-800 font-medium underline">
                            Set Default
                        </button>
                    </form>
                @endif

                {{-- Edit --}}
                <button onclick="openEditModal({{ $address->id }}, '{{ addslashes($address->label) }}', '{{ addslashes($address->full_name) }}', '{{ addslashes($address->phone) }}', '{{ addslashes($address->address_line_1) }}', '{{ addslashes($address->address_line_2) }}', '{{ addslashes($address->city) }}', '{{ addslashes($address->province) }}', '{{ addslashes($address->postal_code) }}')"
                    class="text-xs text-gray-600 hover:text-gray-800 font-medium underline text-left">
                    Edit
                </button>

                {{-- Delete --}}
                <form action="{{ route('addresses.destroy', $address) }}" method="POST"
                    onsubmit="return confirm('Delete this address?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-xs text-red-600 hover:text-red-800 font-medium underline">
                        Delete
                    </button>
                </form>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-xl shadow p-12 text-center text-gray-400">
            <p class="text-lg font-medium mb-2">No addresses saved yet.</p>
            <p class="text-sm">Click "Add Address" to add your first delivery address.</p>
        </div>
    @endforelse

</div>

{{-- ── Add Address Modal ──────────────────────────────────── --}}
<div id="addAddressModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">Add New Address</h2>
            <button onclick="document.getElementById('addAddressModal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="overflow-y-auto flex-1 px-6 py-4">
            <form action="{{ route('addresses.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                        <select name="label" class="w-full border-gray-300 rounded-lg text-sm">
                            <option>Home</option>
                            <option>Work</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" placeholder="09XX XXX XXXX"
                            class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" placeholder="Full name"
                        class="w-full border-gray-300 rounded-lg text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                    <input type="text" name="address_line_1" placeholder="House no., Street"
                        class="w-full border-gray-300 rounded-lg text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="address_line_2" placeholder="Barangay, Subdivision"
                        class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="city" class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                        <input type="text" name="province" class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                        <input type="text" name="postal_code" class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_default" value="1" id="add_is_default"
                        class="rounded border-gray-300">
                    <label for="add_is_default" class="text-sm text-gray-600">Set as default address</label>
                </div>
                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                        Save Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Edit Address Modal ──────────────────────────────────── --}}
<div id="editAddressModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b">
            <h2 class="text-lg font-bold text-gray-900">Edit Address</h2>
            <button onclick="document.getElementById('editAddressModal').classList.add('hidden')"
                class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
        </div>
        <div class="overflow-y-auto flex-1 px-6 py-4">
            <form id="editAddressForm" action="" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Label</label>
                        <select name="label" id="edit_label" class="w-full border-gray-300 rounded-lg text-sm">
                            <option>Home</option>
                            <option>Work</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                        <input type="text" name="phone" id="edit_phone"
                            class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name"
                        class="w-full border-gray-300 rounded-lg text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 1</label>
                    <input type="text" name="address_line_1" id="edit_address_line_1"
                        class="w-full border-gray-300 rounded-lg text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Line 2 <span class="text-gray-400">(optional)</span></label>
                    <input type="text" name="address_line_2" id="edit_address_line_2"
                        class="w-full border-gray-300 rounded-lg text-sm">
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                        <input type="text" name="city" id="edit_city"
                            class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                        <input type="text" name="province" id="edit_province"
                            class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                        <input type="text" name="postal_code" id="edit_postal_code"
                            class="w-full border-gray-300 rounded-lg text-sm" required>
                    </div>
                </div>
                <div class="pt-2">
                    <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 transition">
                        Update Address
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditModal(id, label, fullName, phone, line1, line2, city, province, postalCode) {
    // Set form action
    document.getElementById('editAddressForm').action = `/addresses/${id}`;

    // Fill fields
    document.getElementById('edit_label').value        = label;
    document.getElementById('edit_full_name').value    = fullName;
    document.getElementById('edit_phone').value        = phone;
    document.getElementById('edit_address_line_1').value = line1;
    document.getElementById('edit_address_line_2').value = line2;
    document.getElementById('edit_city').value         = city;
    document.getElementById('edit_province').value     = province;
    document.getElementById('edit_postal_code').value  = postalCode;

    document.getElementById('editAddressModal').classList.remove('hidden');
}
</script>
@endpush

@endsection
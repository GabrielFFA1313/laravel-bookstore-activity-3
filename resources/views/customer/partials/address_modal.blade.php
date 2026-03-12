{{-- ── Address Selection Modal ─────────────────────────────── --}}
@php
    $defaultAddressId = $addresses->firstWhere('is_default', true)?->id ?? $addresses->first()?->id ?? '';
@endphp
<div
    x-data="{
        show: false,
        showForm: false,
        selectedAddress: '{{ $defaultAddressId }}'
    }"
    x-on:open-address-modal.window="show = true"
>
    {{-- Trigger: Checkout button calls this from cart --}}
    <template x-if="show">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] flex flex-col">

                {{-- Modal Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b">
                    <h2 class="text-lg font-bold text-gray-900">Select Delivery Address</h2>
                    <button @click="show = false"
                        class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>

                {{-- Address List --}}
                <div class="overflow-y-auto flex-1 px-6 py-4 space-y-3">
                    @forelse($addresses as $address)
                        <label class="block cursor-pointer">
                            <input type="radio" name="selected_address" value="{{ $address->id }}"
                                x-model="selectedAddress"
                                class="peer hidden"
                                {{ $address->is_default ? 'checked' : '' }}>
                            <div class="border-2 rounded-lg p-4 peer-checked:border-indigo-600 peer-checked:bg-indigo-50 hover:border-indigo-300 transition">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
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
                                    <div class="w-5 h-5 rounded-full border-2 border-gray-300 peer-checked:border-indigo-600 flex-shrink-0 mt-1"></div>
                                </div>
                            </div>
                        </label>
                    @empty
                        <div class="text-center py-6 text-gray-400">
                            <p class="mb-2">You have no saved addresses yet.</p>
                            <p class="text-sm">
                                <a href="{{ route('addresses.index') }}" class="text-indigo-600 underline">
                                    Add an address here
                                </a> before checking out.
                            </p>
                        </div>
                    @endforelse

                    {{-- Add New Address Toggle --}}
                    <button type="button" @click="showForm = !showForm"
                        class="w-full border-2 border-dashed border-gray-300 rounded-lg p-3 text-sm text-indigo-600 hover:border-indigo-400 hover:bg-indigo-50 transition font-medium">
                        + Add New Address
                    </button>

                    {{-- New Address Form --}}
                    <div x-show="showForm" x-cloak class="bg-gray-50 rounded-lg p-4">
                        <form action="{{ route('addresses.store') }}" method="POST" class="space-y-3">
                            @csrf
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Label</label>
                                    <select name="label" class="w-full border-gray-300 rounded text-sm">
                                        <option>Home</option>
                                        <option>Work</option>
                                        <option>Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Phone</label>
                                    <input type="text" name="phone" placeholder="09XX XXX XXXX"
                                        class="w-full border-gray-300 rounded text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="full_name" placeholder="Full name"
                                    class="w-full border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Address Line 1</label>
                                <input type="text" name="address_line_1" placeholder="House no., Street"
                                    class="w-full border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Address Line 2 (optional)</label>
                                <input type="text" name="address_line_2" placeholder="Barangay, Subdivision"
                                    class="w-full border-gray-300 rounded text-sm">
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">City</label>
                                    <input type="text" name="city" class="w-full border-gray-300 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Province</label>
                                    <input type="text" name="province" class="w-full border-gray-300 rounded text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Postal Code</label>
                                    <input type="text" name="postal_code" class="w-full border-gray-300 rounded text-sm">
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="checkbox" name="is_default" value="1" id="modal_is_default"
                                    class="rounded border-gray-300">
                                <label for="modal_is_default" class="text-xs text-gray-600">Set as default address</label>
                            </div>
                            <button type="submit"
                                class="w-full bg-indigo-600 text-white py-2 rounded text-sm hover:bg-indigo-700 transition font-medium">
                                Save & Continue
                            </button>
                        </form>
                    </div>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t">
                    <form action="{{ route('orders.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="address_id" :value="selectedAddress">
                        <button type="submit"
                            x-bind:disabled="!selectedAddress"
                            x-bind:class="selectedAddress ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-300 cursor-not-allowed'"
                            class="w-full text-white py-3 rounded-lg transition font-semibold">
                            Confirm Order
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </template>
</div>
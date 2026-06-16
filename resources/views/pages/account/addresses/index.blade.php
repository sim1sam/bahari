@extends('layouts.account')

@section('title', 'Shipping Addresses')
@section('page_title', 'Shipping Addresses')
@section('mobile_title', 'Addresses')
@section('page_subtitle', 'Save home, office, and other delivery addresses')

@section('breadcrumb')
    <a href="{{ route('account.dashboard') }}" class="hover:text-brand-600">Dashboard</a>
    <span>/</span>
    <span class="text-ink">Shipping Addresses</span>
@endsection

@section('content')
    <div class="px-4 pt-4 lg:px-8 lg:pt-8 space-y-6 pb-4">
        <div class="grid lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1">
                <form action="{{ route('account.addresses.store') }}" method="POST" class="account-panel">
                    @csrf
                    <div class="account-panel-header">
                        <h2 class="font-semibold text-ink">Add New Address</h2>
                    </div>
                    <div class="account-panel-body space-y-4">
                        <div>
                            <label for="type" class="block text-sm font-medium mb-1.5">Address type</label>
                            <select name="type" id="type" required class="account-input @error('type') border-red-400 @enderror">
                                @foreach ($types as $value => $label)
                                    <option value="{{ $value }}" @selected(old('type', 'home') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="label" class="block text-sm font-medium mb-1.5">Label <span class="text-ink-muted">(optional)</span></label>
                            <input type="text" name="label" id="label" value="{{ old('label') }}" placeholder="Apartment, Branch, etc." class="account-input @error('label') border-red-400 @enderror">
                            @error('label')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="recipient_name" class="block text-sm font-medium mb-1.5">Recipient name</label>
                            <input type="text" name="recipient_name" id="recipient_name" value="{{ old('recipient_name', auth()->user()->name) }}" required class="account-input @error('recipient_name') border-red-400 @enderror">
                            @error('recipient_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-medium mb-1.5">Phone</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required class="account-input @error('phone') border-red-400 @enderror">
                            @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label for="address_line" class="block text-sm font-medium mb-1.5">Street address</label>
                            <input type="text" name="address_line" id="address_line" value="{{ old('address_line') }}" required class="account-input @error('address_line') border-red-400 @enderror">
                            @error('address_line')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="city" class="block text-sm font-medium mb-1.5">City</label>
                                <input type="text" name="city" id="city" value="{{ old('city') }}" required class="account-input @error('city') border-red-400 @enderror">
                                @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="zip" class="block text-sm font-medium mb-1.5">ZIP</label>
                                <input type="text" name="zip" id="zip" value="{{ old('zip') }}" required class="account-input @error('zip') border-red-400 @enderror">
                                @error('zip')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <label class="flex items-center gap-2 text-sm text-ink-muted">
                            <input type="checkbox" name="is_default" value="1" class="rounded border-border text-brand-600 focus:ring-brand-500">
                            Make default shipping address
                        </label>

                        <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">
                            Add Address
                        </button>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-2 space-y-4">
                @forelse ($addresses as $address)
                    <article class="account-panel">
                        <div class="account-panel-header gap-3">
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="font-semibold text-ink">{{ $address->label ?: $address->typeLabel() }}</h2>
                                    <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs font-semibold text-brand-700">{{ $address->typeLabel() }}</span>
                                    @if ($address->is_default)
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Default</span>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-ink-muted truncate">{{ $address->address_line }}, {{ $address->city }} {{ $address->zip }}</p>
                            </div>
                        </div>

                        <form action="{{ route('account.addresses.update', $address) }}" method="POST" class="account-panel-body space-y-4">
                            @csrf
                            @method('PATCH')

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="type-{{ $address->id }}" class="block text-sm font-medium mb-1.5">Type</label>
                                    <select name="type" id="type-{{ $address->id }}" required class="account-input">
                                        @foreach ($types as $value => $label)
                                            <option value="{{ $value }}" @selected(old('type', $address->type) === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="label-{{ $address->id }}" class="block text-sm font-medium mb-1.5">Label</label>
                                    <input type="text" name="label" id="label-{{ $address->id }}" value="{{ old('label', $address->label) }}" class="account-input">
                                </div>
                            </div>

                            <div class="grid sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="recipient-name-{{ $address->id }}" class="block text-sm font-medium mb-1.5">Recipient name</label>
                                    <input type="text" name="recipient_name" id="recipient-name-{{ $address->id }}" value="{{ old('recipient_name', $address->recipient_name) }}" required class="account-input">
                                </div>
                                <div>
                                    <label for="phone-{{ $address->id }}" class="block text-sm font-medium mb-1.5">Phone</label>
                                    <input type="tel" name="phone" id="phone-{{ $address->id }}" value="{{ old('phone', $address->phone) }}" required class="account-input">
                                </div>
                            </div>

                            <div>
                                <label for="address-line-{{ $address->id }}" class="block text-sm font-medium mb-1.5">Street address</label>
                                <input type="text" name="address_line" id="address-line-{{ $address->id }}" value="{{ old('address_line', $address->address_line) }}" required class="account-input">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="city-{{ $address->id }}" class="block text-sm font-medium mb-1.5">City</label>
                                    <input type="text" name="city" id="city-{{ $address->id }}" value="{{ old('city', $address->city) }}" required class="account-input">
                                </div>
                                <div>
                                    <label for="zip-{{ $address->id }}" class="block text-sm font-medium mb-1.5">ZIP</label>
                                    <input type="text" name="zip" id="zip-{{ $address->id }}" value="{{ old('zip', $address->zip) }}" required class="account-input">
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-sm text-ink-muted">
                                <input type="checkbox" name="is_default" value="1" class="rounded border-border text-brand-600 focus:ring-brand-500" @checked($address->is_default)>
                                Use as default
                            </label>

                            <div class="flex flex-wrap items-center gap-3">
                                <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700 transition-colors">Save</button>
                                @unless ($address->is_default)
                                    <button type="submit" form="default-address-{{ $address->id }}" class="rounded-lg border border-border px-4 py-2.5 text-sm font-medium text-ink-muted hover:text-ink transition-colors">Make Default</button>
                                @endunless
                                <button type="submit" form="delete-address-{{ $address->id }}" class="ml-auto rounded-lg border border-red-200 px-4 py-2.5 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors">Delete</button>
                            </div>
                        </form>

                        <form id="default-address-{{ $address->id }}" action="{{ route('account.addresses.default', $address) }}" method="POST" class="hidden">
                            @csrf
                            @method('PATCH')
                        </form>
                        <form id="delete-address-{{ $address->id }}" action="{{ route('account.addresses.destroy', $address) }}" method="POST" class="hidden">
                            @csrf
                            @method('DELETE')
                        </form>
                    </article>
                @empty
                    <div class="account-panel">
                        <div class="account-panel-body text-center py-12">
                            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-brand-50 text-brand-600">
                                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 11c1.657 0 3-1.567 3-3.5S13.657 4 12 4 9 5.567 9 7.5 10.343 11 12 11z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 9c0 7-7.5 12-7.5 12S4.5 16 4.5 9a7.5 7.5 0 1115 0z"/></svg>
                            </div>
                            <h2 class="font-semibold text-ink">No saved addresses yet</h2>
                            <p class="mt-1 text-sm text-ink-muted">Add your home, office, or another delivery address for faster checkout.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection

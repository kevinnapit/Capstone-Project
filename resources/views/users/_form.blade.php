@csrf
@if ($method !== 'POST')
    @method($method)
@endif

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Nama" />
        <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $managedUser->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $managedUser->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="role" value="Role" />
        <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            <option value="">Pilih role</option>
            @foreach ($roles as $role)
                <option value="{{ $role->name }}" @selected(old('role', isset($managedUser) ? $managedUser->roles->first()?->name : '') === $role->name)>{{ $role->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('role')" class="mt-2" />
    </div>
    <div></div>
    <div>
        <x-input-label for="password" :value="isset($managedUser) ? 'Password baru (opsional)' : 'Password'" />
        <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! isset($managedUser)" autocomplete="new-password" />
        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>
    <div>
        <x-input-label for="password_confirmation" value="Konfirmasi password" />
        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! isset($managedUser)" autocomplete="new-password" />
    </div>
</div>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('users.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Simpan</button>
</div>

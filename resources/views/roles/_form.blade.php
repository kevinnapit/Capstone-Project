@csrf
@if ($method !== 'POST')
    @method($method)
@endif

<div>
    <x-input-label for="name" value="Nama role" />
    <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $managedRole->name ?? '')" :readonly="isset($managedRole) && in_array($managedRole->name, ['admin', 'employee'], true)" required />
    <p class="mt-1 text-xs text-gray-500">Gunakan huruf kecil, misalnya: supervisor atau kasir.</p>
    <x-input-error :messages="$errors->get('name')" class="mt-2" />
</div>

<fieldset class="mt-6">
    <legend class="text-sm font-medium text-gray-900">Permission</legend>
    @if (isset($managedRole) && $managedRole->name === 'admin')
        <p class="mt-1 text-xs text-amber-700">Role admin selalu memperoleh seluruh permission untuk mencegah sistem terkunci.</p>
    @endif
    <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($permissions as $permission)
            <label class="flex items-center rounded-lg border border-gray-200 p-3 hover:bg-gray-50">
                <input type="checkbox" name="permissions[]" value="{{ $permission->name }}" class="rounded border-gray-300 text-blue-700 focus:ring-blue-500" @checked(in_array($permission->name, old('permissions', isset($managedRole) ? $managedRole->permissions->pluck('name')->all() : []))) @disabled(isset($managedRole) && $managedRole->name === 'admin')>
                <span class="ml-2 text-sm text-gray-700">{{ $permission->name }}</span>
            </label>
        @endforeach
    </div>
    <x-input-error :messages="$errors->get('permissions')" class="mt-2" />
</fieldset>

<div class="mt-6 flex justify-end gap-3">
    <a href="{{ route('roles.index') }}" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</a>
    <button type="submit" class="rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white hover:bg-blue-800">Simpan</button>
</div>

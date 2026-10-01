@props([
    'name' => null,
    'nameExpression' => null,
    'options' => [],
    'dynamicOptions' => null,
    'selected' => '',
    'model' => null,
    'placeholder' => 'Pilih data',
    'searchPlaceholder' => 'Cari...',
    'required' => false,
])

<div
    class="relative"
    x-data="{
        open: false,
        search: '',
        value: @js((string) $selected),
        staticOptions: {{ Illuminate\Support\Js::from(collect($options)->map(fn ($label, $value) => ['value' => (string) $value, 'label' => (string) $label])->values()) }},
        get optionList() { return {{ $dynamicOptions ?: 'this.staticOptions' }}; },
        get currentValue() { return {{ $model ?: 'this.value' }} ?? ''; },
        set currentValue(value) { {{ $model ? $model.' = value' : 'this.value = value' }}; },
        get selectedLabel() {
            const found = this.optionList.find(option => String(option.value ?? option.id) === String(this.currentValue));
            return found ? (found.label ?? found.name) : '';
        },
        get filteredOptions() {
            const query = this.search.toLowerCase().trim();
            return this.optionList.filter(option => !query || String(option.label ?? option.name).toLowerCase().includes(query));
        },
        choose(option) {
            this.currentValue = String(option.value ?? option.id);
            this.open = false;
            this.search = '';
        }
    }"
    @click.outside="open = false"
    @keydown.escape.window="open = false"
>
    <input type="hidden" @if ($name) name="{{ $name }}" @endif @if ($nameExpression) :name="{{ $nameExpression }}" @endif x-model="currentValue" @if ($required) required @endif>
    <button type="button" @click="open = !open; if (open) $nextTick(() => $refs.search?.focus())" class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-left text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500">
        <span class="truncate" :class="selectedLabel ? 'text-gray-900' : 'text-gray-500'" x-text="selectedLabel || @js($placeholder)"></span>
        <svg class="ml-2 h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
    </button>
    <div x-show="open" x-cloak x-transition class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white p-2 shadow-lg">
        <input x-ref="search" x-model="search" type="search" placeholder="{{ $searchPlaceholder }}" class="mb-2 w-full rounded-md border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500">
        <div class="max-h-60 overflow-y-auto">
            @if (! $required)
                <button type="button" @click="currentValue = ''; open = false; search = ''" class="w-full rounded-md px-3 py-2 text-left text-sm text-gray-500 hover:bg-gray-100">{{ $placeholder }}</button>
            @endif
            <template x-for="option in filteredOptions" :key="String(option.value ?? option.id)">
                <button type="button" @click="choose(option)" class="flex w-full items-center justify-between rounded-md px-3 py-2 text-left text-sm hover:bg-blue-50" :class="String(currentValue) === String(option.value ?? option.id) ? 'bg-blue-50 text-blue-700' : 'text-gray-700'">
                    <span x-text="option.label ?? option.name"></span><span x-show="String(currentValue) === String(option.value ?? option.id)">✓</span>
                </button>
            </template>
            <p x-show="filteredOptions.length === 0" class="px-3 py-4 text-center text-sm text-gray-500">Data tidak ditemukan.</p>
        </div>
    </div>
</div>

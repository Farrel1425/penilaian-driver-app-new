@csrf
<div class="form-grid">
    <x-admin.field label="Nama Kategori Indikator" name="name" :value="$category->name" required />
    <x-admin.select label="Target" name="target_type" required>
        @foreach ([App\Models\Question::TARGET_DRIVER, App\Models\Question::TARGET_VEHICLE, App\Models\Question::TARGET_FEEDBACK] as $target)
            <option value="{{ $target }}" @selected(old('target_type', $category->target_type) === $target)>{{ App\Models\Question::targetLabel($target) }}</option>
        @endforeach
    </x-admin.select>
    <x-admin.field label="Urutan" name="sort_order" type="number" :value="$category->sort_order" min="0" max="9999" required />
    <x-admin.select label="Status" name="status" required>
        @foreach ([App\Models\IndicatorCategory::STATUS_ACTIVE => 'Aktif', App\Models\IndicatorCategory::STATUS_INACTIVE => 'Nonaktif'] as $value => $label)
            <option value="{{ $value }}" @selected(old('status', $category->status) === $value)>{{ $label }}</option>
        @endforeach
    </x-admin.select>
</div>
<div class="form-actions">
    <a class="secondary-button" href="{{ route('admin.indicator-categories.index') }}">Batal</a>
    <button class="primary-button" type="submit">Simpan</button>
</div>

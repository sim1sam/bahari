@props(['name', 'label', 'value', 'default' => '#000000', 'hint' => null])

<div class="settings-field">
    <label>{{ $label }}</label>
    <div class="settings-color-field">
        <input type="color" value="{{ old($name, $value ?? $default) }}" oninput="this.nextElementSibling.value=this.value">
        <input type="text" name="{{ $name }}" class="form-control" value="{{ old($name, $value ?? $default) }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
    </div>
    @if ($hint)
        <small class="settings-field-hint">{{ $hint }}</small>
    @endif
</div>

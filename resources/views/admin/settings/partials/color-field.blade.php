@props(['name', 'label', 'value', 'default' => '#000000'])

<div class="form-group">
    <label>{{ $label }}</label>
    <div class="d-flex align-items-center gap-2">
        <input type="color" class="form-control form-control-color" value="{{ old($name, $value ?? $default) }}" oninput="this.nextElementSibling.value=this.value">
        <input type="text" name="{{ $name }}" class="form-control" value="{{ old($name, $value ?? $default) }}" pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$" oninput="this.previousElementSibling.value=this.value">
    </div>
</div>

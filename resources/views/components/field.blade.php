@props(['name', 'label', 'type' => 'text', 'value' => null])
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}"
           value="{{ $type === 'password' ? '' : old($name, $value) }}"
           {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@props(['model', 'label' => 'Image'])

<div class="form-group">
    <label>{{ $label }}</label>
    @if ($model->imageUrl())
        <div class="mb-2">
            <img src="{{ $model->imageUrl() }}" alt="" class="img-thumbnail" style="max-height:120px">
            <div class="custom-control custom-checkbox mt-2">
                <input type="checkbox" class="custom-control-input" id="remove_image" name="remove_image" value="1">
                <label class="custom-control-label" for="remove_image">Remove image</label>
            </div>
        </div>
    @endif
    <input type="file" name="image" class="form-control-file @error('image') is-invalid @enderror" accept="image/png,image/jpeg,image/webp">
    @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
    <input type="url" name="image_url" class="form-control mt-2" placeholder="Or paste image URL" value="{{ old('image_url') }}">
</div>

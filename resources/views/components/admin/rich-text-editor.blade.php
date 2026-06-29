@props([
    'name',
    'id' => null,
    'value' => '',
    'rows' => 8,
    'placeholder' => '',
])

@php
    $fieldId = $id ?? str_replace(['[', ']'], ['-', ''], $name);
@endphp

<textarea
    id="{{ $fieldId }}"
    name="{{ $name }}"
    class="form-control js-rich-text-editor"
    rows="{{ $rows }}"
    placeholder="{{ $placeholder }}"
    {{ $attributes }}
>{{ $value }}</textarea>

@once
    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css">
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js"></script>
        <script>
            $(function () {
                $('.js-rich-text-editor').summernote({
                    height: 240,
                    placeholder: 'Write the full product description…',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['insert', ['link', 'picture', 'hr']],
                        ['view', ['codeview', 'fullscreen']],
                    ],
                });

                $('form').on('submit', function () {
                    $('.js-rich-text-editor').each(function () {
                        var $field = $(this);
                        if ($field.next('.note-editor').length) {
                            $field.val($field.summernote('code'));
                        }
                    });
                });
            });
        </script>
    @endpush
@endonce

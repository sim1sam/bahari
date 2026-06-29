@if (session('success') || session('error') || $errors->any())
    <script>
        (function () {
            function showFlashAlerts() {
                if (typeof Swal === 'undefined') {
                    return setTimeout(showFlashAlerts, 50);
                }

                @if (session('success'))
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: @json(session('success')),
                        position: 'center',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#0891b2',
                        allowOutsideClick: true,
                        heightAuto: false,
                    });
                @endif

                @if (session('error'))
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: @json(session('error')),
                        position: 'center',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545',
                        allowOutsideClick: true,
                        heightAuto: false,
                    });
                @endif

                @if ($errors->any())
                    Swal.fire({
                        icon: 'error',
                        title: 'Could not save',
                        html: '<ul style="text-align:left;margin:0;padding-left:1.25rem;">' +
                            @json($errors->all()).map(function (message) {
                                return '<li>' + message + '</li>';
                            }).join('') +
                            '</ul>',
                        position: 'center',
                        showConfirmButton: true,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#dc3545',
                        allowOutsideClick: true,
                        heightAuto: false,
                    });
                @endif
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showFlashAlerts);
            } else {
                showFlashAlerts();
            }
        })();
    </script>
@endif

@if (session('success') || session('error'))
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
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', showFlashAlerts);
            } else {
                showFlashAlerts();
            }
        })();
    </script>
@endif

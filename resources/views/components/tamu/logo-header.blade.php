<a href="{{ url('/') }}" class="d-inline-block btn-pindah-home" aria-label="Menuju halaman utama"
    data-text="{{ app()->getLocale() === 'en' ? 'Go to homepage?' : 'Pindah ke halaman utama?' }}"
    data-confirm="{{ app()->getLocale() === 'en' ? 'Yes, proceed' : 'Ya, lanjutkan' }}"
    data-cancel="{{ app()->getLocale() === 'en' ? 'Cancel' : 'Batal' }}">
    <img src="{{ asset('theme/images/akreditasi-unggul.webp') }}" alt="Logo Akreditasi Unggul" class="img-fluid"
        style="width:60px" />
</a>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.btn-pindah-home').addEventListener('click', function(e) {
            e.preventDefault();

            const targetUrl = this.getAttribute('href');
            const textMessage = this.getAttribute('data-text');
            const confirmText = this.getAttribute('data-confirm');
            const cancelText = this.getAttribute('data-cancel');

            Swal.fire({
                text: textMessage,
                icon: 'question',
                iconColor: '#004a5f',
                width: '400px',
                padding: '1rem',
                showCancelButton: true,
                confirmButtonText: confirmText,
                confirmButtonColor: '#004a5f',
                cancelButtonText: cancelText
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = targetUrl;
                }
            });
        });
    });
</script>

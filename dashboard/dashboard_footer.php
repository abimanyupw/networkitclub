</div> <footer class="dashboard-footer text-light mt-auto">
        <div class="container py-3">
            <div class="justify-content-center align-items-center text-center">
                <p class="mb-0 fw-bold">© 2025 NETWORK IT CLUB. All rights reserved.</p>
                <p class="mb-0">Developed by 
                    <a href="https://www.instagram.com/abimanyupw_/" 
                       class="text-decoration-none text-light fw-semibold">
                        Abimanyu Pradipa W
                    </a>
                </p>
            </div>
        </div>
    </footer>

    </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        if (sidebarToggle && sidebar) { // Pastikan elemen ada sebelum menambahkan event listener
            sidebarToggle.addEventListener('click', function () {
                sidebar.classList.toggle('sidebar-open');
            });
        }
    </script>

   <script>
    const IDLE_TIMEOUT_SECONDS = 300; // 5 menit (5 * 60 detik)
    const CHECK_INTERVAL_SECONDS = 30; // Cek setiap 30 detik untuk aktivitas server

    let timeout;
    let checkSessionInterval;

    function resetTimer() {
        clearTimeout(timeout);
        clearInterval(checkSessionInterval);
        timeout = setTimeout(logoutUser, IDLE_TIMEOUT_SECONDS * 1000);

        // checkSessionInterval akan memanggil check_session.php
        // check_session.php SEKARANG akan melakukan redirect sendiri jika idle
        checkSessionInterval = setInterval(checkSessionStatus, Math.max(1, IDLE_TIMEOUT_SECONDS - CHECK_INTERVAL_SECONDS) * 1000);
    }

    function checkSessionStatus() {
        // Panggil check_session.php
        // TIDAK PERLU handle respons JSON 'logged_out' lagi, karena PHP akan redirect
        fetch('../check_session.php')
            .then(response => {
                // Respons bisa kosong jika PHP sudah redirect
                // Atau bisa juga respon JSON 'active'
                if (response.redirected) { // Cek jika fetch API di-redirect
                    console.log('Session check resulted in a redirect (logged out).');
                    // Tidak perlu melakukan apa-apa lagi, browser sudah di-redirect
                } else {
                    // Jika tidak di-redirect, asumsikan sesi aktif dan reset timer
                    // Ini menangani respons JSON 'active' dari check_session.php
                    return response.json(); // Lanjutkan parse JSON
                }
            })
            .then(data => {
                if (data && data.status === 'active') {
                    console.log('Sesi aktif, memperbarui timer.');
                    resetTimer();
                }
                // Jika data kosong (karena sudah redirect) atau status bukan 'active',
                // tidak perlu ada tindakan lain dari JS ini.
            })
            .catch(error => {
                console.error('Error checking session status:', error);
                // window.location.href = '../logout.php?msg=error_session_check'; // Opsional redirect error
            });
    }

    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keypress', resetTimer);
    document.addEventListener('click', resetTimer);
    document.addEventListener('scroll', resetTimer);

    function logoutUser() {
        // Jika timeout klien terjadi, langsung panggil check_session.php
        // check_session.php akan mendeteksi idle dan melakukan redirect
        console.log('Client-side idle timeout reached. Initiating server-side session check.');
        window.location.href = '../../check_session.php'; // Atau langsung logout.php jika Anda tidak peduli server side check
    }

    resetTimer();

    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            resetTimer();
        }
    });
</script>

   <?php if (isset($enable_material_scripts) && $enable_material_scripts === true): ?>
<script>
    $(document).ready(function() {
        if ($('#material_content').length) {
            $('#material_content').summernote({
                placeholder: 'Isi konten materi di sini...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    // Kelompok Dasar
                    ['style', ['style']], // Paragraph Styles (Normal, H1, H2, dst.)
                    ['fontname', ['fontname']], // <-- MENAMBAHKAN: Pilihan Font Family
                    ['fontsize', ['fontsize']], // <-- MENAMBAHKAN: Pilihan Font Size
                    ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']], // <-- MENAMBAHKAN: Italic, Strikethrough, Superscript, Subscript
                    ['color', ['color']], // Warna teks
                    ['para', ['ul', 'ol', 'paragraph', 'height']], // <-- MENAMBAHKAN: Line Height
                    ['table', ['table']], // Tabel
                    ['insert', ['link', 'picture', 'video', 'hr']], // <-- MENAMBAHKAN: Horizontal Rule (Garis Pembatas)
                    ['view', ['fullscreen', 'codeview', 'help']], // Fullscreen, Code View, Help
                    ['misc', ['undo', 'redo', 'print', 'help']] // <-- MENAMBAHKAN: Undo, Redo, Print (jika dibutuhkan)
                ],
                // Opsi tambahan untuk fontname dan fontsize
                fontNames: ['Arial', 'Arial Black', 'Comic Sans MS', 'Courier New', 'Merriweather', 'Open Sans', 'Roboto', 'Times New Roman'], // Daftar font yang tersedia
                fontSizes: ['8', '9', '10', '11', '12', '14', '16', '18', '24', '36', '48'], // Daftar ukuran font
                lineHeights: ['0.8', '1.0', '1.2', '1.4', '1.5', '1.6', '1.8', '2.0', '3.0'] // Daftar tinggi baris
            });
        }

        // ... (Kode toggleFileUrlField tetap sama) ...
        function toggleFileUrlField() {
            if ($('#material_type').length) {
                var selectedType = $('#material_type').val();
                if (selectedType === 'text' || selectedType === '') {
                    $('#file_url_section').hide();
                    $('#file_url').val('');
                } else {
                    $('#file_url_section').show();
                }
            }
        }

        toggleFileUrlField();

        $('#material_type').change(function() {
            toggleFileUrlField();
        });
    });
</script>
<?php endif; ?>

    </body>
    </html>
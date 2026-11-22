*** Settings ***
Documentation       Pengujian Otomatis Autentikasi (Login dan Registrasi)
...                 Mencakup skenario positif dan negatif untuk Login Admin, Petugas, Warga,
...                 serta validasi pendaftaran akun warga baru.
Resource            resources/common.resource
Suite Setup         Buka Browser Dan Kunjungi    ${LOGIN_URL}
Suite Teardown      Tutup Sesi Browser
Test Teardown       Run Keyword And Ignore Error    Lakukan Logout

*** Test Cases ***
TC-01: Login Berhasil Sebagai Warga
    [Documentation]    Memastikan akun warga yang valid dapat login dan diarahkan ke dashboard warga.
    Lakukan Login    ${WARGA_USER}    ${WARGA_PASS}
    Verifikasi Berhasil Masuk Ke Dashboard    /warga/dashboard
    Wait Until Page Contains    Menu Warga    ${TIMEOUT}
    Page Should Contain    Laporkan Sampah

TC-02: Login Berhasil Sebagai Petugas
    [Documentation]    Memastikan akun petugas dapat login dan melihat menu petugas.
    Lakukan Login    ${PETUGAS_USER}    ${PETUGAS_PASS}
    Verifikasi Berhasil Masuk Ke Dashboard    /petugas/dashboard
    Wait Until Page Contains    Menu Petugas    ${TIMEOUT}
    Page Should Contain    Tugas Hari Ini

TC-03: Login Berhasil Sebagai Admin
    [Documentation]    Memastikan akun administrator dapat login dan mengakses dashboard admin.
    Lakukan Login    ${ADMIN_USER}    ${ADMIN_PASS}
    Verifikasi Berhasil Masuk Ke Dashboard    /admin/dashboard
    Wait Until Page Contains    Menu Admin    ${TIMEOUT}
    Page Should Contain    Kelola Laporan

TC-04: Login Gagal Dengan Password Salah
    [Documentation]    Memverifikasi pesan kesalahan ketika password tidak sesuai.
    Lakukan Login    ${WARGA_USER}    passwordSalah123
    Wait Until Page Contains Element    xpath://div[contains(@class, 'alert') and contains(@class, 'error')]    ${TIMEOUT}
    Page Should Contain    Login gagal

TC-05: Login Gagal Dengan Username Tidak Terdaftar
    [Documentation]    Memverifikasi penanganan sistem saat username tidak ada di database.
    Lakukan Login    user_fiktif_999    password123
    Wait Until Page Contains Element    xpath://div[contains(@class, 'alert') and contains(@class, 'error')]    ${TIMEOUT}
    Page Should Contain    Username atau password salah

TC-06: Registrasi Akun Warga Baru Berhasil
    [Documentation]    Memastikan warga baru dapat mendaftar dengan data lengkap dan unik.
    Buka Halaman Register
    ${random_suffix}=    Generate Random String    6    [NUMBERS]
    ${random_username}=    Catenate    SEPARATOR=    warga_test_    ${random_suffix}
    ${random_phone}=       Catenate    SEPARATOR=    0812987    ${random_suffix}
    
    Input Text        id:name                     Warga Robot Tester
    Input Text        id:username                 ${random_username}
    Input Text        id:contact                  ${random_phone}
    Select From List By Value    id:area          Banjarsari
    Input Password    id:password                 rahasia123
    Input Password    id:password_confirmation    rahasia123
    Click Button      xpath://button[@type='submit']
    
    # Setelah registrasi sukses, otomatis diarahkan ke dashboard warga
    Verifikasi Berhasil Masuk Ke Dashboard    /warga/dashboard
    Wait Until Page Contains    Selamat datang di SampahRute    ${TIMEOUT}

TC-07: Registrasi Gagal Karena Konfirmasi Password Tidak Cocok
    [Documentation]    Memvalidasi sistem menolak pendaftaran jika konfirmasi password berbeda.
    Buka Halaman Register
    ${random_suffix}=    Generate Random String    6    [NUMBERS]
    ${random_username}=    Catenate    SEPARATOR=    warga_inv_    ${random_suffix}
    
    Input Text        id:name                     Warga Salah Password
    Input Text        id:username                 ${random_username}
    Input Text        id:contact                  081233445566
    Select From List By Value    id:area          Jebres
    Input Password    id:password                 rahasia123
    Input Password    id:password_confirmation    berbeda123
    Click Button      xpath://button[@type='submit']
    
    Wait Until Page Contains Element    xpath://div[contains(@class, 'alert') and contains(@class, 'error')]    ${TIMEOUT}
    Page Should Contain    Pendaftaran gagal

*** Settings ***
Documentation       Pengujian User Flow: Pelaporan Sampah Oleh Warga
...                 Mencakup navigasi ke form pelaporan, pengisian data laporan,
...                 pengiriman laporan baru, dan verifikasi riwayat laporan.
Resource            resources/common.resource
Suite Setup         Buka Browser Dan Kunjungi    ${LOGIN_URL}
Suite Teardown      Tutup Sesi Browser

*** Test Cases ***
TC-01: Warga Mengakses Formulir Buat Laporan
    [Documentation]    Login sebagai warga dan membuka halaman form laporan sampah.
    Lakukan Login    ${WARGA_USER}    ${WARGA_PASS}
    Verifikasi Berhasil Masuk Ke Dashboard    /warga/dashboard
    
    Click Element    xpath://nav[contains(@class,'side-menu')]//a[contains(@href, '/warga/laporan/buat')]
    Wait Until Location Is    ${WARGA_CREATE_REPORT}    ${TIMEOUT}
    Wait Until Page Contains    Laporkan Sampah    ${TIMEOUT}
    Page Should Contain Element    id:title
    Page Should Contain Element    id:location_id
    Page Should Contain Element    id:description

TC-02: Warga Berhasil Mengirimkan Laporan Sampah Baru
    [Documentation]    Mengisi formulir dengan data valid dan memastikan tersimpan ke database.
    Go To    ${WARGA_CREATE_REPORT}
    Wait Until Element Is Visible    id:title    ${TIMEOUT}
    
    # Pilih Jenis Masalah (Radio button)
    Click Element    xpath://input[@name='category' and @value='belum_diangkut']/following-sibling::span
    
    # Isi Judul Laporan
    ${rand_id}=    Generate Random String    4    [NUMBERS]
    ${report_title}=    Catenate    SEPARATOR=    Sampah Menumpuk di Depo Blok    ${rand_id}
    Input Text    id:title    ${report_title}
    
    # Pilih Lokasi Sampah
    Select From List By Index    id:location_id    1
    
    # Isi Deskripsi (Minimal 10 karakter)
    Input Text    id:description    Tumpukan sampah basah sudah melebihi kapasitas dan belum diangkut lebih dari 2 hari. Mohon segera ditindaklanjuti.
    
    # Kirim Laporan
    Click Button    xpath://form[contains(@class, 'report-form')]//button[@type='submit']
    
    # Verifikasi diarahkan ke halaman detail laporan
    Wait Until Location Matches    .*/warga/laporan/SR-.*    ${TIMEOUT}
    Wait Until Page Contains Element    xpath://div[contains(@class, 'alert') and contains(@class, 'success')]    ${TIMEOUT}
    Page Should Contain    berhasil disimpan ke database
    Page Should Contain    ${report_title}

TC-03: Validasi Form Gagal Jika Deskripsi Terlalu Pendek
    [Documentation]    Memastikan validasi server berjalan jika deskripsi kurang dari 10 karakter.
    Go To    ${WARGA_CREATE_REPORT}
    Wait Until Element Is Visible    id:title    ${TIMEOUT}
    
    Input Text    id:title    Sampah Bau
    Select From List By Index    id:location_id    1
    Input Text    id:description    pendek
    
    # Eksekusi submit dengan mengabaikan validasi HTML5 bawaan browser
    Execute JavaScript    document.querySelector('form.report-form').submit();
    
    Wait Until Page Contains Element    xpath://div[contains(@class, 'alert') and contains(@class, 'error')]    ${TIMEOUT}
    Page Should Contain    Deskripsi minimal 10 karakter

TC-04: Warga Memeriksa Daftar Riwayat Laporan Saya
    [Documentation]    Memastikan laporan yang baru dibuat muncul dalam daftar laporan warga.
    Click Element    xpath://nav[contains(@class,'side-menu')]//a[contains(@href, '/warga/laporan') and not(contains(@href, '/buat'))]
    Wait Until Location Is    ${WARGA_REPORTS}    ${TIMEOUT}
    Wait Until Page Contains    Laporan Saya    ${TIMEOUT}
    Page Should Contain Element    xpath://table | //div[contains(@class, 'report-grid')]
    
    [Teardown]    Lakukan Logout

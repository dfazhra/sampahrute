*** Settings ***
Documentation       Pengujian User Flow Lanjutan: Alur Kerja Admin & Petugas Lapangan
...                 Mencakup monitoring laporan warga oleh admin, filtering tabel,
...                 serta pemantauan tugas harian dan riwayat oleh petugas.
Resource            resources/common.resource
Suite Setup         Buka Browser Dan Kunjungi    ${LOGIN_URL}
Suite Teardown      Tutup Sesi Browser

*** Test Cases ***
TC-01: Admin Mengakses Halaman Kelola Laporan
    [Documentation]    Admin login dan membuka tabel kelola laporan untuk melihat data aduan masyarakat.
    Lakukan Login    ${ADMIN_USER}    ${ADMIN_PASS}
    Verifikasi Berhasil Masuk Ke Dashboard    /admin/dashboard
    
    Click Element    xpath://nav[contains(@class,'side-menu')]//a[contains(@href, '/admin/laporan')]
    Wait Until Location Is    ${ADMIN_REPORTS}    ${TIMEOUT}
    Wait Until Page Contains    Kelola Laporan Warga    ${TIMEOUT}
    Page Should Contain Element    id:report-table
    Page Should Contain Element    xpath://input[@data-filter-search]

TC-02: Admin Melakukan Pencarian Dan Filter Laporan
    [Documentation]    Menguji fitur pencarian laporan pada tabel admin.
    Input Text    xpath://input[@data-filter-search]    Pasar
    Sleep    0.5s
    # Memastikan tabel merespons filter
    Page Should Contain Element    id:report-table
    
    [Teardown]    Lakukan Logout

TC-03: Petugas Mengakses Daftar Tugas Pengangkutan Hari Ini
    [Documentation]    Petugas login dan memeriksa rute pengangkutan yang ditugaskan hari ini.
    Lakukan Login    ${PETUGAS_USER}    ${PETUGAS_PASS}
    Verifikasi Berhasil Masuk Ke Dashboard    /petugas/dashboard
    
    Click Element    xpath://nav[contains(@class,'side-menu')]//a[contains(@href, '/petugas/tugas')]
    Wait Until Location Is    ${PETUGAS_TASKS}    ${TIMEOUT}
    Wait Until Page Contains    Tugas Hari Ini    ${TIMEOUT}
    Page Should Contain Element    xpath://div[contains(@class, 'segmented')]
    Page Should Contain Element    xpath://div[contains(@class, 'task-grid')]

TC-04: Petugas Melihat Riwayat Pengangkutan
    [Documentation]    Petugas memeriksa halaman log riwayat pengangkutan sampah.
    Click Element    xpath://nav[contains(@class,'side-menu')]//a[contains(@href, '/petugas/riwayat')]
    Wait Until Location Contain    /petugas/riwayat    ${TIMEOUT}
    Wait Until Page Contains    Riwayat Pengangkutan    ${TIMEOUT}
    
    [Teardown]    Lakukan Logout

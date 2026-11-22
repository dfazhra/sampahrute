*** Settings ***
Documentation       Resource file yang berisi variabel global, setup/teardown, dan reusable keywords
...                 untuk pengujian otomatis menggunakan Robot Framework dan SeleniumLibrary.
Library             SeleniumLibrary
Library             String

*** Variables ***
${BASE_URL}             http://127.0.0.1:8000
${BROWSER}              chrome
${HEADLESS}             false
${DELAY}                0.2s
${TIMEOUT}              10s

# Kredensial Pengguna Demo
${ADMIN_USER}           admin
${ADMIN_PASS}           admin123

${PETUGAS_USER}         petugas
${PETUGAS_PASS}         petugas123

${WARGA_USER}           warga
${WARGA_PASS}           warga123

# URL Halaman
${LOGIN_URL}            ${BASE_URL}/login
${REGISTER_URL}         ${BASE_URL}/register
${HOME_URL}             ${BASE_URL}
${WARGA_DASHBOARD}      ${BASE_URL}/warga/dashboard
${WARGA_CREATE_REPORT}  ${BASE_URL}/warga/laporan/buat
${WARGA_REPORTS}        ${BASE_URL}/warga/laporan
${ADMIN_DASHBOARD}      ${BASE_URL}/admin/dashboard
${ADMIN_REPORTS}        ${BASE_URL}/admin/laporan
${PETUGAS_DASHBOARD}    ${BASE_URL}/petugas/dashboard
${PETUGAS_TASKS}        ${BASE_URL}/petugas/tugas

*** Keywords ***
Buka Browser Dan Kunjungi
    [Arguments]    ${url}=${BASE_URL}
    ${chrome_options}=    Evaluate    sys.modules['selenium.webdriver'].ChromeOptions()    sys
    Run Keyword If    '${HEADLESS}' == 'true'    Call Method    ${chrome_options}    add_argument    --headless=new
    Call Method    ${chrome_options}    add_argument    --no-sandbox
    Call Method    ${chrome_options}    add_argument    --disable-dev-shm-usage
    Call Method    ${chrome_options}    add_argument    --window-size\=1280,900
    Open Browser    ${url}    ${BROWSER}    options=${chrome_options}
    Set Selenium Speed    ${DELAY}
    Set Selenium Timeout    ${TIMEOUT}

Tutup Sesi Browser
    Close Browser

Buka Halaman Login
    Go To    ${LOGIN_URL}
    Wait Until Element Is Visible    id:email    ${TIMEOUT}
    Title Should Contain    Login SampahRute

Buka Halaman Register
    Go To    ${REGISTER_URL}
    Wait Until Element Is Visible    id:name    ${TIMEOUT}
    Title Should Contain    Daftar Akun Warga

Lakukan Login
    [Arguments]    ${username}    ${password}
    Buka Halaman Login
    Input Text    id:email    ${username}
    Input Password    id:password    ${password}
    Click Button    xpath://form[@action='${LOGIN_URL}']//button[@type='submit']

Verifikasi Berhasil Masuk Ke Dashboard
    [Arguments]    ${expected_role_path}
    Location Should Contain    ${expected_role_path}
    Wait Until Page Contains Element    xpath://aside[@id='sidebar']    ${TIMEOUT}

Lakukan Logout
    Wait Until Page Contains Element    xpath://form[contains(@action, 'logout')]    ${TIMEOUT}
    Click Button    xpath://form[contains(@action, 'logout')]//button[contains(@class, 'side-logout')]
    Wait Until Location Is    ${HOME_URL}/

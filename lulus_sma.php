<?php
// Array data siswa dari file paste.txt
$siswa = [
    "'Aisyah Nurul Aulia",
    "Abdul Aziz Nur Ilham Hamzah",
    "Adzkiya Imtiyaz Amanie",
    "Afifah Zulpa Nur Aini",
    "Ahmad Saiful Hakim",
    "Ahmat Sholikin",
    "Akmal Baihaqi",
    "Al-Adawiyah Qudwatunnisa",
    "Asma' Annajiyah",
    "BAGAS ASRUL RAMADHAN",
    "Chandra Amalia Sammawati",
    "Dava Maulana Hernawan",
    "Faridah Najdah Al Ghaziyah",
    "Fitri Ramadhani",
    "Hamima Khoirunniswah",
    "Karima Hanin Fatina",
    "Khoirunnisa' Alfa'izah",
    "Laila Agna Ramadhani",
    "Mawaddatul Khusna Rizqika",
    "Nabila Khoirunnisa",
    "Nabila Nuris Syifa",
    "Nabila Taskiya Fatma",
    "Nadiatus Sholikhah",
    "Nahya Alfiyanti",
    "Nuur Hasna",
    "Sumayyah Asy Syahidah",
    "Tsalisatul Khoiriyah Safar",
    "Wafiq Nurul Azizah",
    "Zahroh Auliya' Najwa",
    "Zahrotul Jamiilah",
    "Ahmad Aqil Asysy Uja'i",
    "Ahmad Dzakwan Zuhairisyafiq",
    "AHMAD MIFTAHURROIHAN",
    "AHMAD NI'AM KARIM",
    "Ammar Abdillah",
    "Arkan Hamid Ridho Baihaqi",
    "Dzulfikar Zain Djojosuroto",
    "Fahri Imanul Haq",
    "Faiz Rafi Ahmad",
    "Ikhsan Jovinando",
    "Ishaq Qudwah Mustaqim",
    "Isma'il Bin Hadi",
    "M. Salik Ilman",
    "Malik Arkan Safero Hakim",
    "Moch. Bintang Indradibta Sakti",
    "MUH. SHODIQUL WA'DUL AMIN",
    "Muhammad Adam Ridwanulloh",
    "Muhammad Afif Fadhlulloh",
    "Muhammad Al Faruq",
    "Muhammad Al Fatih",
    "Muhammad Helga Shafa",
    "Muhammad Ibrahim Nabhan",
    "MUHAMMAD IQBAL ABDULLAH",
    "Muhammad Jamil Musyaffa",
    "Muhammad Sulthon Hakim",
    "MUHAMMAD ZAIDAN 'ALIM",
    "Saif Syafi'",
    "Sokhifathir Raykhan",
    "Zaid Abdunnafi' Mosha",
    "Zaidan Hakim Rais Bahjatullah",
    "'AISYAH",
    "Azka Talitha",
    "Dina Puspita Riyana",
    "Dwi Noviyanti",
    "ERSA MAYA PRATIWI",
    "Fa'izah",
    "FADILAH ADIEN KHASANAH",
    "Fahdah Afifah",
    "FAZYRISNA SALSABILA",
    "GALIH AL RASYD ZAKARIYA",
    "Gefira Nur Hasyifa",
    "Halimah Pramudita Jati",
    "Hulda Aulia Nurcahya",
    "Irna Dwi Astuti",
    "LINGGAR NUR PENGGALIH",
    "Maimanatul Afifah",
    "miftakhul khoiriyah",
    "NABILA AMANY",
    "Nabilla Aghnie Fitri",
    "Nawang Ayudia Ramadhani",
    "Nia Kurniasih",
    "NOOR FAIZAH",
    "Nuha Budi Yafi'ah",
    "PUTRI DYAH AYUNINGTYAS",
    "Radita Fatma Hidayanti",
    "Rahma Fadhilah Khairun Nisa",
    "Reno Rahmadandi",
    "RESKA DWI UTOMO",
    "REYKHAN IKHSANUDDIN KAMIL",
    "Rifa Apriani",
    "Rina Fatmawati",
    "Shindy Nur Arifah",
    "Suci Puspitasari",
    "SYAHIDAH ASMA AMANINA",
    "TOMAS CANDRA MUKTI",
    "Zakiyah Salsabila",
    "Abdullah Azzam Mujahid",
    "AFFAN KAMIL AL GHIFARI",
    "BAYU AHMAD PRADANA",
    "Darus Sakti Wibawa",
    "Dian Putra Ramadhan",
    "FADZIL DAFIQ ROSYADI",
    "Fahmi Robbani",
    "faiz ridho Kharisma Wibowo",
    "GHUFRON MUNTAHA",
    "Hafizuddin Fathurrahman Jauhari",
    "IMAM SAIFUL AHMAD",
    "Irfan Dzulkarahman Lutfianto",
    "Muhammad  Zakky Abdillah",
    "Muhammad Kholis Kamaluddin",
    "Muhammad Saiful Fatah",
    "Muhammad Zaid Panji Al Hafiz",
    "Nabill Afdhol Rozzaq",
    "Naufal Abdullah",
    "RASYA PUTRA WARDANA",
    "Rizki Ari Wibowo",
    "UMMARUL YAHYA AL FAROUK",
    "ADITYA IRSYAD MAULANA",
    "Aditya Saputra",
    "AHMAD PONCO SEPTIAN",
    "Dany Fajri Fadholi",
    "DHIYA ULHAQ",
    "Ibnu Davin Al-Mazamir",
    "Kurniawan Syahrial Akbar",
    "MIKAILYYAS AKBAR",
    "MUHAMMAD AKMAL BURHANUDDIN",
    "Muhammad Iqbal Maulana",
    "Muhammad Qolbi",
    "MUHAMMAD RIFA'I",
    "Muhammad Rizqy Mubarok Hasri",
    "MUHAMMAD ZAKIRUN NI'AM",
    "Nur Rahman Namsa",
    "Sahri Sa'ban",
    "SATRIO RAMADAN",
    "SYAHDAN JAISYUR ROHMAN",
    "Zhafran Fatahillah Arifin",
];

// Jika file ada, baca kontennya dan ubah ke array
$file_path = 'paste.txt';
if (file_exists($file_path)) {
    $content = file_get_contents($file_path);
    $siswa = explode("\n", $content);
    $siswa = array_map('trim', $siswa);
    $siswa = array_filter($siswa); // Hapus baris kosong
}

// Loop dan tampilkan data

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Kelulusan SMA</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.12.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Merriweather:ital,wght@0,300;0,400;0,700;0,900;1,300;1,400;1,700;1,900&family=PT+Serif:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <style>
        .container {
            font-family: Merriweather;
        }

        .search-container {
            margin: 20px 0;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 14px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>

<!--  -->

<body>
    <div class="container">
        <div class="d-flex align-items-center my-4">
            <a href="index.php" class="btn me-3" style="font-size: 14px;">
                <i class="bi bi-chevron-left m-0 p-0"></i>
            </a>
            <h1 class="m-0 fw-bold" style="font-size: 16px;">Daftar Kelulusan SMA</h1>
        </div>

        <!-- Alert Box Info -->
        <div class="alert border bg-light mt-3" style="border-radius: 15px;">
            <div class="d-flex">
                <i class="bi bi-exclamation-circle-fill fs-4 me-3" style="font-size: 30px; color:rgb(219, 106, 68)"></i>
                <div>
                    <p class="fw-bold p-0 m-0" style="font-size: 14px;">Informasi Kelulusan</p>
                    <p class="p-0 m-0 text-muted" style="font-size: 12px;">
                        Hasil kelulusan ini berlaku kepada seluruh siswa yang telah menginduk SMA Muhamamdiyah 5 Gatak, keputusan ini telah final dan telah disahkan oleh pihak sekolah. <span class="d-block d-md-none"><br></span>
                        Silahkan gunakan kolom pencarian untuk menemukan nama Anda dengan cepat.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form Pencarian -->
        <div class="row search-container">
            <div class="col-md-6 mx-auto">
                <div class="input-group mb-3" style="border-radius: 15px; overflow: hidden;">
                    <input type="text" id="searchInput" class="form-control" placeholder="Cari nama..." aria-label="Cari nama" style="border: 1px solid #dee2e6; font-size: 14px;">
                    <button class="btn" type="button" onclick="searchData()" style="background-color: rgb(219, 106, 68); color: white; font-size: 14px;">Cari</button>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Kelulusan -->
        <div class="table-responsive" style="border-radius: 15px; overflow: hidden; border: 1px solid #dee2e6;">
            <table class="table table-hover mb-0">
                <thead style="background-color: rgb(245, 245, 245);">
                    <tr>
                        <th scope="col" class="fw-bold" style="font-size: 14px; color: #444;">No</th>
                        <th scope="col" class="fw-bold" style="font-size: 14px; color: #444;">Nama</th>
                        <th scope="col" class="fw-bold" style="font-size: 14px; color: #444;">Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <?php
                    $counter = 1;
                    foreach ($siswa as $nama) {
                        // Menentukan status kelulusan (semua dianggap LULUS)
                        $status = "LULUS";

                        echo '<tr class="search-item"> 
                                <td style="font-size: 12px;">' . $counter . '</td>
                                <td style="font-size: 12px;">' . $nama . '</td>
                                <td><span class="border rounded px-2 py-1" style="font-size: 12px; background-color: #e8f5e9; color: #2e7d32;">' . $status . '</span></td>
                              </tr>';
                        $counter++;
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Info Halaman -->
        <div class="text-center mt-3 mb-5">
            <p class="text-muted" style="font-size: 12px;">Total Siswa: <?php echo count($siswa); ?></p>
        </div>
    </div>

    <!-- Bootstrap JS dan Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript untuk Pencarian -->
    <script>
        function searchData() {
            const input = document.getElementById('searchInput');
            const filter = input.value.toUpperCase();
            const table = document.getElementById('tableBody');
            const tr = table.getElementsByClassName('search-item');

            // Hitung jumlah hasil pencarian
            let found = 0;

            for (let i = 0; i < tr.length; i++) {
                const td = tr[i].getElementsByTagName('td')[1]; // Kolom nama (indeks 1)
                if (td) {
                    const txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = '';
                        found++;
                    } else {
                        tr[i].style.display = 'none';
                    }
                }
            }

            // Tampilkan jumlah hasil pencarian jika sedang mencari
            if (filter) {
                document.querySelector('.text-center.mt-3.mb-5 p').textContent = `Ditemukan: ${found} siswa`;
            } else {
                document.querySelector('.text-center.mt-3.mb-5 p').textContent = `Total Siswa: ${tr.length}`;
            }
        }

        // Live search saat mengetik
        document.getElementById('searchInput').addEventListener('keyup', searchData);
    </script>
</body>

</html>
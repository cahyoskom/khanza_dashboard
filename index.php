<?php
// Database Config
include 'config.php';

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// Function to execute query and get count
function getCount($query) {
    global $conn;
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_array($result);
        return $row[0];
    }
    return 0;
}




// === SIMPLE DATE RANGE FILTER - PHP NATIVE ===
// Proses parameter tanggal
$tgl_awal = $_GET['tgl_awal'] ?? date('');  // Default awal bulan
$tgl_akhir = $_GET['tgl_akhir'] ?? date(''); // Default hari ini

// Proses parameter penjamin
$penjamin = $_GET['penjamin'] ?? 'BPJ';  // Default kosong (semua penjamin)


// Validasi format tanggal
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_awal)) $tgl_awal = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_akhir)) $tgl_akhir = date('Y-m-01');

// Sanitasi penjamin
$penjamin = trim(strip_tags($penjamin));

// Pastikan tgl_awal <= tgl_akhir
if ($tgl_awal > $tgl_akhir) {
    $temp = $tgl_awal;
    $tgl_awal = $tgl_akhir; 
    $tgl_akhir = $temp;
}

// Variables siap pakai untuk query
// echo "Periode: $tgl_awal sampai $tgl_akhir, Penjamin: $penjamin";
?>


<?php
// Dashboard queries - sesuaikan dengan struktur tabel Anda
$queries = array(
    'registrasi_total' => "     SELECT COUNT(*) FROM reg_periksa
                                WHERE 	1=1
                                        AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'",
    'registrasi_bpjs' => "      SELECT COUNT(*) FROM reg_periksa
                                WHERE 	1=1
                                        AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                        AND kd_pj = '$penjamin'",
                            
    'registrasi_batal' => "     SELECT COUNT(*) FROM reg_periksa 
                                WHERE 	1=1
                                        AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                        AND kd_pj = '$penjamin'
                                        AND stts = 'Batal'",
    'registrasi_non_batal' => " SELECT COUNT(*) FROM reg_periksa 
                                WHERE 	1=1
                                    AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                    AND kd_pj = '$penjamin'
                                    AND stts <> 'Batal'",
    'registrasi_rajal' => "     SELECT COUNT(*) FROM reg_periksa 
                                WHERE 	1=1
                                    AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                    AND kd_pj = '$penjamin'
                                    AND stts <> 'Batal'
                                    AND status_lanjut = 'Ralan'
                                    AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')",
    'registrasi_ranap' => "     SELECT COUNT(*) FROM reg_periksa 
                                WHERE 	1=1
                                    AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                    AND kd_pj = '$penjamin'
                                    AND stts <> 'Batal'
                                    AND status_lanjut = 'Ranap'
                                    AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')",
    'fo_sep_ralan' => "         SELECT COUNT(*) FROM bridging_sep bsep
                                LEFT JOIN reg_periksa rp 
                                    ON rp.no_rawat = bsep.no_rawat 
                                WHERE 	1=1
                                    AND tglsep BETWEEN '$tgl_awal' and '$tgl_akhir'
                                    AND kd_pj = '$penjamin'
                                    AND stts <> 'Batal'
                                    AND status_lanjut = 'Ralan'
                                    AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')",
    'fo_sep_ranap' => "         SELECT COUNT(*) FROM bridging_sep bsep
                                LEFT JOIN reg_periksa rp 
                                    ON rp.no_rawat = bsep.no_rawat 
                                WHERE 	1=1
                                    AND tglsep BETWEEN '$tgl_awal' and '$tgl_akhir'
                                    AND kd_pj = '$penjamin'
                                    AND stts <> 'Batal'
                                    AND status_lanjut = 'Ranap'
                                    AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') -- eksclude Radiologi, Laboratorium, VK, OK, PERINATOLOGI  
                                    AND bsep.nmpolitujuan <>''",
    'poli_ralan_soap' => "      SELECT COUNT(*) AS jumlah_group
                                FROM (
                                    SELECT rp.no_rawat, rp.status_lanjut
                                    FROM sik_rshj.reg_periksa rp
                                    LEFT JOIN sik_rshj.pemeriksaan_ralan pr
                                        ON rp.no_rawat = pr.no_rawat
                                    LEFT JOIN pegawai p 
                                        ON rp.kd_dokter = p.nik
                                    LEFT JOIN poliklinik pl
                                        ON rp.kd_poli = pl.kd_poli 
                                    WHERE 1=1
                                        AND rp.tgl_registrasi BETWEEN '$tgl_awal' AND '$tgl_akhir'
                                        AND rp.kd_pj = '$penjamin'
                                        AND rp.stts <> 'Batal'
                                        AND rp.status_lanjut = 'Ralan'
                                        AND rp.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')
                                        AND pr.pemeriksaan <> ''
                                    GROUP BY rp.no_rawat, rp.status_lanjut
                                ) AS grouped_data",
    'kasir_ralan' => "          SELECT COUNT(*) AS jumlah_group
                                FROM (
                                    SELECT 
                                        bil.no_rawat
                                    FROM sik_rshj.billing bil
                                    WHERE 	1=1
                                    --	AND tgl_byr BETWEEN '$tgl_awal' and '$tgl_akhir'
                                        AND bil.no = 'No.Nota'
                                        AND bil.no_rawat IN (
                                                                SELECT 
                                                                    x.no_rawat 
                                                                FROM sik_rshj.reg_periksa x
                                                                WHERE 	1=1
                                                                        AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                                                        AND kd_pj = '$penjamin'
                                                                        AND stts <> 'Batal'
                                                                        AND status_lanjut = 'Ralan'
                                                                        AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') 
                                                                        AND status_bayar = 'Sudah Bayar'
                                        )
                                    GROUP BY
                                        bil.no_rawat,
                                        bil.tgl_byr, 
                                        bil.nm_perawatan
                                ) AS grouped_data  ",
    'kasir_ranap' => "          SELECT COUNT(*) AS jumlah_group
                                FROM (
                                    SELECT 
                                        bil.no_rawat
                                    FROM sik_rshj.billing bil
                                    WHERE 	1=1
                                    --	AND tgl_byr BETWEEN '$tgl_awal' and '$tgl_akhir'
                                        AND bil.no = 'No.Nota'
                                        AND bil.no_rawat IN (
                                                                SELECT 
                                                                    x.no_rawat 
                                                                FROM sik_rshj.reg_periksa x
                                                                WHERE 	1=1
                                                                        AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                                                        AND kd_pj = '$penjamin'
                                                                        AND stts <> 'Batal'
                                                                        AND status_lanjut = 'Ranap'
                                                                        AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') 
                                                                        AND status_bayar = 'Sudah Bayar'
                                        )
                                    GROUP BY
                                        bil.no_rawat,
                                        bil.tgl_byr, 
                                        bil.nm_perawatan
                                ) AS grouped_data  ",
    'rm_ralan' => "             SELECT COUNT(*) AS jumlah_group
                                FROM (
                                        SELECT rp.no_rawat 
                                        FROM sik_rshj.reg_periksa rp
                                            LEFT JOIN pemeriksaan_ralan pr 
                                                ON rp.no_rawat = pr.no_rawat 
                                            LEFT JOIN resume_pasien rp2
                                                ON rp.no_rawat =rp2.no_rawat
                                            LEFT JOIN pegawai p 
                                                ON p.nik = rp.kd_dokter 
                                            LEFT JOIN poliklinik poli
                                                ON poli.kd_poli = rp.kd_poli 
                                            LEFT JOIN data_triase_igd dti 
                                                ON rp.no_rawat = dti.no_rawat 
                                            LEFT JOIN diagnosa_pasien dp
                                                ON rp.no_rawat = dp.no_rawat 
                                            LEFT JOIN prosedur_pasien pp 
                                                ON rp.no_rawat = pp.no_rawat 		
                                        WHERE 	1=1
                                                AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                                AND rp.kd_pj = '$penjamin'
                                                AND rp.stts <> 'Batal'
                                                AND rp.status_lanjut = 'Ralan'
                                                AND rp.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') -- eksclude Radiologi, Laboratorium, VK, OK, PERINATOLOGI  
                                        GROUP BY 
                                            rp.no_rawat,
                                            rp.no_rkm_medis
                                ) AS grouped_data",
    'rm_ranap' => "             SELECT COUNT(*) AS jumlah_group
                                FROM (
                                        SELECT rp.no_rawat 
                                        FROM sik_rshj.reg_periksa rp
                                            LEFT JOIN pemeriksaan_ralan pr 
                                                ON rp.no_rawat = pr.no_rawat 
                                            LEFT JOIN resume_pasien rp2
                                                ON rp.no_rawat =rp2.no_rawat
                                            LEFT JOIN pegawai p 
                                                ON p.nik = rp.kd_dokter 
                                            LEFT JOIN poliklinik poli
                                                ON poli.kd_poli = rp.kd_poli 
                                            LEFT JOIN data_triase_igd dti 
                                                ON rp.no_rawat = dti.no_rawat 
                                            LEFT JOIN diagnosa_pasien dp
                                                ON rp.no_rawat = dp.no_rawat 
                                            LEFT JOIN prosedur_pasien pp 
                                                ON rp.no_rawat = pp.no_rawat 		
                                        WHERE 	1=1
                                                AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                                AND rp.kd_pj = '$penjamin'
                                                AND rp.stts <> 'Batal'
                                                AND rp.status_lanjut = 'ranap'
                                                AND rp.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') -- eksclude Radiologi, Laboratorium, VK, OK, PERINATOLOGI  
                                        GROUP BY 
                                            rp.no_rawat,
                                            rp.no_rkm_medis
                                ) AS grouped_data"
);

// Get counts
$counts = array();
foreach ($queries as $key => $query) {
    $counts[$key] = getCount($query);
}


// Card configurations
$cards = array(
    array('title' => 'Data Registrasi FO Pasien BPJS Kesehatan', 'count' => $counts['registrasi_bpjs'], 'icon' => '👥', 'color' => 'bg-blue-500', 'link' => 'detail.php?type=registrasi_bpjs'),
    array('title' => 'Data Registrasi Pasien Batal FO', 'count' => $counts['registrasi_batal'], 'icon' => '❌', 'color' => 'bg-red-500', 'link' => 'detail.php?type=registrasi_batal'),
    array('title' => 'Data Registrasi Pasien non Batal FO', 'count' => $counts['registrasi_non_batal'], 'icon' => '✅', 'color' => 'bg-green-500', 'link' => 'detail.php?type=registrasi_non_batal'),
    array('title' => 'Data Registrasi Pasien Rajal/Ralan FO', 'count' => $counts['registrasi_rajal'], 'icon' => '🏥', 'color' => 'bg-purple-500', 'link' => 'detail.php?type=registrasi_rajal'),
    array('title' => 'Data Registrasi Pasien Ranap FO', 'count' => $counts['registrasi_ranap'], 'icon' => '🛏️', 'color' => 'bg-indigo-500', 'link' => 'detail.php?type=registrasi_ranap'),
    array('title' => 'FO SEP check Ralan', 'count' => $counts['fo_sep_ralan'], 'icon' => '🔍', 'color' => 'bg-cyan-500', 'link' => 'detail.php?type=fo_sep_ralan'),
    array('title' => 'FO SEP check Ranap', 'count' => $counts['fo_sep_ranap'], 'icon' => '🔎', 'color' => 'bg-blue-600', 'link' => 'detail.php?type=fo_sep_ranap'),
    array('title' => 'POLI Ralan (cek inputan soap Ralan)', 'count' => $counts['poli_ralan_soap'], 'icon' => '📝', 'color' => 'bg-pink-500', 'link' => 'detail.php?type=poli_ralan_soap'),
    array('title' => 'KASIR cek Ralan', 'count' => $counts['kasir_ralan'], 'icon' => '💳', 'color' => 'bg-orange-500', 'link' => 'detail.php?type=kasir_ralan'),
    array('title' => 'KASIR cek Ranap', 'count' => $counts['kasir_ranap'], 'icon' => '💵', 'color' => 'bg-red-600', 'link' => 'detail.php?type=kasir_ranap'),
    array('title' => 'RM CEK RALAN (SOAP,RESUME,TRIASE IGD, ICD 09, ICD 10)', 'count' => $counts['rm_ralan'], 'icon' => '📊', 'color' => 'bg-gray-600', 'link' => 'detail.php?type=rm_ralan'),
    array('title' => 'RM CEK RANAP (SOAP,RESUME,TRIASE IGD, ICD 09, ICD 10)', 'count' => $counts['rm_ranap'], 'icon' => '📈', 'color' => 'bg-slate-600', 'link' => 'detail.php?type=rm_ranap')
);
?>



<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SIMRS - Monitoring Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-gray-900">🏥 Dashboard SIMRS</h1>
                    <span class="ml-3 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                        Monitoring Real-time
                    </span>
                </div>
                <div class="text-sm text-gray-500">
                    <span id="current-time"></span>
                </div>
            </div>
        </div>
        <!-- Filter Tanggal -->
        <div class="bg-white rounded-lg shadow-sm p-4 mb-6">
            <form method="GET" class="flex flex-wrap items-center gap-3 justify-center">
                <!-- Untuk Tambahan GET Parameter Lain -->
                <?php foreach($_GET as $key => $value): ?>
                    <?php if (!in_array($key, ['tgl_awal', 'tgl_akhir', 'penjamin'])): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">📅 Dari:</label>
                    <input type="date" name="tgl_awal" value="<?php echo $tgl_awal; ?>" 
                        class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-gray-700">📅 Sampai:</label>
                    <input type="date" name="tgl_akhir" value="<?php echo $tgl_akhir; ?>" 
                        class="px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md transition-colors">
                    🔍 Cari
                </button>
                
                <a href="?" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md transition-colors text-decoration-none">
                    🔄 Reset
                </a>
            </form>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Ringkasan Summary  -->
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">📊 Summary Hari Ini</h2>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-600"><?php  echo $counts['registrasi_total']; ?></div>
                        <div class="text-sm text-gray-500">Total Data</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-600"><?php echo $counts['registrasi_non_batal']; ?></div>
                        <div class="text-sm text-gray-500">Registrasi Aktif</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-red-600"><?php echo $counts['registrasi_batal']; ?></div>
                        <div class="text-sm text-gray-500">Registrasi Batal</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-purple-600 whitespace-nowrap">
                            <?php 
                                echo date('d/m/Y', strtotime($tgl_awal)) . ' - ' . date('d/m/Y', strtotime($tgl_akhir)); 
                            ?>
                        </div>
                        <div class="text-sm text-gray-500">Periode</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php foreach ($cards as $card): ?>
            <div class="card-hover">
                <a href="<?php echo $card['link'].'&tgl_awal='.$tgl_awal.'&tgl_akhir='.$tgl_akhir; ?>" class="block" target="_blank"> 
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:border-gray-300">
                        <!-- Card Header -->
                        <div class="<?php echo $card['color']; ?> px-4 py-3">
                            <div class="flex items-center justify-between text-white">
                                <span class="text-2xl"><?php echo $card['icon']; ?></span>
                                <span class="text-2xl font-bold"><?php echo number_format($card['count']); ?></span>
                            </div>
                        </div>
                        
                        <!-- Card Body -->
                        <div class="p-4">
                            <h3 class="text-sm font-medium text-gray-900 mb-2 leading-tight">
                                <?php echo $card['title']; ?>
                            </h3>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">Klik untuk detail</span>
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                        
                        <!-- Indikator Progress -->
                        <div class="h-1 bg-gray-100">
                            <div class="h-full <?php echo $card['color']; ?> opacity-20" style="width: <?php echo min(100, ($card['count'] / 50) * 100); ?>%"></div>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Refresh Button -->
        <div class="mt-8 text-center">
            <button onclick="location.reload()" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                🔄 Refresh Data
            </button>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="text-center text-sm text-gray-500">
                <p>© <?php echo date('Y'); ?> Dashboard SIMRS - Sistem Informasi Manajemen Rumah Sakit</p>
                <p class="mt-1">Last updated: <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
        </div>
    </footer>

    <script>
        // Update Waktu Sekarang
        function updateTime() {
            const now = new Date();
            document.getElementById('current-time').textContent = now.toLocaleString('id-ID');
        }
        
        updateTime();
        setInterval(updateTime, 1000);

        // Auto refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>


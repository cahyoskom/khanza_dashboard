<?php
// Database
include 'config.php';

// Set charset dan timezone
mysqli_set_charset($conn, "utf8");
mysqli_query($conn, "SET time_zone = '+07:00'");

// Format tanggal ke "01 Agustus 2025"
function formatTanggalIndo($tanggal) {
    $bulanIndo = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $tgl = date('j', strtotime($tanggal));
    $bln = $bulanIndo[(int)date('n', strtotime($tanggal))];
    $thn = date('Y', strtotime($tanggal));
    return "$tgl $bln $thn";
}


// Ambil parameter umum
$type      = $_GET['type'] ?? 'registrasi_bpjs';
$tgl_awal  = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');

// Mapping type ke query
switch ($type) {
    case 'registrasi_bpjs':
        $judul = 'Data FO Registrasi Pasien BPJS Kesehatan';
        $sql = "SELECT 
                    r.no_rawat AS 'No Rawat', 
                    r.no_rkm_medis AS 'No RM', 
                    p.nm_pasien AS 'Nama Pasien', 
                    r.tgl_registrasi AS 'Tgl Resitrasi', 
                    d.nm_dokter AS 'Dokter', 
                    pl.nm_poli AS 'Poliklinik'
                FROM reg_periksa r
                    LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
                    LEFT JOIN dokter d ON d.kd_dokter = r.kd_dokter
                    LEFT JOIN poliklinik pl ON pl.kd_poli = r.kd_poli
                WHERE 1=1
                    AND r.tgl_registrasi BETWEEN '$tgl_awal' AND '$tgl_akhir'
                    AND r.kd_pj = 'BPJ'
        ";
        break;

    case 'registrasi_batal':
        $judul = 'Data FO Registrasi Pasien Batal';
        $sql = "SELECT 
                        r.no_rawat AS 'No Rawat', 
                        r.no_rkm_medis AS 'No RM', 
                        p.nm_pasien AS 'Nama Pasien', 
                        r.tgl_registrasi AS 'Tgl Registrasi', 
                        d.nm_dokter AS 'Dokter', 
                        pl.nm_poli AS 'Poliklinik'
                FROM reg_periksa r
                        LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
                        LEFT JOIN dokter d ON d.kd_dokter = r.kd_dokter
                        LEFT JOIN poliklinik pl ON pl.kd_poli = r.kd_poli
                WHERE   1=1
                        AND r.tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND r.kd_pj = 'BPJ'
                        AND r.stts = 'Batal'
        ";
        break;

    case 'registrasi_non_batal':
        $judul = 'Data FO Registrasi Pasien Non Batal';
        $sql = "SELECT 
                        r.no_rawat AS 'No Rawat', 
                        r.no_rkm_medis AS 'No RM', 
                        p.nm_pasien AS 'Nama Pasien', 
                        r.tgl_registrasi AS 'Tgl Registrasi', 
                        d.nm_dokter AS 'Dokter', 
                        pl.nm_poli AS 'Poliklinik'
                FROM reg_periksa r
                        LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
                        LEFT JOIN dokter d ON d.kd_dokter = r.kd_dokter
                        LEFT JOIN poliklinik pl ON pl.kd_poli = r.kd_poli
                WHERE   1=1
                        AND r.tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND r.kd_pj = 'BPJ'
                        AND r.stts <> 'Batal'
        ";
        break;

    case 'registrasi_rajal':
        $judul = 'Data FO Registrasi Pasien Rawat Jalan';
        $sql = "SELECT 
                        r.no_rawat AS 'No Rawat', 
                        r.no_rkm_medis AS 'No RM', 
                        p.nm_pasien AS 'Nama Pasien', 
                        r.tgl_registrasi AS 'Tgl Registrasi', 
                        d.nm_dokter AS 'Dokter', 
                        pl.nm_poli AS 'Poliklinik'
                FROM reg_periksa r
                        LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
                        LEFT JOIN dokter d ON d.kd_dokter = r.kd_dokter
                        LEFT JOIN poliklinik pl ON pl.kd_poli = r.kd_poli
                WHERE   1=1
                        AND r.tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND r.kd_pj = 'BPJ'
                        AND r.stts <> 'Batal'
                        AND r.status_lanjut = 'Ralan'
                        AND r.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')
        ";
        break;

    case 'registrasi_ranap':
        $judul = 'Data FO Registrasi Pasien Rawat Inap';
        $sql = "SELECT 
                        r.no_rawat AS 'No Rawat', 
                        r.no_rkm_medis AS 'No RM', 
                        p.nm_pasien AS 'Nama Pasien', 
                        r.tgl_registrasi AS 'Tgl Registrasi', 
                        d.nm_dokter AS 'Dokter', 
                        pl.nm_poli AS 'Poliklinik'
                FROM reg_periksa r
                        LEFT JOIN pasien p ON p.no_rkm_medis = r.no_rkm_medis
                        LEFT JOIN dokter d ON d.kd_dokter = r.kd_dokter
                        LEFT JOIN poliklinik pl ON pl.kd_poli = r.kd_poli
                WHERE   1=1
                        AND r.tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND r.kd_pj = 'BPJ'
                        AND r.stts <> 'Batal'
                        AND r.status_lanjut = 'Ranap'
                        AND r.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')
        ";
        break;

    case 'fo_sep_ralan':
        $judul = 'Data FO SEP RAWAT JALAN';
        $sql = "SELECT 
                        bsep.no_sep AS 'No SEP',
                        bsep.tglsep AS 'Tanggal SEP',
                        bsep.no_rawat AS 'No rawat', 
                        bsep.nomr AS 'No RM', 
                        bsep.nama_pasien AS 'Nama Pasien', 
                        bsep.tanggal_lahir AS 'Tgl Lahir', 
                        bsep.nmdpdjp AS 'Dokter', 
                        bsep.nmpolitujuan AS 'Poliklinik'
                FROM bridging_sep bsep
                    LEFT JOIN reg_periksa r 
                        ON r.no_rawat = bsep.no_rawat 
                WHERE 	1=1
                        AND bsep.tglsep BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND r.kd_pj = 'BPJ'
                        AND r.stts <> 'Batal'
                        AND r.status_lanjut = 'Ralan'
                        AND r.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')                
        ";
        break;

    case 'fo_sep_ranap':
        $judul = 'Data FO SEP RAWAT INAP';
        $sql = "SELECT 
                        bsep.no_sep AS 'No SEP',
                        bsep.tglsep AS 'Tanggal SEP',
                        bsep.no_rawat AS 'No rawat', 
                        bsep.nomr AS 'No RM', 
                        bsep.nama_pasien AS 'Nama Pasien', 
                        bsep.tanggal_lahir AS 'Tgl Lahir', 
                        bsep.nmdpdjp AS 'Dokter', 
                        bsep.nmpolitujuan AS 'Poliklinik'
                FROM bridging_sep bsep
                    LEFT JOIN reg_periksa r 
                        ON r.no_rawat = bsep.no_rawat 
                WHERE 	1=1
                        AND bsep.tglsep BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND r.kd_pj = 'BPJ'
                        AND r.stts <> 'Batal'
                        AND r.status_lanjut = 'Ranap'
                        AND r.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')
                        AND bsep.nmpolitujuan <>''                
        ";
        break;

    case 'poli_ralan_soap':
        $judul = 'Data POLIKLINIK Inputan SOAP';
        $sql = "SELECT 
						rp.no_rawat AS 'No Rawat',
						pl.nm_poli AS 'Poliklinik',
						p.nama AS 'Nama Pasien',
						IF(count(*) > 0,'ADA','TIDAK ADA') AS 'SOAP',
						count(*) AS 'Jml SOAP',
						pr.keluhan as 'Keluhan (S)',
						pr.pemeriksaan as 'Pemeriksaan (O)',
						pr.penilaian  as 'Penilaian (A)',
						pr.rtl as 'Rencana Tindak Lanjut (P)'
					FROM sik_rshj.reg_periksa rp
						LEFT JOIN sik_rshj.pemeriksaan_ralan pr
							ON rp.no_rawat = pr.no_rawat
						LEFT JOIN pegawai p 
							ON rp.kd_dokter = p.nik
						LEFT JOIN poliklinik pl
							ON rp.kd_poli = pl.kd_poli 
					WHERE 	1=1
							AND rp.tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
							AND rp.kd_pj = 'BPJ'
							AND rp.stts <> 'Batal'
							AND rp.status_lanjut = 'Ralan'
							AND rp.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035')
							AND pr.pemeriksaan <> ''
					GROUP BY 
							rp.no_rawat,
							rp.status_lanjut            
        ";
        break;

    case 'kasir_ralan':
        $judul = 'Data KASIR RAWAT JALAN';
        $sql = "SELECT 
						grouped_data.no_rawat AS 'No Rawat',
                        p.no_rkm_medis AS 'No RM',
                        p.nm_pasien AS 'Nama Pasien',
                        pl.nm_poli AS 'Poliklinik',
                        DATE_FORMAT(tgl_byr, '%d-%M-%Y') AS 'Tanggal Closing',  
                        REPLACE(nm_perawatan, ':', '') AS 'No Nota',
                        r.status_bayar AS 'Status Bayar'
					FROM (
                                    SELECT 
                                        bil.*
                                    FROM sik_rshj.billing bil
                                    WHERE 	1=1
                                        AND bil.no = 'No.Nota'
                                        AND bil.no_rawat IN (
                                                                SELECT 
                                                                    x.no_rawat 
                                                                FROM sik_rshj.reg_periksa x
                                                                WHERE 	1=1
                                                                        AND x.tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                                                        AND kd_pj = 'BPJ'
                                                                        AND stts <> 'Batal'
                                                                        AND status_lanjut = 'Ralan'
                                                                        AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') 
                                                                        AND status_bayar = 'Sudah Bayar'
                                        )
                                    GROUP BY
                                        bil.no_rawat,
                                        bil.tgl_byr, 
                                        bil.nm_perawatan
                                ) AS grouped_data
                                LEFT JOIN sik_rshj.reg_periksa r ON r.no_rawat = grouped_data.no_rawat
                                LEFT JOIN sik_rshj.pasien p ON r.no_rkm_medis = p.no_rkm_medis
                                LEFT JOIN sik_rshj.poliklinik pl ON r.kd_poli = pl.kd_poli
        ";
        break;

    case 'kasir_ranap':
        $judul = 'Data KASIR RAWAT INAP';
        $sql = "SELECT 
						grouped_data.no_rawat AS 'No Rawat',
                        p.no_rkm_medis AS 'No RM',
                        p.nm_pasien AS 'Nama Pasien',
                        pl.nm_poli AS 'Asal Poli',
                        DATE_FORMAT(tgl_byr, '%d-%M-%Y') AS 'Tanggal Closing', 
                        REPLACE(nm_perawatan, ':', '') AS 'No Nota',
                        r.status_bayar AS 'Status Bayar'
					FROM (
                                    SELECT 
                                        bil.*
                                    FROM sik_rshj.billing bil
                                    WHERE 	1=1
                                        AND bil.no = 'No.Nota'
                                        AND bil.no_rawat IN (
                                                                SELECT 
                                                                    x.no_rawat 
                                                                FROM sik_rshj.reg_periksa x
                                                                WHERE 	1=1
                                                                        AND x.tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                                                                        AND kd_pj = 'BPJ'
                                                                        AND stts <> 'Batal'
                                                                        AND status_lanjut = 'Ranap'
                                                                        AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') 
                                                                        AND status_bayar = 'Sudah Bayar'
                                        )
                                    GROUP BY
                                        bil.no_rawat,
                                        bil.tgl_byr, 
                                        bil.nm_perawatan
                                ) AS grouped_data
                                LEFT JOIN sik_rshj.reg_periksa r ON r.no_rawat = grouped_data.no_rawat
                                LEFT JOIN sik_rshj.pasien p ON r.no_rkm_medis = p.no_rkm_medis
                                LEFT JOIN sik_rshj.poliklinik pl ON r.kd_poli = pl.kd_poli
        ";
        break;

    case 'rm_ralan':
        $judul = 'Data RM RAWAT JALAN';
        $sql = "SELECT 
                    rp.no_rawat AS 'No Rawat',
                    rp.no_rkm_medis AS 'No RM',
                    p2.nm_pasien AS 'Nama Pasien',
                    -- pr.nip AS '',
                    p.nama AS 'Nama Petugas',
                    poli.nm_poli AS 'Poliklinik',
                    IF(rp2.diagnosa_utama IS NOT NULL,'Ada','Tidak Ada') AS 'Resume Ralan',
                    IFNULL(dti.tgl_kunjungan,'Tidak Ada') AS 'Triase IGD',
                    dp.kd_penyakit as 'ICD 10',
                    pp.kode as 'ICD 9'
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
                    LEFT JOIN sik_rshj.pasien p2 
                        ON rp.no_rkm_medis = p2.no_rkm_medis 		
                WHERE 	1=1
                        AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND rp.kd_pj = 'BPJ'
                        AND rp.stts <> 'Batal'
                        AND rp.status_lanjut = 'Ralan'
                        AND rp.kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') -- eksclude Radiologi, Laboratorium, VK, OK, PERINATOLOGI  
                GROUP BY 
                    rp.no_rawat,
                    rp.no_rkm_medis
        ";
        break;

    case 'rm_ranap':
        $judul = 'Data RM RAWAT INAP';
        $sql = "SELECT 
                    rp.no_rawat AS 'No Rawat',
                    rp.no_rkm_medis AS 'No RM',
                    p2.nm_pasien AS 'Nama Pasien',
                    pr.nip AS 'Nama User',
                    IF(rp2.diagnosa_utama IS NOT NULL,'Ada','Tidak Ada') AS 'Resume Ranap',
                    IFNULL(dti.tgl_kunjungan,'Tidak Ada') AS 'Triase IGD',
                    dp.kd_penyakit as 'ICD 10',
                    pp.kode as 'ICD 9'
                FROM sik_rshj.reg_periksa rp
                    LEFT JOIN pemeriksaan_ranap pr 
                        ON rp.no_rawat = pr.no_rawat 
                    LEFT JOIN resume_pasien_ranap rp2
                        ON rp.no_rawat =rp2.no_rawat 
                    LEFT JOIN data_triase_igd dti 
                        ON rp.no_rawat = dti.no_rawat 
                    LEFT JOIN diagnosa_pasien dp
                        ON rp.no_rawat = dp.no_rawat 
                    LEFT JOIN prosedur_pasien pp 
                        ON rp.no_rawat = pp.no_rawat
                    LEFT JOIN sik_rshj.pasien p2 
                        ON rp.no_rkm_medis = p2.no_rkm_medis 	 		
                WHERE 	1=1
                        AND tgl_registrasi BETWEEN '$tgl_awal' and '$tgl_akhir'
                        AND rp.kd_pj = 'BPJ'
                        AND stts <> 'Batal'
                        AND status_lanjut = 'ranap'
                        AND kd_poli NOT IN ('U0005','U0006','U0007','U0008','U0035') -- eksclude Radiologi, Laboratorium, VK, OK, PERINATOLOGI  
                GROUP BY 
                    rp.no_rawat,
                    rp.no_rkm_medis
        ";
        break;

    default:
        die("Type data tidak dikenali!");
}

// Jalankan query
$result = $conn->query($sql);
if (!$result) {
    die("Query error: " . $conn->error);
}

// Judul halaman (hapus underscore & kapitalisasi awal kata)
$title = strtoupper(str_replace('_', ' ', $type));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($title); ?></title>

<!-- jQuery download dan taruh di folder lokal kalau tidak ada jaringan internet-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- DataTables CSS & JS download dan taruh di folder lokal kalau tidak ada jaringan internet-->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js"></script>

<style>
    body { font-family: Arial, sans-serif; font-size: 14px; padding: 10px; }
    table.dataTable thead th { background: #f2f2f2; }
</style>
</head>
<body>

<h2><?php echo htmlspecialchars($title); ?></h2>
<p>
    Periode: <?php echo formatTanggalIndo($tgl_awal); ?> s/d <?php echo formatTanggalIndo($tgl_akhir); ?>
</p>

<table id="tabelku" class="display nowrap" style="width:100%">
    <thead>
        <tr>
            <?php
            if ($result->num_rows > 0) {
                foreach ($result->fetch_fields() as $f) {
                    echo "<th>{$f->name}</th>";
                }
            }
            ?>
        </tr>
    </thead>
    <tbody>
        <?php
        $result->data_seek(0);
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($row as $col) {
                echo "<td>" . htmlspecialchars($col) . "</td>";
            }
            echo "</tr>";
        }
        ?>
    </tbody>
</table>

<script>
$(document).ready(function() {
    $('#tabelku').DataTable({
        responsive: true,
        scrollX: true,
        scrollY: "400px",
        scrollCollapse: true,
        paging: true,
        searching: true,
        ordering: true
    });
});
</script>

</body>
</html>
<?php $conn->close(); ?>


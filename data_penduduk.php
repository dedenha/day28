<?php
require 'vendor/autoload.php'; // Pastikan autoload di-include

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;

// Sample data penduduk
$dataPenduduk = [
    ['Nama' => 'Andi', 'Usia' => 25, 'Alamat' => 'Jl. Sudirman', 'Pekerjaan' => 'Programmer'],
    ['Nama' => 'Budi', 'Usia' => 30, 'Alamat' => 'Jl. Thamrin', 'Pekerjaan' => 'Designer'],
    ['Nama' => 'Cici', 'Usia' => 28, 'Alamat' => 'Jl. Merdeka', 'Pekerjaan' => 'Marketing'],
    ['Nama' => 'Dewi', 'Usia' => 35, 'Alamat' => 'Jl. Kebon Jeruk', 'Pekerjaan' => 'Programmer'],
];

// Cek jika ada pencarian
$searchResult = [];
if (isset($_POST['search'])) {
    $search = strtolower($_POST['search']);
    foreach ($dataPenduduk as $person) {
        if (
            strpos(strtolower($person['Nama']), $search) !== false ||
            strpos(strtolower($person['Pekerjaan']), $search) !== false
        ) {
            $searchResult[] = $person;
        }
    }
} else {
    $searchResult = $dataPenduduk; // Jika tidak ada pencarian, tampilkan semua data
}

// Simpan data ke file CSV
if (isset($_POST['export_csv'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama');
    $sheet->setCellValue('B1', 'Usia');
    $sheet->setCellValue('C1', 'Alamat');
    $sheet->setCellValue('D1', 'Pekerjaan');

    $row = 2;
    foreach ($searchResult as $person) {
        $sheet->setCellValue('A' . $row, $person['Nama']);
        $sheet->setCellValue('B' . $row, $person['Usia']);
        $sheet->setCellValue('C' . $row, $person['Alamat']);
        $sheet->setCellValue('D' . $row, $person['Pekerjaan']);
        $row++;
    }

    $writer = new Csv($spreadsheet);
    $writer->save('data_penduduk.csv');
    echo "Data berhasil diekspor ke data_penduduk.csv";
}

// Simpan data ke file XLSX
if (isset($_POST['export_xlsx'])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'Nama');
    $sheet->setCellValue('B1', 'Usia');
    $sheet->setCellValue('C1', 'Alamat');
    $sheet->setCellValue('D1', 'Pekerjaan');

    $row = 2;
    foreach ($searchResult as $person) {
        $sheet->setCellValue('A' . $row, $person['Nama']);
        $sheet->setCellValue('B' . $row, $person['Usia']);
        $sheet->setCellValue('C' . $row, $person['Alamat']);
        $sheet->setCellValue('D' . $row, $person['Pekerjaan']);
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $writer->save('data_penduduk.xlsx');
    echo "Data berhasil diekspor ke data_penduduk.xlsx";
}

// Menghasilkan laporan dalam PDF
if (isset($_POST['generate_report'])) {
    $dompdf = new Dompdf();
    $html = '<h1>Laporan Data Penduduk</h1><table border="1" cellpadding="5">
                <tr>
                    <th>Nama</th>
                    <th>Usia</th>
                    <th>Alamat</th>
                    <th>Pekerjaan</th>
                </tr>';

    foreach ($searchResult as $person) {
        $html .= '<tr>
                    <td>' . htmlspecialchars($person['Nama']) . '</td>
                    <td>' . htmlspecialchars($person['Usia']) . '</td>
                    <td>' . htmlspecialchars($person['Alamat']) . '</td>
                    <td>' . htmlspecialchars($person['Pekerjaan']) . '</td>
                  </tr>';
    }
    $html .= '</table>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('laporan_data_penduduk.pdf', ['Attachment' => true]);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Penduduk</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <h1>Data Penduduk</h1>

    <form method="POST" action="">
        <input type="text" name="search" placeholder="Cari Nama atau Pekerjaan">
        <button type="submit">Cari</button>
    </form>

    <form method="POST" action="">
        <button type="submit" name="export_csv">Ekspor ke CSV</button>
        <button type="submit" name="export_xlsx">Ekspor ke XLSX</button>
        <button type="submit" name="generate_report">Buat Laporan PDF</button>
    </form>

    <table border="1">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Usia</th>
                <th>Alamat</th>
                <th>Pekerjaan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($searchResult as $person): ?>
                <tr>
                    <td><?php echo htmlspecialchars($person['Nama']); ?></td>
                    <td><?php echo htmlspecialchars($person['Usia']); ?></td>
                    <td><?php echo htmlspecialchars($person['Alamat']); ?></td>
                    <td><?php echo htmlspecialchars($person['Pekerjaan']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Grafik Jumlah Penduduk Berdasarkan Pekerjaan</h2>
    <canvas id="pendudukChart" width="400" height="200"></canvas>
    <script>
        const ctx = document.getElementById('pendudukChart').getContext('2d');
        const data = {
            labels: [],
            datasets: [{
                label: 'Jumlah Penduduk',
                data: [],
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        // Menghitung jumlah penduduk berdasarkan pekerjaan
        const pekerjaanCount = {};
        <?php foreach ($dataPenduduk as $person): ?>
            pekerjaanCount['<?php echo $person['Pekerjaan']; ?>'] = (pekerjaanCount['<?php echo $person['Pekerjaan']; ?>'] || 0) + 1;
        <?php endforeach; ?>

        // Memasukkan data ke dalam grafik
        for (const [key, value] of Object.entries(pekerjaanCount)) {
            data.labels.push(key);
            data.datasets[0].data.push(value);
        }

        const myChart = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>

</html>
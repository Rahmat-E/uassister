<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization,X-Requested-With");


if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ========================
// KONEKSI DATABASE
// ========================
require 'Database.php'; // pastikan Database.php sudah pakai PDO

// ========================
// DAPATKAN METHOD DAN ENDPOINT
// ========================
$method = $_SERVER['REQUEST_METHOD'];


// Ambil endpoint terakhir dari URL, contoh: /server.php/menu
// Ambil endpoint dari URL (fix untuk PHP 5.6)
$path = '';
if (isset($_SERVER['PATH_INFO'])) {
    $path = $_SERVER['PATH_INFO'];
} elseif (isset($_SERVER['REQUEST_URI'])) {
    $uriParts = explode('server.php', $_SERVER['REQUEST_URI']);
    if (count($uriParts) > 1) {
        $path = $uriParts[1];
    }
}
$path = trim($path, '/');
$endpointParts = explode('/', $path);
$endpoint = isset($endpointParts[0]) ? $endpointParts[0] : '';


// ========================
// ROUTING SIMPLE
// ========================
switch ($endpoint) {

    // ==================================================
    // =============== ENDPOINT MENU =====================
    // ==================================================
    case 'menu':
        if ($method === 'GET') {
            $stmt = $pdo->query("SELECT * FROM menu");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }

        elseif ($method === 'POST') {
            $data = json_decode(file_get_contents("php://input"), true);
            if ($data && isset($data['nama_menu'], $data['harga'], $data['kategori'], $data['stok'])) {
                $stmt = $pdo->prepare("INSERT INTO menu (nama_menu, harga, kategori, stok) VALUES (?, ?, ?, ?)");
                $stmt->execute(array($data['nama_menu'], $data['harga'], $data['kategori'], $data['stok']));
                echo json_encode(array("message" => "Menu berhasil ditambahkan"));
            } else {
                echo json_encode(array("error" => "Data tidak lengkap"));
            }
        }
        elseif ($method === 'PUT') {
    $rawData = file_get_contents("php://input");
    $data = json_decode($rawData, true);

    // fallback untuk PHP 5.6 yang kadang gagal baca JSON
    if (!$data) {
        parse_str($rawData, $data);
    }

    $id = isset($_GET['id_menu']) ? intval($_GET['id_menu']) : null;

    if ($id && isset($data['nama_menu'], $data['harga'], $data['kategori'], $data['stok'])) {
        $stmt = $pdo->prepare("UPDATE menu SET nama_menu = ?, harga = ?, kategori = ?, stok = ? WHERE id_menu = ?");
        $stmt->execute(array($data['nama_menu'], $data['harga'], $data['kategori'], $data['stok'], $id));
        echo json_encode(array("message" => "Menu berhasil diupdate"));
    } else {
        echo json_encode(array("error" => "Data tidak lengkap atau id_menu tidak ditemukan"));
    }
}

	

        elseif ($method === 'DELETE') {
            $id = isset($_GET['id_menu']) ? $_GET['id_menu'] : null;
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM menu WHERE id_menu = ?");
                $stmt->execute(array($id));
                echo json_encode(array("message" => "Menu berhasil dihapus"));
            } else {
                echo json_encode(array("error" => "id_menu tidak ditemukan"));
            }
        }
        break;

    // ==================================================
    // =============== ENDPOINT PELANGGAN ===============
    // ==================================================
    case 'pelanggan':
        if ($method === 'GET') {
            $stmt = $pdo->query("SELECT * FROM pelanggan");
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($result);
        }

        elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data && isset($data['nama'], $data['email'], $data['telepon'], $data['alamat'], $data['password'])) {

        // Hash password pakai MD5
        $hashedPassword = md5($data['password']);

        $stmt = $pdo->prepare("INSERT INTO pelanggan (nama, email, telepon, alamat, password_hash) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute(array($data['nama'], $data['email'], $data['telepon'], $data['alamat'], $hashedPassword));

        echo json_encode(array("message" => "Pelanggan berhasil ditambahkan"));
    } else {
        echo json_encode(array("error" => "Data tidak lengkap"));
    }
}

        elseif ($method === 'DELETE') {
            $id = isset($_GET['id_pelanggan']) ? $_GET['id_pelanggan'] : null;
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM pelanggan WHERE id_pelanggan = ?");
                $stmt->execute(array($id));
                echo json_encode(array("message" => "Pelanggan berhasil dihapus"));
            } else {
                echo json_encode(array("error" => "id_pelanggan tidak ditemukan"));
            }
        }
        break;

// ==================================================
// =============== ENDPOINT LOGIN ===================
// ==================================================
case 'login':
    if ($method === 'POST') {
        $data = json_decode(file_get_contents("php://input"), true);

        if ($data && isset($data['email'], $data['password'])) {
            $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE email = ?");
            $stmt->execute(array($data['email']));
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $user['password_hash'] === md5($data['password'])) {
                echo json_encode(array("message" => "Login berhasil", "user" => $user));
            } else {
                echo json_encode(array("error" => "Email atau password salah"));
            }
        } else {
            echo json_encode(array("error" => "Data tidak lengkap"));
        }
    }
    break;


    // ==================================================
    // =============== ENDPOINT PESANAN =================
    // ==================================================
	    // ==================================================
    // =============== ENDPOINT PESANAN =================
    // ==================================================
    case 'pesanan':

        if ($method === 'GET') {
    $query = "SELECT 
                p.id_pesanan,
                p.id_pelanggan,
                COALESCE(pel.nama, 'Tidak Diketahui') AS nama_pelanggan,
                p.tanggal_pesan,
                COALESCE(p.total_harga, SUM(dp.subtotal)) AS total_harga,
                p.status,
                GROUP_CONCAT(m.nama_menu SEPARATOR ', ') AS nama_menu,
                SUM(dp.jumlah) AS jumlah_total
              FROM pesanan p
              JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
              JOIN menu m ON dp.id_menu = m.id_menu
              LEFT JOIN pelanggan pel ON p.id_pelanggan = pel.id_pelanggan";

    // Filter by pelanggan kalau dikirim dari user
    if (isset($_GET['id_pelanggan'])) {
        $query .= " WHERE p.id_pelanggan = " . intval($_GET['id_pelanggan']);
    }

    $query .= " GROUP BY p.id_pesanan ORDER BY p.id_pesanan DESC";

    $stmt = $pdo->query($query);
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($result);
}

        // POST pesanan (tambah baru)
	elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    if ($data && isset($data['id_pelanggan'], $data['total_harga'])) {

        // Simpan pesanan baru
        $stmt = $pdo->prepare("INSERT INTO pesanan (id_pelanggan, total_harga, status) VALUES (?, ?, 'Menunggu')");
        $stmt->execute(array($data['id_pelanggan'], $data['total_harga']));
        $id_pesanan = $pdo->lastInsertId();

        // Simpan setiap item detail pesanan
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $id_menu = intval($item['id_menu']);
                $jumlah = intval($item['jumlah']);
                $subtotal = floatval($item['subtotal']);

                // Cek stok saat ini
                $stmtCheck = $pdo->prepare("SELECT stok FROM menu WHERE id_menu = ?");
                $stmtCheck->execute(array($id_menu));
                $menu = $stmtCheck->fetch(PDO::FETCH_ASSOC);

                if ($menu && $menu['stok'] >= $jumlah) {
                    // Kurangi stok
                    $stmtUpdate = $pdo->prepare("UPDATE menu SET stok = stok - ? WHERE id_menu = ?");
                    $stmtUpdate->execute(array($jumlah, $id_menu));

                    // Simpan ke detail pesanan
                    $stmtDetail = $pdo->prepare("INSERT INTO detail_pesanan (id_pesanan, id_menu, jumlah, subtotal) VALUES (?, ?, ?, ?)");
                    $stmtDetail->execute(array($id_pesanan, $id_menu, $jumlah, $subtotal));
                } else {
                    // Jika stok tidak cukup, batalkan pesanan
                    echo json_encode(array("error" => "Stok tidak mencukupi untuk menu ID " . $id_menu));
                    // Hapus pesanan yang baru dibuat
                    $pdo->prepare("DELETE FROM pesanan WHERE id_pesanan = ?")->execute(array($id_pesanan));
                    exit;
                }
            }
        }

	echo json_encode(array("message" => "Pesanan berhasil dibuat", "id_pesanan" => $id_pesanan));


$cmd = "php -r 'sleep(2); 
    \$pdo = new PDO(\"mysql:host=localhost;dbname=db_restoran\", \"root\", \"\");
    \$stmt = \$pdo->prepare(\"UPDATE pesanan SET status = ? WHERE id_pesanan = ?\");
    \$stmt->execute(array(\"Diproses\", $id_pesanan));
    sleep(2);
    \$stmt = \$pdo->prepare(\"UPDATE pesanan SET status = ? WHERE id_pesanan = ?\");
    \$stmt->execute(array(\"Selesai\", $id_pesanan));
' > /dev/null 2>&1 &";

shell_exec($cmd);

    } else {
        echo json_encode(array("error" => "Data tidak lengkap"));
    }
}


        // PUT pesanan (ubah jumlah/subtotal)
        elseif ($method === 'PUT') {
            $data = json_decode(file_get_contents("php://input"), true);
            $id_pesanan = isset($_GET['id_pesanan']) ? intval($_GET['id_pesanan']) : null;

            if ($id_pesanan && isset($data['jumlah']) && isset($data['subtotal'])) {
                // Update total harga di pesanan
                $stmt = $pdo->prepare("UPDATE pesanan SET total_harga = ? WHERE id_pesanan = ?");
                $stmt->execute(array($data['subtotal'], $id_pesanan));

                // Update jumlah dan subtotal di detail_pesanan
                $stmt2 = $pdo->prepare("UPDATE detail_pesanan SET jumlah = ?, subtotal = ? WHERE id_pesanan = ?");
                $stmt2->execute(array($data['jumlah'], $data['subtotal'], $id_pesanan));

                echo json_encode(array("message" => "Pesanan berhasil diupdate"));
            } else {
                echo json_encode(array("error" => "Data tidak lengkap atau id_pesanan tidak ditemukan"));
            }
        }

        // DELETE pesanan
        elseif ($method === 'DELETE') {
            $id = isset($_GET['id_pesanan']) ? $_GET['id_pesanan'] : null;
            if ($id) {
                $stmt = $pdo->prepare("DELETE FROM pesanan WHERE id_pesanan = ?");
                $stmt->execute(array($id));
                echo json_encode(array("message" => "Pesanan berhasil dihapus"));
            } else {
                echo json_encode(array("error" => "id_pesanan tidak ditemukan"));
            }
        }

        break;

    // ==================================================
    // =============== DEFAULT RESPONSE =================
    // ==================================================
    default:
        echo json_encode(array("error" => "Endpoint tidak ditemukan"));
        break;
}
?>

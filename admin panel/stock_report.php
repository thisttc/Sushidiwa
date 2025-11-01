<?php
// เริ่มต้น session และเชื่อมต่อ database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../components/connect.php';

// รายงานสต็อก
$stock_report = $conn->prepare("
    SELECT 
        name,
        price,
        stock,
        sales,
        status,
        CASE 
            WHEN stock = 0 THEN 'Out of Stock'
            WHEN stock < 5 THEN 'Very Low'
            WHEN stock < 10 THEN 'Low'
            ELSE 'Adequate'
        END as stock_status,
        (sales / (sales + stock)) * 100 as sales_rate
    FROM products 
    WHERE seller_id = ?
    ORDER BY stock ASC, sales DESC
");
$stock_report->execute([$seller_id]);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kab Shop - Stock Report</title>
    <link rel="stylesheet" type="text/css" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        .report-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .stat-table th, .stat-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .stat-table th {
            background-color: #f5576c;
            color: white;
        }
        .stock-out { background-color: #ffebee; color: #d32f2f; font-weight: bold; }
        .stock-low { background-color: #fff3e0; color: #f57c00; }
        .stock-adequate { background-color: #e8f5e8; color: #388e3c; }
    </style>
</head>
<body>
    <div class="main-container">
        <?php include '../components/admin_header.php'; ?>

        <section class="dashboard">
            <div class="heading">
                <h1><i class="fas fa-boxes"></i> Stock Report</h1>
                <a href="dashboard.php" class="btn">Back to Dashboard</a>
            </div>

            <div class="report-container">
                <h2><i class="fas fa-chart-pie"></i> Inventory Status</h2>
                <table class="stat-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Sales</th>
                            <th>Sales Rate</th>
                            <th>Status</th>
                            <th>Stock Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $stock_report->fetch(PDO::FETCH_ASSOC)): 
                            $stock_class = '';
                            if ($row['stock'] == 0) $stock_class = 'stock-out';
                            elseif ($row['stock'] < 10) $stock_class = 'stock-low';
                            else $stock_class = 'stock-adequate';
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']); ?></td>
                            <td>$<?= number_format($row['price']); ?></td>
                            <td class="<?= $stock_class; ?>"><?= number_format($row['stock']); ?></td>
                            <td><?= number_format($row['sales']); ?></td>
                            <td><?= number_format($row['sales_rate'], 1); ?>%</td>
                            <td><?= ucfirst($row['status']); ?></td>
                            <td class="<?= $stock_class; ?>"><?= $row['stock_status']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="../js/admin_script.js"></script>
</body>
</html>
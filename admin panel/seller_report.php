<?php
// เริ่มต้น session และเชื่อมต่อ database
if(isset($_COOKIE['seller_id'])) {
		$seller_id = $_COOKIE['seller_id'];
	}
	else {
		$seller_id = '';
		header('location:login.php');
	}

// ดึงข้อมูลโปรไฟล์
$select_profile = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
$select_profile->execute([$seller_id]);
$fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);

// ดึงข้อมูลสถิติยอดขาย
$sales_report = $conn->prepare("
    SELECT 
        p.name as product_name,
        COUNT(o.id) as order_count,
        SUM(o.qty) as total_qty,
        SUM(o.price * o.qty) as total_revenue,
        AVG(o.price) as avg_price,
        MIN(o.date) as first_sale,
        MAX(o.date) as last_sale
    FROM orders o 
    JOIN products p ON o.product_id = p.id 
    WHERE o.seller_id = ? AND o.status = 'delivered'
    GROUP BY p.id 
    ORDER BY total_revenue DESC
");
$sales_report->execute([$seller_id]);

// สถิติรวม
$total_stats = $conn->prepare("
    SELECT 
        COUNT(DISTINCT o.id) as total_orders,
        SUM(o.qty) as total_items_sold,
        SUM(o.price * o.qty) as total_revenue,
        AVG(o.price * o.qty) as avg_order_value
    FROM orders o 
    WHERE o.seller_id = ? AND o.status = 'delivered'
");
$total_stats->execute([$seller_id]);
$stats = $total_stats->fetch(PDO::FETCH_ASSOC);

// ถ้าไม่มีข้อมูล ให้ตั้งค่า default
if (!$stats) {
    $stats = [
        'total_orders' => 0,
        'total_items_sold' => 0,
        'total_revenue' => 0,
        'avg_order_value' => 0
    ];
}

// ยอดขายรายเดือน
$monthly_sales = $conn->prepare("
    SELECT 
        DATE_FORMAT(date, '%Y-%m') as month,
        COUNT(*) as order_count,
        SUM(price * qty) as monthly_revenue
    FROM orders 
    WHERE seller_id = ? AND status = 'delivered'
    GROUP BY DATE_FORMAT(date, '%Y-%m')
    ORDER BY month DESC
    LIMIT 6
");
$monthly_sales->execute([$seller_id]);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kab Shop - Sales Report</title>
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
            background-color: #667eea;
            color: white;
        }
        .stat-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .stat-table tr:hover {
            background-color: #f5f5f5;
        }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .summary-card .number {
            font-size: 24px;
            font-weight: bold;
            margin: 0;
        }
        .empty-message {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        .guest-notice {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #2196f3;
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- ✅ เปลี่ยน header ให้แสดงเฉพาะเมื่อล็อกอิน -->
        <?php if(isset($_SESSION['seller_id'])): ?>
            <?php include '../components/admin_header.php'; ?>
        <?php else: ?>
            <div class="guest-notice">
                <p><i class="fas fa-info-circle"></i> คุณกำลังดูรายงานในโหมดผู้เยี่ยมชม</p>
            </div>
        <?php endif; ?>

        <section class="dashboard">
            <div class="heading">
                <h1><i class="fas fa-chart-bar"></i> Sales Report</h1>
                <?php if(isset($_SESSION['seller_id'])): ?>
                    <a href="dashboard.php" class="btn">Back to Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn">Login</a>
                <?php endif; ?>
            </div>

            <!-- ✅ แสดงข้อมูล seller ที่กำลังดู -->
            <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                <p><strong>Seller:</strong> <?= $fetch_profile['name'] ?? 'Guest User' ?></p>
                <p><strong>Seller ID:</strong> <?= $seller_id ?></p>
            </div>

            <!-- สรุปสถิติ -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Orders</h3>
                    <p class="number"><?= number_format($stats['total_orders']); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Items Sold</h3>
                    <p class="number"><?= number_format($stats['total_items_sold']); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Total Revenue</h3>
                    <p class="number">$<?= number_format($stats['total_revenue']); ?></p>
                </div>
                <div class="summary-card">
                    <h3>Avg Order Value</h3>
                    <p class="number">$<?= number_format($stats['avg_order_value'], 2); ?></p>
                </div>
            </div>

            <!-- รายงานสินค้า -->
            <div class="report-container">
                <h2><i class="fas fa-box"></i> Product Sales Performance</h2>
                <?php if($sales_report->rowCount() > 0): ?>
                <table class="stat-table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Orders</th>
                            <th>Quantity Sold</th>
                            <th>Total Revenue</th>
                            <th>Average Price</th>
                            <th>First Sale</th>
                            <th>Last Sale</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $sales_report->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['product_name']); ?></td>
                            <td><?= number_format($row['order_count']); ?></td>
                            <td><?= number_format($row['total_qty']); ?></td>
                            <td>$<?= number_format($row['total_revenue']); ?></td>
                            <td>$<?= number_format($row['avg_price'], 2); ?></td>
                            <td><?= $row['first_sale']; ?></td>
                            <td><?= $row['last_sale']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-message">
                    <i class="fas fa-chart-line fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                    <p>No sales data available yet.</p>
                    <p>Start selling to see your sales report here!</p>
                </div>
                <?php endif; ?>
            </div>

            <!-- ยอดขายรายเดือน -->
            <div class="report-container">
                <h2><i class="fas fa-calendar-alt"></i> Monthly Sales (Last 6 Months)</h2>
                <?php if($monthly_sales->rowCount() > 0): ?>
                <table class="stat-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Orders</th>
                            <th>Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($month = $monthly_sales->fetch(PDO::FETCH_ASSOC)): ?>
                        <tr>
                            <td><?= $month['month']; ?></td>
                            <td><?= number_format($month['order_count']); ?></td>
                            <td>$<?= number_format($month['monthly_revenue']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div class="empty-message">
                    <i class="fas fa-calendar fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                    <p>No monthly sales data available.</p>
                </div>
                <?php endif; ?>
            </div>

        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src="../js/admin_script.js"></script>
</body>
</html>
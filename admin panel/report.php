<?php
    include '../components/connect.php';

    if(isset($_COOKIE['seller_id'])) {
        $seller_id = $_COOKIE['seller_id'];
    }
    else {
        $seller_id = '';
        header('location:login.php');
    }

    // Get seller info
    $select_seller = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
    $select_seller->execute([$seller_id]);
    $fetch_seller = $select_seller->fetch(PDO::FETCH_ASSOC);

    // Get current month and year
    $current_month = date('m');
    $current_year = date('Y');
    $current_month_name = date('F');

    // Get monthly sales summary
    $select_monthly_summary = $conn->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as completed_orders,
            SUM(CASE WHEN status = 'in progress' THEN 1 ELSE 0 END) as pending_orders,
            SUM(price * qty) as total_sales,
            SUM(CASE WHEN status = 'delivered' THEN price * qty ELSE 0 END) as completed_sales
        FROM `orderss` 
        WHERE seller_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
    ");
    $select_monthly_summary->execute([$seller_id, $current_month, $current_year]);
    $monthly_summary = $select_monthly_summary->fetch(PDO::FETCH_ASSOC);

    // Get daily sales breakdown
    $select_daily_sales = $conn->prepare("
        SELECT 
            DATE(date) as sale_date,
            COUNT(*) as orders_count,
            SUM(price * qty) as daily_sales,
            SUM(CASE WHEN status = 'delivered' THEN price * qty ELSE 0 END) as completed_sales,
            SUM(CASE WHEN status = 'in progress' THEN price * qty ELSE 0 END) as pending_sales
        FROM `orderss` 
        WHERE seller_id = ? AND MONTH(date) = ? AND YEAR(date) = ?
        GROUP BY DATE(date)
        ORDER BY sale_date DESC
    ");
    $select_daily_sales->execute([$seller_id, $current_month, $current_year]);
    $daily_sales = $select_daily_sales->fetchAll(PDO::FETCH_ASSOC);

    // Get product-wise sales
    $select_product_sales = $conn->prepare("
        SELECT 
            p.name as product_name,
            COUNT(o.id) as orders_count,
            SUM(o.qty) as total_quantity,
            SUM(o.price * o.qty) as total_sales,
            AVG(o.price) as avg_price
        FROM `orderss` o
        JOIN `products` p ON o.product_id = p.id
        WHERE o.seller_id = ? AND MONTH(o.date) = ? AND YEAR(o.date) = ?
        GROUP BY o.product_id
        ORDER BY total_sales DESC
    ");
    $select_product_sales->execute([$seller_id, $current_month, $current_year]);
    $product_sales = $select_product_sales->fetchAll(PDO::FETCH_ASSOC);

    // Get monthly comparison (current vs previous month)
    $previous_month = $current_month == 1 ? 12 : $current_month - 1;
    $previous_year = $current_month == 1 ? $current_year - 1 : $current_year;
    
    $select_previous_month_sales = $conn->prepare("
        SELECT SUM(price * qty) as previous_sales
        FROM `orderss` 
        WHERE seller_id = ? AND MONTH(date) = ? AND YEAR(date) = ? AND status = 'delivered'
    ");
    $select_previous_month_sales->execute([$seller_id, $previous_month, $previous_year]);
    $previous_month_data = $select_previous_month_sales->fetch(PDO::FETCH_ASSOC);
    $previous_month_sales = $previous_month_data['previous_sales'] ?: 0;

    // Calculate growth percentage
    $current_sales = $monthly_summary['completed_sales'] ?: 0;
    $growth_percentage = $previous_month_sales > 0 ? 
        (($current_sales - $previous_month_sales) / $previous_month_sales) * 100 : ($current_sales > 0 ? 100 : 0);

    // --- NEW QUERIES ADDED HERE ---

    // 1. Get detailed 'in progress' orders
    $select_in_progress_details = $conn->prepare("
        SELECT 
            u.name AS uname, 
            p.name AS pname, 
            o.qty,
            o.price AS pprice, 
            (o.qty * o.price) AS total_price, 
            o.address AS oad, 
            o.date AS odate, 
            o.payment_status AS opay 
        FROM `orderss` o
        JOIN `products` p ON p.id = o.product_id
        JOIN `users` u ON u.id = o.user_id
        WHERE o.status = 'in progress' AND o.seller_id = ?
        ORDER BY o.date DESC
    ");
    $select_in_progress_details->execute([$seller_id]);
    $in_progress_details = $select_in_progress_details->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get summary of 'in progress' orders by date and product
    $select_in_progress_summary = $conn->prepare("
        SELECT 
            DATE(o.date) AS odate,
            p.name AS pname,
            SUM(o.qty) AS total_qty,
            o.price AS pprice,
            SUM(o.qty * o.price) AS total_price
        FROM `orderss` o
        JOIN `products` p ON o.product_id = p.id
        WHERE o.status = 'in progress' AND o.seller_id = ?
        GROUP BY DATE(o.date), p.name, o.price
        ORDER BY odate ASC
    ");
    $select_in_progress_summary->execute([$seller_id]);
    $in_progress_summary = $select_in_progress_summary->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset = 'utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kab Shop - Monthly Sales Report</title>
    <link rel = "stylesheet" type = "text/css" href = "../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
</head>
<body>
    <div class ="main-container">
        
        <?php include '../components/admin_header.php'; ?>

        <section class="dashboard">
            <div class="heading">
                <h1>Monthly Sales Report - <?= $current_month_name . ' ' . $current_year ?></h1>
                <p>Seller: <?= htmlspecialchars($fetch_seller['name']) ?></p>
            </div>

            <div class="table-container">
                <h3>Monthly Sales Summary</h3>
                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Total Orders</th>
                            <th>Completed Orders</th>
                            <th>Pending Orders</th>
                            <th>Total Sales Value</th>
                            <th>Completed Sales</th>
                            <th>Monthly Growth</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?= $monthly_summary['total_orders'] ?: 0 ?></td>
                            <td><?= $monthly_summary['completed_orders'] ?: 0 ?></td>
                            <td><?= $monthly_summary['pending_orders'] ?: 0 ?></td>
                            <td><?= number_format($monthly_summary['total_sales'] ?: 0, 2) ?>฿</td>
                            <td><?= number_format($monthly_summary['completed_sales'] ?: 0, 2) ?>฿</td>
                            <td class="<?= $growth_percentage >= 0 ? 'positive' : 'negative' ?>">
                                <?= number_format($growth_percentage, 1) ?>%
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-container">
                <h3>Daily Sales Breakdown</h3>
                <?php if(count($daily_sales) > 0): ?>
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Orders</th>
                                <th>Total Sales</th>
                                <th>Completed Sales</th>
                                <th>Pending Sales</th>
                                <th>Completion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($daily_sales as $daily): ?>
                                <tr>
                                    <td><?= date('M j, Y', strtotime($daily['sale_date'])) ?></td>
                                    <td><?= $daily['orders_count'] ?></td>
                                    <td><?= number_format($daily['daily_sales'] ?: 0, 2) ?>฿</td>
                                    <td><?= number_format($daily['completed_sales'] ?: 0, 2) ?>฿</td>
                                    <td><?= number_format($daily['pending_sales'] ?: 0, 2) ?>฿</td>
                                    <td>
                                        <?= $daily['daily_sales'] > 0 ? 
                                            number_format(($daily['completed_sales'] / $daily['daily_sales']) * 100, 1) : 0 ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?= $monthly_summary['total_orders'] ?: 0 ?></strong></td>
                                <td><strong><?= number_format($monthly_summary['total_sales'] ?: 0, 2) ?>฿</strong></td>
                                <td><strong><?= number_format($monthly_summary['completed_sales'] ?: 0, 2) ?>฿</strong></td>
                                <td><strong><?= number_format(($monthly_summary['total_sales'] - $monthly_summary['completed_sales']) ?: 0, 2) ?>฿</strong></td>
                                <td><strong>
                                    <?= $monthly_summary['total_sales'] > 0 ? 
                                        number_format(($monthly_summary['completed_sales'] / $monthly_summary['total_sales']) * 100, 1) : 0 ?>%
                                </strong></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <p class="no-data">No sales data available for this month.</p>
                <?php endif; ?>
            </div>

            <div class="table-container">
                <h3>Product-wise Sales Performance</h3>
                <?php if(count($product_sales) > 0): ?>
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Orders</th>
                                <th>Quantity Sold</th>
                                <th>Total Sales</th>
                                <th>Average Price</th>
                                <th>Sales Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $total_product_sales = array_sum(array_column($product_sales, 'total_sales'));
                            ?>
                            <?php foreach($product_sales as $product): ?>
                                <tr>
                                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td><?= $product['orders_count'] ?></td>
                                    <td><?= $product['total_quantity'] ?></td>
                                    <td><?= number_format($product['total_sales'], 2) ?>฿</td>
                                    <td><?= number_format($product['avg_price'], 2) ?>฿</td>
                                    <td>
                                        <?= $total_product_sales > 0 ? 
                                            number_format(($product['total_sales'] / $total_product_sales) * 100, 1) : 0 ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?= array_sum(array_column($product_sales, 'orders_count')) ?></strong></td>
                                <td><strong><?= array_sum(array_column($product_sales, 'total_quantity')) ?></strong></td>
                                <td><strong><?= number_format($total_product_sales, 2) ?>฿</strong></td>
                                <td><strong>-</strong></td>
                                <td><strong>100.0%</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <p class="no-data">No product sales data available for this month.</p>
                <?php endif; ?>
            </div>

            <div class="table-container">
                <h3>Each sushi for each customer orders </h3>
                <?php if(count($in_progress_details) > 0): ?>
                    <table class="sales-table">
                        <thead>
                            <tr>
                                <th>customer name</th>
                                <th>Menu</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th>Address</th>
                                <th>Date</th>
                                <th>Payment status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($in_progress_details as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row["uname"]) ?></td>
                                <td><?= htmlspecialchars($row["pname"]) ?></td>
                                <td><?= $row["qty"] ?></td>
                                <td><?= number_format($row["pprice"], 2) ?> บาท</td>
                                <td><?= number_format($row["total_price"], 2) ?> บาท</td>
                                <td><?= htmlspecialchars($row["oad"]) ?></td>
                                <td><?= date('M j, Y H:i', strtotime($row["odate"])) ?></td>
                                <td><?= htmlspecialchars($row["opay"]) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="no-data">Order is empty right now</p>
                <?php endif; ?>
            </div>

            <div class="table-container">
                <h3>Total orders of each menu per day</h3>
                <?php if(count($in_progress_summary) > 0): ?>
                    <table class="sales-table">
                        <thead>
                             <tr>
                                <th>Date</th>
                                <th>Menu</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($in_progress_summary as $row): ?>
                            <tr>
                                <td><?= date('M j, Y', strtotime($row["odate"])) ?></td>
                                <td><?= htmlspecialchars($row["pname"]) ?></td>
                                <td><?= $row["total_qty"] ?></td>
                                <td><?= number_format($row["pprice"], 2) ?> บาท</td>
                                <td><?= number_format($row["total_price"], 2) ?> บาท</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                     <p class="no-data">order is empty right now</p>
                <?php endif; ?>
            </div>


            <div class="action-buttons">
                <button onclick="window.print()" class="btn">Print Report</button>
            </div>
        </section>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
    <script src = "../js/admin_script.js"></script>

    <?php include '../components/alert.php'; ?>

</body>
</html>
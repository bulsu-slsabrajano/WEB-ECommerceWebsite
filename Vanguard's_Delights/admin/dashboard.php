<?php
require_once '../db/action/config.php'; 
require_once '../db/connection.php'; 
require_once '../db/action/fetch_dashboard.php'; 

session_start();

// Data Fetching
$summary        = getDashboardSummary($conn);
$topProducts    = getTopSellingProducts($conn);
$topCategories  = getTopSellingCategories($conn); // New function

// Chart Data for JS
$dailyData   = getDailySales($conn);
$weeklyData  = getWeeklySales($conn);
$monthlyData = getMonthlySales($conn);

$admin_name = $_SESSION["login_data"]["first_name"] ?? "Admin";

// Low stock products
$lowStock = $conn->query("
    SELECT name, stock_quantity FROM products 
    WHERE stock_quantity <= 5 AND is_active = 1 
    ORDER BY stock_quantity ASC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <style>
        /* Maintain all your original CSS here */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat-card { background: white; border-radius: 14px; box-shadow: 0 2px 16px rgba(0,0,0,0.05); padding: 20px 22px; display: flex; flex-direction: column; gap: 12px; }
        .stat-card-top { display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .stat-icon.maroon { background: var(--maroon-xlight); color: var(--maroon); }
        .stat-icon.green { background: #EDFAF3; color: #1E7D4A; }
        .stat-icon.blue { background: #EEF2FF; color: #3730A3; }
        .stat-icon.orange { background: #FFF3E0; color: #E65100; }
        .stat-label { font-size: 11.5px; font-weight: 600; color: var(--text-gray); text-transform: uppercase; letter-spacing: 0.7px; margin: 0; }
        .stat-value { font-size: 26px; font-weight: 700; color: #1A1A1A; line-height: 1; margin: 0; }
        .stat-sub { font-size: 12px; color: var(--text-gray); margin: 0; }
        .dash-grid { display: grid; grid-template-columns: 1fr 340px; gap: 16px; margin-bottom: 16px; }
        .dash-card { background: white; border-radius: 14px; box-shadow: 0 2px 16px rgba(0,0,0,0.05); padding: 22px 24px; }
        .dash-card-title { font-size: 14px; font-weight: 600; color: #1A1A1A; margin: 0 0 4px; }
        .dash-card-sub { font-size: 12px; color: var(--text-gray); margin: 0 0 18px; }
        .chart-toggle { display: flex; gap: 6px; }
        .chart-toggle-btn { padding: 5px 14px; border: 1.5px solid var(--cream-dark); border-radius: 20px; background: white; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 500; color: var(--text-gray); cursor: pointer; transition: 0.2s; }
        .chart-toggle-btn.active, .chart-toggle-btn:hover { background: var(--maroon); color: white; border-color: var(--maroon); }
        .rank-item { display: flex; align-items: center; gap: 12px; padding: 10px 0; border-bottom: 1px solid #F8F4F0; }
        .rank-item:last-child { border-bottom: none; }
        .rank-num { width: 26px; height: 26px; border-radius: 8px; background: var(--maroon-xlight); color: var(--maroon); font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .rank-name { flex: 1; font-size: 13px; font-weight: 500; color: #1A1A1A; }
        .rank-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; background: var(--maroon-xlight); color: var(--maroon); font-size: 11.5px; font-weight: 600; }
        .low-item { display: flex; align-items: center; gap: 12px; padding: 9px 0; border-bottom: 1px solid #F8F4F0; }
        .low-item:last-child { border-bottom: none; }
        .low-icon { width: 32px; height: 32px; border-radius: 8px; background: #FFF3E0; color: #E65100; font-size: 13px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .low-name { flex: 1; font-size: 13px; font-weight: 500; color: #1A1A1A; }
        .low-qty { font-size: 12px; font-weight: 700; color: #E65100; }
        .section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 0; padding: 20px 24px 16px; border-bottom: 1px solid var(--cream-dark); }
        .section-header h5 { font-size: 14px; font-weight: 600; color: #1A1A1A; margin: 0; }
        .section-link { font-size: 12.5px; font-weight: 500; color: var(--maroon); text-decoration: none; transition: 0.2s; }
        .section-link:hover { text-decoration: underline; color: var(--maroon); }
        .welcome-bar { margin-bottom: 20px; }
        .welcome-bar h4 { font-size: 18px; font-weight: 600; color: #1A1A1A; margin: 0 0 2px; }
        .welcome-bar p { font-size: 13px; color: var(--text-gray); margin: 0; }
    </style>
</head>
<body>
<div class="d-flex" id="wrapper">
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <img src="../images/logo.png" alt="Logo">
            <h6>Vanguard's<br>Delights</h6>
        </div>
        <div class="nav-section">
            <a href="dashboard.php" class="list-group-item active"><i class="fa-solid fa-gauge-high"></i>Dashboard</a>
            <a href="products.php" class="list-group-item"><i class="fa-solid fa-box"></i>Products</a>
            <a href="categories.php" class="list-group-item"><i class="fa-solid fa-layer-group"></i>Categories</a>
            <a href="orders.php" class="list-group-item"><i class="fa-solid fa-receipt"></i>Orders</a>
            <a href="admin_ui.php" class="list-group-item"><i class="fa-solid fa-users"></i>Customers</a>
            <a href="reports.php" class="list-group-item"><i class="fa-solid fa-chart-line"></i>Reports</a>
            <a href="admin_site.php" class="list-group-item"><i class="fa-solid fa-shield-halved"></i>Admin Site Settings</a>
        </div>
        <div class="sidebar-footer">
            <hr>
            <a href="../db/action/logout.php" class="list-group-item logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>Log Out
            </a>
        </div>
    </div>

    <div id="page-content-wrapper">
        <nav class="top-navbar">
            <h3 class="page-title">Dashboard</h3>
            <div class="admin-area">
                <div class="admin-info">
                    <p class="name"><?= htmlspecialchars($admin_name) ?></p>
                    <span class="role">Admin</span>
                </div>
                <div class="admin-profile-icon"><i class="fa-solid fa-circle-user"></i></div>
            </div>
        </nav>

        <div class="content-area">
            <div class="welcome-bar">
                <h4>Welcome back, <?= htmlspecialchars($admin_name) ?>!</h4>
                <p>Here's what's happening in your store today.</p>
            </div>

            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon maroon"><i class="fa-solid fa-peso-sign"></i></div>
                        <span style="font-size:11px;font-weight:600;color:#1E7D4A;background:#EDFAF3;padding:3px 9px;border-radius:20px;">Revenue</span>
                    </div>
                    <div>
                        <p class="stat-label">Total Sales</p>
                        <p class="stat-value">₱<?= number_format($summary['total_sales'], 2) ?></p>
                        <p class="stat-sub">From completed orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
                        <span style="font-size:11px;font-weight:600;color:#3730A3;background:#EEF2FF;padding:3px 9px;border-radius:20px;">Orders</span>
                    </div>
                    <div>
                        <p class="stat-label">Total Orders</p>
                        <p class="stat-value"><?= number_format($summary['total_orders']) ?></p>
                        <p class="stat-sub">All time</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon green"><i class="fa-solid fa-users"></i></div>
                        <span style="font-size:11px;font-weight:600;color:#1E7D4A;background:#EDFAF3;padding:3px 9px;border-radius:20px;">Customers</span>
                    </div>
                    <div>
                        <p class="stat-label">Active Customers</p>
                        <p class="stat-value"><?= number_format($summary['total_customers']) ?></p>
                        <p class="stat-sub">Registered accounts</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon orange"><i class="fa-solid fa-triangle-exclamation"></i></div>
                        <span style="font-size:11px;font-weight:600;color:#E65100;background:#FFF3E0;padding:3px 9px;border-radius:20px;">Alert</span>
                    </div>
                    <div>
                        <p class="stat-label">Low Stock Items</p>
                        <p class="stat-value"><?= count($lowStock) ?></p>
                        <p class="stat-sub">Products needing restock</p>
                    </div>
                </div>
            </div>

            <div class="dash-grid">
                <div class="dash-card">
                    <div class="d-flex align-items-start justify-content-between mb-1">
                        <div>
                            <p class="dash-card-title">Sales Analytics</p>
                            <p class="dash-card-sub">Revenue overview by period</p>
                        </div>
                        <div class="chart-toggle">
                            <button class="chart-toggle-btn active" onclick="switchChart(this,'daily')">Daily</button>
                            <button class="chart-toggle-btn" onclick="switchChart(this,'weekly')">Weekly</button>
                            <button class="chart-toggle-btn" onclick="switchChart(this,'monthly')">Monthly</button>
                        </div>
                    </div>
                    <div id="salesChart" style="width:100%;height:300px;"></div>
                </div>

                <div class="dash-card">
                    <p class="dash-card-title">Best-Selling Products</p>
                    <p class="dash-card-sub">Top products by units sold</p>
                    <?php if (!empty($topProducts)): ?>
                        <?php foreach ($topProducts as $i => $prod): ?>
                        <div class="rank-item">
                            <div class="rank-num"><?= $i + 1 ?></div>
                            <span class="rank-name"><?= htmlspecialchars($prod['name']) ?></span>
                            <span class="rank-badge"><?= $prod['sold_count'] ?> sold</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">No sales data yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="dash-grid">
                <div class="dash-card">
                    <div class="section-header" style="padding:0 0 16px; border:none;">
                        <h5 class="dash-card-title"><i class="fa-solid fa-triangle-exclamation me-2" style="color:#E65100;"></i>Low Stock Alerts</h5>
                        <a href="products.php?filter=low" class="section-link">View All</a>
                    </div>
                    <?php if (!empty($lowStock)): ?>
                        <?php foreach ($lowStock as $item): ?>
                        <div class="low-item">
                            <div class="low-icon"><i class="fa-solid fa-box"></i></div>
                            <span class="low-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="low-qty"><?= $item['stock_quantity'] ?> left</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state" style="padding:20px 0;">
                            <i class="fa-solid fa-circle-check" style="color:#2ECC71;"></i>
                            All products are well stocked.
                        </div>
                    <?php endif; ?>
                </div>

                <div class="dash-card">
                    <p class="dash-card-title">Top Categories</p>
                    <p class="dash-card-sub">Highest selling product types</p>
                    <?php if (!empty($topCategories)): ?>
                        <?php foreach ($topCategories as $i => $cat): ?>
                        <div class="rank-item">
                            <div class="rank-num"><?= $i + 1 ?></div>
                            <span class="rank-name"><?= htmlspecialchars($cat['category_name']) ?></span>
                            <span class="rank-badge"><?= $cat['sold_count'] ?> sold</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">No category data.</div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Data Sets from PHP
const dataSets = {
    daily: <?= json_encode($dailyData) ?>,
    weekly: <?= json_encode($weeklyData) ?>,
    monthly: <?= json_encode($monthlyData) ?>
};

const formatData = (arr) => arr.map(item => ({ date: item.sales_date, value: parseFloat(item.revenue) }));

let chartRoot, xAxis, chartSeries;

am5.ready(function () {
    chartRoot = am5.Root.new("salesChart");
    chartRoot.setThemes([am5themes_Animated.new(chartRoot)]);
    chartRoot._logo.dispose();

    var chart = chartRoot.container.children.push(
        am5xy.XYChart.new(chartRoot, { panX: false, panY: false, wheelX: "none", wheelY: "none" })
    );

    var xRenderer = am5xy.AxisRendererX.new(chartRoot, { minGridDistance: 30 });
    xRenderer.labels.template.setAll({ fontSize: 11, fill: am5.color(0x7E7E7E), fontFamily: "Poppins" });
    xRenderer.grid.template.setAll({ strokeOpacity: 0 });

    xAxis = chart.xAxes.push(am5xy.CategoryAxis.new(chartRoot, {
        categoryField: "date",
        renderer: xRenderer
    }));

    var yAxis = chart.yAxes.push(am5xy.ValueAxis.new(chartRoot, {
        renderer: am5xy.AxisRendererY.new(chartRoot, {})
    }));
    yAxis.get("renderer").labels.template.setAll({ fontSize: 11, fill: am5.color(0x7E7E7E) });

    chartSeries = chart.series.push(am5xy.ColumnSeries.new(chartRoot, {
        name: "Revenue",
        xAxis: xAxis,
        yAxis: yAxis,
        valueYField: "value",
        categoryXField: "date",
        tooltip: am5.Tooltip.new(chartRoot, {
            labelText: "₱{valueY.formatNumber('#,###.00')}",
            getFillFromSprite: false,
            background: am5.Rectangle.new(chartRoot, {
                fill: am5.color(0x822F2F),
                cornerRadiusTL: 8, cornerRadiusTR: 8, cornerRadiusBL: 8, cornerRadiusBR: 8
            })
        })
    }));

    chartSeries.columns.template.setAll({
        cornerRadiusTL: 6, cornerRadiusTR: 6,
        fill: am5.color(0x822F2F),
        strokeOpacity: 0,
        width: am5.percent(60)
    });

    updateChartData('daily');
    chart.appear(1000, 100);
});

function switchChart(btn, period) {
    document.querySelectorAll('.chart-toggle-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    updateChartData(period);
}

function updateChartData(period) {
    const newData = formatData(dataSets[period]);
    xAxis.data.setAll(newData);
    chartSeries.data.setAll(newData);
    chartSeries.appear(500);
}
</script>
</body>
</html>
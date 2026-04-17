<!-- admin/reports.php -->
<?php
require_once '../db/action/config.php';
require_once '../db/connection.php';
require_once '../db/action/fetch_reports.php';

session_start();

$admin_name      = $_SESSION["login_data"]["first_name"] ?? "Admin";

$summary         = getRevenueSummary($conn);
$dailyRevenue    = getDailyRevenue($conn);
$weeklyRevenue   = getWeeklyRevenue($conn);
$monthlyRevenue  = getMonthlyRevenue($conn);
$statusBreakdown = getOrderStatusBreakdown($conn);
$topProducts     = getTopProductsByRevenue($conn);
$topCategories   = getTopCategoriesByRevenue($conn);
$customerGrowth  = getCustomerGrowth($conn);
$recentTx        = getRecentTransactions($conn);

// Completion rate
$total    = $summary['total_orders'] ?: 1;
$compRate = round(($summary['completed_orders'] / $total) * 100, 1);

$status_class = [
    'completed'  => 'os-completed',
    'pending'    => 'os-pending',
    'cancelled'  => 'os-cancelled',
    'processing' => 'os-processing',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Vanguard's Delights</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin_style.css">
    <script src="https://cdn.amcharts.com/lib/5/index.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/xy.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/percent.js"></script>
    <script src="https://cdn.amcharts.com/lib/5/themes/Animated.js"></script>
    <style>
        /* ── Stat grid ── */
        .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 20px; }
        .stat-card { background: white; border-radius: 14px; box-shadow: 0 2px 16px rgba(0,0,0,0.05); padding: 20px 22px; display: flex; flex-direction: column; gap: 10px; }
        .stat-card-top { display: flex; align-items: center; justify-content: space-between; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
        .stat-icon.maroon { background: var(--maroon-xlight); color: var(--maroon); }
        .stat-icon.green  { background: #EDFAF3; color: #1E7D4A; }
        .stat-icon.blue   { background: #EEF2FF; color: #3730A3; }
        .stat-icon.purple { background: #F3E8FF; color: #6D28D9; }
        .stat-label { font-size: 11px; font-weight: 600; color: var(--text-gray); text-transform: uppercase; letter-spacing: 0.7px; margin: 0; }
        .stat-value { font-size: 24px; font-weight: 700; color: #1A1A1A; line-height: 1; margin: 0; }
        .stat-sub   { font-size: 12px; color: var(--text-gray); margin: 0; }
        .stat-pill  { font-size: 11px; font-weight: 600; padding: 3px 9px; border-radius: 20px; }

        /* ── Layout grids ── */
        .row-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .row-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .row-grid-wide { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; margin-bottom: 16px; }

        /* ── Report card ── */
        .rep-card { background: white; border-radius: 14px; box-shadow: 0 2px 16px rgba(0,0,0,0.05); padding: 22px 24px; }
        .rep-card-title { font-size: 14px; font-weight: 600; color: #1A1A1A; margin: 0 0 2px; }
        .rep-card-sub   { font-size: 12px; color: var(--text-gray); margin: 0 0 16px; }

        /* ── Chart toggle ── */
        .chart-toggle { display: flex; gap: 6px; }
        .chart-toggle-btn { padding: 5px 14px; border: 1.5px solid var(--cream-dark); border-radius: 20px; background: white; font-family: 'Poppins', sans-serif; font-size: 12px; font-weight: 500; color: var(--text-gray); cursor: pointer; transition: 0.2s; }
        .chart-toggle-btn.active, .chart-toggle-btn:hover { background: var(--maroon); color: white; border-color: var(--maroon); }

        /* ── Rank list ── */
        .rank-item { display: flex; align-items: center; gap: 11px; padding: 9px 0; border-bottom: 1px solid #F8F4F0; }
        .rank-item:last-child { border-bottom: none; }
        .rank-num  { width: 26px; height: 26px; border-radius: 8px; background: var(--maroon-xlight); color: var(--maroon); font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .rank-name { flex: 1; font-size: 13px; font-weight: 500; color: #1A1A1A; }
        .rank-sub  { font-size: 11.5px; color: var(--text-gray); font-weight: 400; display: block; }
        .rank-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; background: var(--maroon-xlight); color: var(--maroon); font-size: 11px; font-weight: 600; white-space: nowrap; }

        /* ── Progress bar ── */
        .prog-row { margin-bottom: 12px; }
        .prog-label { display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 5px; }
        .prog-label span:first-child { font-weight: 500; color: #1A1A1A; }
        .prog-label span:last-child  { color: var(--text-gray); }
        .prog-bar-wrap { background: #F3EDE8; border-radius: 10px; height: 8px; overflow: hidden; }
        .prog-bar-fill { height: 8px; border-radius: 10px; background: var(--maroon); transition: width 0.8s ease; }

        /* ── Status breakdown ── */
        .status-row { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #F8F4F0; }
        .status-row:last-child { border-bottom: none; }
        .status-dot-lg { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .status-label { flex: 1; font-size: 13px; font-weight: 500; color: #1A1A1A; text-transform: capitalize; }
        .status-count { font-size: 13px; font-weight: 600; color: #1A1A1A; }
        .status-pct   { font-size: 11.5px; color: var(--text-gray); min-width: 38px; text-align: right; }

        /* ── Recent transactions table ── */
        .tx-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .tx-table thead th { font-size: 10.5px; font-weight: 600; color: var(--maroon-light); text-transform: uppercase; letter-spacing: 0.8px; padding: 0 0 10px; border-bottom: 1.5px solid var(--cream-dark); white-space: nowrap; }
        .tx-table td { padding: 11px 0; border-bottom: 1px solid #F8F4F0; vertical-align: middle; color: #2D2D2D; }
        .tx-table tbody tr:last-child td { border-bottom: none; }

        /* ── Completion ring ── */
        .kpi-ring-wrap { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; padding: 10px 0; }
        .kpi-ring-value { font-size: 28px; font-weight: 700; color: #1A1A1A; }
        .kpi-ring-label { font-size: 12px; color: var(--text-gray); }

        /* ── Welcome bar ── */
        .welcome-bar { margin-bottom: 20px; }
        .welcome-bar h4 { font-size: 18px; font-weight: 600; color: #1A1A1A; margin: 0 0 2px; }
        .welcome-bar p  { font-size: 13px; color: var(--text-gray); margin: 0; }

        /* ── Section header row ── */
        .card-header-row { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 0; }
    </style>
</head>
<body>
<div class="d-flex" id="wrapper">

    <!-- SIDEBAR -->
    <div id="sidebar-wrapper">
        <div class="sidebar-heading">
            <img src="../images/logo.png" alt="Logo">
            <h6>Vanguard's<br>Delights</h6>
        </div>
        <div class="nav-section">
            <a href="dashboard.php"  class="list-group-item"><i class="fa-solid fa-gauge-high"></i>Dashboard</a>
            <a href="products.php"   class="list-group-item"><i class="fa-solid fa-box"></i>Products</a>
            <a href="categories.php" class="list-group-item"><i class="fa-solid fa-layer-group"></i>Categories</a>
            <a href="orders.php"     class="list-group-item"><i class="fa-solid fa-receipt"></i>Orders</a>
            <a href="admin_ui.php"   class="list-group-item"><i class="fa-solid fa-users"></i>Customers</a>
            <a href="reports.php"    class="list-group-item active"><i class="fa-solid fa-chart-line"></i>Reports</a>
            <a href="admin_site.php" class="list-group-item"><i class="fa-solid fa-shield-halved"></i>Admin Site Settings</a>
        </div>
        <div class="sidebar-footer">
            <hr>
            <a href="../db/action/logout.php" class="list-group-item logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>Log Out
            </a>
        </div>
    </div>

    <!-- MAIN -->
    <div id="page-content-wrapper">
        <nav class="top-navbar">
            <h3 class="page-title">Reports</h3>
            <div class="admin-area">
                <div class="admin-info">
                    <p class="name"><?= htmlspecialchars($admin_name) ?></p>
                    <span class="role">Admin</span>
                </div>
                <div class="admin-profile-icon">
                    <i class="fa-solid fa-circle-user"></i>
                </div>
            </div>
        </nav>

        <div class="content-area">

            <div class="welcome-bar">
                <h4>Sales & Analytics Report</h4>
                <p>A full overview of your store's performance, revenue, and customer trends.</p>
            </div>

            <!-- ── Row 1: Summary Stat Cards ── -->
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon maroon"><i class="fa-solid fa-peso-sign"></i></div>
                        <span class="stat-pill" style="color:#1E7D4A;background:#EDFAF3;">Revenue</span>
                    </div>
                    <div>
                        <p class="stat-label">Total Revenue</p>
                        <p class="stat-value">₱<?= number_format($summary['total_revenue'], 2) ?></p>
                        <p class="stat-sub">All completed orders</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon blue"><i class="fa-solid fa-receipt"></i></div>
                        <span class="stat-pill" style="color:#3730A3;background:#EEF2FF;">Orders</span>
                    </div>
                    <div>
                        <p class="stat-label">Total Orders</p>
                        <p class="stat-value"><?= number_format($summary['total_orders']) ?></p>
                        <p class="stat-sub"><?= $summary['completed_orders'] ?> completed &bull; <?= $summary['pending_orders'] ?> pending</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon green"><i class="fa-solid fa-arrow-trend-up"></i></div>
                        <span class="stat-pill" style="color:#1E7D4A;background:#EDFAF3;">Avg</span>
                    </div>
                    <div>
                        <p class="stat-label">Avg Order Value</p>
                        <p class="stat-value">₱<?= number_format($summary['avg_order_value'], 2) ?></p>
                        <p class="stat-sub">Per completed order</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-top">
                        <div class="stat-icon purple"><i class="fa-solid fa-circle-check"></i></div>
                        <span class="stat-pill" style="color:#6D28D9;background:#F3E8FF;">Rate</span>
                    </div>
                    <div>
                        <p class="stat-label">Completion Rate</p>
                        <p class="stat-value"><?= $compRate ?>%</p>
                        <p class="stat-sub"><?= $summary['cancelled_orders'] ?> orders cancelled</p>
                    </div>
                </div>
            </div>

            <!-- ── Row 2: Revenue Chart (wide) + Order Status ── -->
            <div class="row-grid-wide">

                <!-- Revenue Over Time -->
                <div class="rep-card">
                    <div class="card-header-row" style="margin-bottom:16px;">
                        <div>
                            <p class="rep-card-title">Revenue Over Time</p>
                            <p class="rep-card-sub" style="margin-bottom:0;">Sales performance by period</p>
                        </div>
                        <div class="chart-toggle">
                            <button class="chart-toggle-btn active" onclick="switchRevChart(this,'daily')">Daily</button>
                            <button class="chart-toggle-btn" onclick="switchRevChart(this,'weekly')">Weekly</button>
                            <button class="chart-toggle-btn" onclick="switchRevChart(this,'monthly')">Monthly</button>
                        </div>
                    </div>
                    <div id="revenueChart" style="width:100%;height:280px;"></div>
                </div>

                <!-- Order Status Breakdown -->
                <div class="rep-card">
                    <p class="rep-card-title">Order Status Breakdown</p>
                    <p class="rep-card-sub">Distribution of all orders</p>
                    <div id="statusDonut" style="width:100%;height:180px;margin-bottom:12px;"></div>
                    <?php
                    $statusColors = [
                        'completed'  => '#2ECC71',
                        'pending'    => '#F59E0B',
                        'processing' => '#6366F1',
                        'cancelled'  => '#E74C3C',
                    ];
                    $totalOrders = $summary['total_orders'] ?: 1;
                    foreach ($statusBreakdown as $s):
                        $key   = strtolower($s['status']);
                        $color = $statusColors[$key] ?? '#888';
                        $pct   = round(($s['count'] / $totalOrders) * 100, 1);
                    ?>
                    <div class="status-row">
                        <span class="status-dot-lg" style="background:<?= $color ?>"></span>
                        <span class="status-label"><?= ucfirst($s['status']) ?></span>
                        <span class="status-count"><?= $s['count'] ?></span>
                        <span class="status-pct"><?= $pct ?>%</span>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- ── Row 3: Top Products + Top Categories + Customer Growth ── -->
            <div class="row-grid-3">

                <!-- Top Products by Revenue -->
                <div class="rep-card">
                    <p class="rep-card-title">Top Products</p>
                    <p class="rep-card-sub">By revenue generated</p>
                    <?php
                    $maxRev = !empty($topProducts) ? (float)$topProducts[0]['revenue'] : 1;
                    foreach (array_slice($topProducts, 0, 6) as $i => $prod):
                        $pct = $maxRev > 0 ? round(($prod['revenue'] / $maxRev) * 100) : 0;
                    ?>
                    <div class="prog-row">
                        <div class="prog-label">
                            <span><?= htmlspecialchars($prod['name']) ?></span>
                            <span>₱<?= number_format($prod['revenue'], 2) ?></span>
                        </div>
                        <div class="prog-bar-wrap">
                            <div class="prog-bar-fill" style="width:<?= $pct ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($topProducts)): ?>
                        <div class="empty-state">No product sales yet.</div>
                    <?php endif; ?>
                </div>

                <!-- Top Categories -->
                <div class="rep-card">
                    <p class="rep-card-title">Top Categories</p>
                    <p class="rep-card-sub">By units sold</p>
                    <?php if (!empty($topCategories)): ?>
                        <?php foreach ($topCategories as $i => $cat): ?>
                        <div class="rank-item">
                            <div class="rank-num"><?= $i + 1 ?></div>
                            <div style="flex:1;min-width:0;">
                                <span class="rank-name"><?= htmlspecialchars($cat['category_name']) ?></span>
                                <span class="rank-sub">₱<?= number_format($cat['revenue'], 2) ?> revenue</span>
                            </div>
                            <span class="rank-badge"><?= number_format($cat['units_sold']) ?> sold</span>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">No category data yet.</div>
                    <?php endif; ?>
                </div>

                <!-- Customer Growth chart -->
                <div class="rep-card">
                    <p class="rep-card-title">Customer Growth</p>
                    <p class="rep-card-sub">New registrations per month</p>
                    <div id="customerChart" style="width:100%;height:240px;"></div>
                </div>

            </div>

            <!-- ── Row 4: Recent Transactions (full width) ── -->
            <div class="rep-card">
                <div class="card-header-row" style="margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--cream-dark);">
                    <div>
                        <p class="rep-card-title">Recent Transactions</p>
                        <p class="rep-card-sub" style="margin-bottom:0;">Last 10 orders across all statuses</p>
                    </div>
                    <a href="orders.php" class="view-link">View All Orders</a>
                </div>
                <?php if (!empty($recentTx)): ?>
                <table class="tx-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentTx as $tx):
                            $sk  = strtolower($tx['order_status'] ?? '');
                            $css = $status_class[$sk] ?? 'os-pending';
                        ?>
                        <tr>
                            <td style="font-weight:600;color:var(--maroon);">#<?= str_pad($tx['order_id'], 4, '0', STR_PAD_LEFT) ?></td>
                            <td style="font-weight:500;"><?= htmlspecialchars($tx['customer_name']) ?></td>
                            <td class="text-muted-sm"><?= date('M d, Y g:i A', strtotime($tx['order_date'])) ?></td>
                            <td style="font-weight:500;">₱<?= number_format($tx['total_amount'], 2) ?></td>
                            <td>
                                <span class="order-status <?= $css ?>">
                                    <span class="dot"></span><?= ucfirst($tx['order_status'] ?? 'Pending') ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="empty-state"><i class="fa-solid fa-receipt"></i>No transactions yet.</div>
                <?php endif; ?>
            </div>

        </div><!-- /content-area -->
    </div><!-- /page-content-wrapper -->
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ══════════════════════════════════════
   PHP DATA → JS
══════════════════════════════════════ */
const revenueData = {
    daily:   <?= json_encode($dailyRevenue) ?>,
    weekly:  <?= json_encode($weeklyRevenue) ?>,
    monthly: <?= json_encode($monthlyRevenue) ?>
};

const statusData     = <?= json_encode($statusBreakdown) ?>;
const customerData   = <?= json_encode($customerGrowth) ?>;

const STATUS_COLORS = {
    completed:  '#2ECC71',
    pending:    '#F59E0B',
    processing: '#6366F1',
    cancelled:  '#E74C3C',
};

am5.ready(function () {

    /* ══════════════════════════════════════
       1. REVENUE COLUMN CHART
    ══════════════════════════════════════ */
    const revRoot = am5.Root.new("revenueChart");
    revRoot.setThemes([am5themes_Animated.new(revRoot)]);
    revRoot._logo.dispose();

    const revChart = revRoot.container.children.push(
        am5xy.XYChart.new(revRoot, { panX: false, panY: false, wheelX: "none", wheelY: "none" })
    );

    const revXRenderer = am5xy.AxisRendererX.new(revRoot, { minGridDistance: 40 });
    revXRenderer.labels.template.setAll({ fontSize: 11, fill: am5.color(0x7E7E7E), fontFamily: "Poppins" });
    revXRenderer.grid.template.setAll({ strokeOpacity: 0 });

    window.revXAxis = revChart.xAxes.push(am5xy.CategoryAxis.new(revRoot, {
        categoryField: "label",
        renderer: revXRenderer
    }));

    const revYAxis = revChart.yAxes.push(am5xy.ValueAxis.new(revRoot, {
        renderer: am5xy.AxisRendererY.new(revRoot, {})
    }));
    revYAxis.get("renderer").labels.template.setAll({ fontSize: 11, fill: am5.color(0x7E7E7E), fontFamily: "Poppins" });

    window.revSeries = revChart.series.push(am5xy.ColumnSeries.new(revRoot, {
        name: "Revenue",
        xAxis: window.revXAxis,
        yAxis: revYAxis,
        valueYField: "revenue",
        categoryXField: "label",
        tooltip: am5.Tooltip.new(revRoot, {
            labelText: "₱{valueY.formatNumber('#,###.00')}",
            getFillFromSprite: false,
            background: am5.Rectangle.new(revRoot, {
                fill: am5.color(0x822F2F),
                cornerRadiusTL: 8, cornerRadiusTR: 8, cornerRadiusBL: 8, cornerRadiusBR: 8
            })
        })
    }));

    window.revSeries.columns.template.setAll({
        cornerRadiusTL: 6, cornerRadiusTR: 6,
        fill: am5.color(0x822F2F),
        strokeOpacity: 0,
        width: am5.percent(55)
    });

    loadRevChart('daily');
    revChart.appear(1000, 100);

    /* ══════════════════════════════════════
       2. ORDER STATUS DONUT
    ══════════════════════════════════════ */
    const donutRoot = am5.Root.new("statusDonut");
    donutRoot.setThemes([am5themes_Animated.new(donutRoot)]);
    donutRoot._logo.dispose();

    const donutChart = donutRoot.container.children.push(
        am5percent.PieChart.new(donutRoot, { innerRadius: am5.percent(65), layout: donutRoot.horizontalLayout })
    );

    const donutSeries = donutChart.series.push(
        am5percent.PieSeries.new(donutRoot, {
            valueField: "count",
            categoryField: "status",
            tooltip: am5.Tooltip.new(donutRoot, {
                labelText: "{category}: {value}",
                getFillFromSprite: false,
                background: am5.Rectangle.new(donutRoot, {
                    fill: am5.color(0x822F2F),
                    cornerRadiusTL: 6, cornerRadiusTR: 6, cornerRadiusBL: 6, cornerRadiusBR: 6
                })
            })
        })
    );

    donutSeries.slices.template.setAll({ strokeOpacity: 0 });
    donutSeries.labels.template.set("visible", false);
    donutSeries.ticks.template.set("visible", false);

    const mappedStatus = statusData.map(s => ({
        status: s.status.charAt(0).toUpperCase() + s.status.slice(1),
        count:  parseInt(s.count),
        fill:   am5.color(STATUS_COLORS[s.status.toLowerCase()] || '#888888')
    }));
    donutSeries.data.setAll(mappedStatus);
    donutSeries.slices.template.adapters.add("fill", (fill, target) => {
        const dp = target.dataItem?.dataContext;
        return dp?.fill || fill;
    });
    donutSeries.slices.template.adapters.add("stroke", (stroke, target) => {
        const dp = target.dataItem?.dataContext;
        return dp?.fill || stroke;
    });
    donutChart.appear(1000, 100);

    /* ══════════════════════════════════════
       3. CUSTOMER GROWTH LINE CHART
    ══════════════════════════════════════ */
    const custRoot = am5.Root.new("customerChart");
    custRoot.setThemes([am5themes_Animated.new(custRoot)]);
    custRoot._logo.dispose();

    const custChart = custRoot.container.children.push(
        am5xy.XYChart.new(custRoot, { panX: false, panY: false, wheelX: "none", wheelY: "none" })
    );

    const custXRenderer = am5xy.AxisRendererX.new(custRoot, { minGridDistance: 50 });
    custXRenderer.labels.template.setAll({ fontSize: 10, fill: am5.color(0x7E7E7E), fontFamily: "Poppins", rotation: -30, centerY: am5.p50, centerX: am5.p100 });
    custXRenderer.grid.template.setAll({ strokeOpacity: 0 });

    const custXAxis = custChart.xAxes.push(am5xy.CategoryAxis.new(custRoot, {
        categoryField: "label",
        renderer: custXRenderer
    }));

    const custYAxis = custChart.yAxes.push(am5xy.ValueAxis.new(custRoot, {
        renderer: am5xy.AxisRendererY.new(custRoot, {}),
        min: 0
    }));
    custYAxis.get("renderer").labels.template.setAll({ fontSize: 10, fill: am5.color(0x7E7E7E) });

    const custSeries = custChart.series.push(am5xy.LineSeries.new(custRoot, {
        name: "New Customers",
        xAxis: custXAxis,
        yAxis: custYAxis,
        valueYField: "new_customers",
        categoryXField: "label",
        stroke: am5.color(0x822F2F),
        fill: am5.color(0x822F2F),
        tooltip: am5.Tooltip.new(custRoot, {
            labelText: "{valueY} new customers",
            getFillFromSprite: false,
            background: am5.Rectangle.new(custRoot, {
                fill: am5.color(0x822F2F),
                cornerRadiusTL: 6, cornerRadiusTR: 6, cornerRadiusBL: 6, cornerRadiusBR: 6
            })
        })
    }));

    custSeries.strokes.template.setAll({ strokeWidth: 2.5 });
    custSeries.bullets.push(() =>
        am5.Bullet.new(custRoot, {
            sprite: am5.Circle.new(custRoot, { radius: 4, fill: am5.color(0x822F2F), strokeOpacity: 0 })
        })
    );

    const custFill = custSeries.set("fill", am5.color(0x822F2F));
    custSeries.fills.template.setAll({ fillOpacity: 0.08, visible: true });

    const cgData = customerData.length
        ? customerData.map(r => ({ label: r.label, new_customers: parseInt(r.new_customers) }))
        : [{ label: 'No data', new_customers: 0 }];

    custXAxis.data.setAll(cgData);
    custSeries.data.setAll(cgData);
    custChart.appear(1000, 100);

});

/* ══════════════════════════════════════
   Revenue chart switcher
══════════════════════════════════════ */
function loadRevChart(period) {
    const raw = revenueData[period] || [];
    const data = raw.map(r => ({ label: r.label, revenue: parseFloat(r.revenue) }));
    window.revXAxis.data.setAll(data);
    window.revSeries.data.setAll(data);
    window.revSeries.appear(500);
}

function switchRevChart(btn, period) {
    document.querySelectorAll('.chart-toggle-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    loadRevChart(period);
}
</script>
</body>
</html>
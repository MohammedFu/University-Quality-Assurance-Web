<?php
// Start session and output buffering at the very beginning
ob_start();
session_start();

// Verify includes exist before requiring
$required_includes = [
    __DIR__ . '/includes/auth.php',
    __DIR__ . '/includes/db.php',
    __DIR__ . '/includes/helpers.php'
];

foreach ($required_includes as $include) {
    if (!file_exists($include)) {
        die("System error: Required file missing - " . basename($include));
    }
    require_once $include;
}

// Verify required session variables
if (!isset($_SESSION['user_id'], $_SESSION['email'], $_SESSION['role'])) {
    header('Location: login.php');
    exit();
}

// Validate session and role
if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer') {
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$userEmail = htmlspecialchars($_SESSION['email'], ENT_QUOTES, 'UTF-8');
$userRole = htmlspecialchars(ucfirst($_SESSION['role']), ENT_QUOTES, 'UTF-8');

// Fetch data with error handling
try {
    $courses = getLecturerCourses($pdo, $userId);
    $learningOutcomes = getLearningOutcomes($pdo, $userId);
} catch (PDOException $e) {
    die("System temporarily unavailable. Please try again later.");
}

// Prepare chart data with validation
$loChartData = [
    'labels' => [],
    'question_counts' => [],
    'mastery_percentages' => []
];

$totalQuestions = 0;
foreach ($learningOutcomes as $lo) {
    $loSymbol = !empty($lo['lo_symbol']) ? htmlspecialchars($lo['lo_symbol'], ENT_QUOTES, 'UTF-8') : 'LO';

    $loChartData['labels'][] = $loSymbol;
    $loChartData['question_counts'][] = (int)($lo['question_count'] ?? 0);
    $loChartData['mastery_percentages'][] = (float)($lo['mastery_percentage'] ?? 0);
    $totalQuestions += (int)($lo['question_count'] ?? 0);
}

// Calculate average mastery
$averageMastery = 0;
if (!empty($loChartData['mastery_percentages'])) {
    $averageMastery = round(array_sum($loChartData['mastery_percentages']) / count($loChartData['mastery_percentages']), 1);
}

// Clear output buffer before HTML
ob_end_clean();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LO Dashboard - University QA</title>

    <!-- Favicon -->
    <link href="img/favicon.jpg" rel="icon">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">

    <!-- AOS Animation -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css" rel="stylesheet">

    <!-- Animate.css for animations -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
        }

        .welcome-banner {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            color: white;
            border-radius: 0.5rem;
        }

        .chart-container {
            height: 400px;
            position: relative;
        }

        /* External legend layout for pie chart */
        .chart-flex {
            display: flex;
            gap: 16px;
            height: 100%;
        }
        .chart-canvas-wrap {
            position: relative;
            flex: 1 1 auto;
        }
        .chart-legend {
            width: 180px;
            max-height: 100%; /* match chart container height */
            overflow-y: auto;
            padding-right: 4px;
        }
        .chart-legend::-webkit-scrollbar { width: 6px; }
        .chart-legend::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        .chart-legend ul { list-style: none; margin: 0; padding-left: 0; }
        .chart-legend li { display: flex; align-items: center; gap: 8px; margin: 2px 0; font-size: 12px; line-height: 1.15; }
        .chart-legend li span { display: inline-block; width: 10px; height: 10px; border-radius: 50%; }

        @media (max-width: 768px) {
            .chart-container {
                height: 300px;
            }
        }

        .lo-card {
            transition: all 0.2s ease;
        }

        .lo-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        #chartLoading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: rgba(255,255,255,0.8);
            z-index: 1000;
            border-radius: 0.5rem;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-left-color: var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        #masteryLabels {
            padding: 15px;
            background-color: #f9fafb;
            border-radius: 0.5rem;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
        }

        #masteryLabelsList {
            max-height: 2000px; /* Large enough to show all content */
            overflow-y: auto;
            scrollbar-width: thin;
            transition: all 0.5s ease;
            display: flex;
            flex-wrap: wrap;
            opacity: 1;
            overflow: hidden;
        }

        #masteryLabelsList.mastery-hidden {
            max-height: 0;
            opacity: 0;
            margin-top: 0;
            margin-bottom: 0;
            padding-top: 0;
            padding-bottom: 0;
            overflow: hidden;
        }

        /* Toggle button styles */
        .toggle-btn {
            position: relative;
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .toggle-btn i {
            transition: all 0.3s ease;
        }

        .toggle-btn.active {
            background-color: var(--primary);
            color: white;
        }

        .toggle-btn.inactive {
            background-color: #e5e7eb;
            color: #6b7280;
        }

        .toggle-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Tooltip styles */
        .tooltip-container {
            position: relative;
            display: inline-block;
        }

        .tooltip-text {
            visibility: hidden;
            width: 200px;
            background-color: rgba(0,0,0,0.8);
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 8px;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            transform: translateX(-50%);
            opacity: 0;
            transition: opacity 0.3s;
            font-size: 0.8rem;
            pointer-events: none;
        }

        .tooltip-container:hover .tooltip-text {
            visibility: visible;
            opacity: 1;
        }

        #masteryLabelsList .mastery-card {
            margin-bottom: 15px;
            transition: all 0.2s ease;
        }

        #masteryLabelsList .mastery-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .mastery-card .card-header {
            padding: 10px 15px;
            font-weight: bold;
        }

        .mastery-card .topic-name {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 3px;
        }

        .mastery-card .card-body {
            padding: 15px;
        }

        .mastery-percentage {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .classification-badge {
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 10px;
            background-color: #e5e7eb;
            color: #4b5563;
            display: inline-block;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <div class="container-xxl position-relative bg-white d-flex p-0">
        <!-- Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="content">
            <div class="container-fluid pt-4 px-4">
                <!-- Header -->
                <div class="welcome-banner p-4 mb-4" data-aos="fade-down">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-chart-line fa-3x me-3"></i>
                        <div>
                            <h1 class="text-white mb-2">Learning Outcomes Dashboard</h1>
                            <p class="mb-0 text-white-50">Analyze student performance across learning outcomes</p>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card p-3 mb-4" data-aos="fade-up">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0 text-primary">Filter Learning Outcomes</h6>
                        <button class="btn btn-sm btn-outline-primary d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse" aria-expanded="false" aria-controls="filterCollapse">
                            <i class="fas fa-filter me-1"></i> Filters
                        </button>
                    </div>

                    <div class="collapse d-md-block" id="filterCollapse">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="loCourseFilter" class="form-label d-md-none">Course</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="fas fa-book"></i></span>
                                    <select class="form-select" id="loCourseFilter">
                                        <option value="">All Courses</option>
                                        <?php foreach ($courses as $course): ?>
                                            <option value="<?= (int)$course['course_id'] ?>">
                                                <?= htmlspecialchars($course['course_name'], ENT_QUOTES, 'UTF-8') ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="loTypeFilter" class="form-label d-md-none">ABET Classification</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-primary text-white"><i class="fas fa-filter"></i></span>
                                    <select class="form-select" id="loTypeFilter">
                                        <option value="all">All ABET Classifications</option>
                                        <?php
                                        $abetOptions = [
                                            'a' => 'Apply math, science, and engineering principles',
                                            'b' => 'Design/conduct experiments and analyze data',
                                            'c' => 'Design systems under constraints',
                                            'd' => 'Function on multidisciplinary teams',
                                            'e' => 'Identify/formulate/solve engineering problems',
                                            'f' => 'Understand ethical responsibility',
                                            'g' => 'Communicate effectively',
                                            'h' => 'Understand global/societal impacts',
                                            'i' => 'Engage in lifelong learning',
                                            'j' => 'Knowledge of contemporary issues',
                                            'k' => 'Use modern engineering tools'
                                        ];
                                        foreach ($abetOptions as $value => $label): ?>
                                            <option value="<?= $value ?>"><?= $value . ': ' . $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Content -->
                <div class="row" data-aos="fade-up">
                    <!-- Chart Column -->
                    <div class="col-lg-8">
                        <div class="card p-3 h-100">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Learning Outcomes Trend</h5>
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm active" data-type="line" title="Line Chart">
                                        <i class="fas fa-chart-line"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" data-type="bar" title="Bar Chart">
                                        <i class="fas fa-chart-bar"></i>
                                    </button>
                                    <button class="btn btn-outline-primary btn-sm" data-type="pie" title="Pie Chart">
                                        <i class="fas fa-chart-pie"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="chart-container position-relative mb-4">
                                <div id="chartLoading" class="d-none">
                                    <div class="loading-spinner"></div>
                                </div>
                                <div class="chart-flex">
                                    <div class="chart-canvas-wrap">
                                        <canvas id="loTrendChart"></canvas>
                                    </div>
                                    <div id="loExternalLegend" class="chart-legend d-none"></div>
                                </div>
                            </div>
                            <div id="masteryLabels" class="mt-4">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                    <h6 class="mb-0 text-primary">Learning Outcomes Mastery Percentages</h6>
                                    <div class="tooltip-container">
                                        <button id="toggleMasteryBtn" class="toggle-btn active">
                                            <i class="fas fa-eye-slash me-1"></i> <span class="toggle-text">Hide</span>
                                        </button>
                                        <span class="tooltip-text">Toggle visibility of mastery percentage cards</span>
                                    </div>
                                </div>
                                <div id="masteryLabelsList" class="row g-3">
                                    <!-- Mastery percentages will be displayed here -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metrics Column -->
                    <div class="col-lg-4">
                        <div class="card p-3 h-100">
                            <h5 class="mb-3">Key Metrics</h5>
                            <div class="d-flex justify-content-between mb-3">
                                <div class="text-center">
                                    <div class="fs-2 fw-bold text-primary"><?= count($learningOutcomes) ?></div>
                                    <div class="text-muted">LOs Tracked</div>
                                </div>
                                <div class="text-center">
                                    <div class="fs-2 fw-bold text-primary"><?= $totalQuestions ?></div>
                                    <div class="text-muted">Questions</div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Average Mastery</span>
                                    <span><?= $averageMastery ?>%</span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success"
                                         role="progressbar"
                                         style="width: <?= $averageMastery ?>%"
                                         aria-valuenow="<?= $averageMastery ?>"
                                         aria-valuemin="0"
                                         aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Section -->
                <div class="row mt-4" data-aos="fade-up">
                    <div class="col-12">
                        <div id="loResults" class="row g-4">
                            <?= renderLoCards($learningOutcomes) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize animations
        AOS.init({
            duration: 600,
            once: true
        });

        // Enhanced chart configuration for different chart types
        const chartContainerEl = document.querySelector('.chart-container');
        function applyChartContainerHeight(type) {
            if (!chartContainerEl) return;
            // Lower height for pie to remove excess bottom whitespace
            chartContainerEl.style.height = (type === 'pie') ? '410px' : '400px';
        }

        // Global learning outcomes data from PHP for classification lookup
        const LEARNING_OUTCOMES = <?= json_encode($learningOutcomes) ?>;

        // ABET keys in fixed order
        const ABET_KEYS = ['a','b','c','d','e','f','g','h','i','j','k'];

        // Percent formatter used by tooltip and legend
        function formatPercent(v) {
            const n = Number(v);
            if (!isFinite(n)) return '0%';
            return `${n.toFixed(1)}%`;
        }

        // Determine ABET classification for a given LO
        function getLoClassification(label, description) {
            if (description) {
                const d = description.toLowerCase();
                if (d.includes('math') || d.includes('science') || d.includes('engineering principles')) return 'a';
                if (d.includes('experiment') || d.includes('data')) return 'b';
                if (d.includes('design') || d.includes('constraints')) return 'c';
                if (d.includes('team')) return 'd';
                if (d.includes('problem')) return 'e';
                if (d.includes('ethical')) return 'f';
                if (d.includes('communicate')) return 'g';
                if (d.includes('global') || d.includes('societal')) return 'h';
                if (d.includes('learning')) return 'i';
                if (d.includes('contemporary')) return 'j';
                if (d.includes('tools')) return 'k';
            }
            if (label && label.length > 0) {
                const first = label.charAt(0).toLowerCase();
                if (ABET_KEYS.includes(first)) return first;
            }
            return null;
        }

        // Build ABET-aggregated chart data from LO-level chart data
        function buildAbetChartData(srcChartData) {
            const sums = Object.fromEntries(ABET_KEYS.map(k => [k, 0]));
            const counts = Object.fromEntries(ABET_KEYS.map(k => [k, 0]));

            const labels = (srcChartData?.labels) || [];
            const values = (srcChartData?.mastery_percentages) || [];

            for (let i = 0; i < labels.length; i++) {
                const label = labels[i];
                const val = Number(values[i]) || 0;
                const lo = (LEARNING_OUTCOMES || []).find(x => x.lo_symbol === label) || {};
                const cls = getLoClassification(label, lo.lo_description || '');
                if (cls) {
                    sums[cls] += val;
                    counts[cls] += 1;
                }
            }

            const outLabels = [...ABET_KEYS];
            const outValues = outLabels.map(k => counts[k] > 0 ? +(sums[k] / counts[k]).toFixed(1) : 0);

            return {
                labels: outLabels,
                mastery_percentages: outValues,
                question_counts: outValues // unused for pie but keep shape
            };
        }
        function getChartConfig(chartData, chartType) {
            const baseConfig = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                family: 'Poppins, sans-serif'
                            }
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: 'rgba(0,0,0,0.7)',
                        bodyFont: {
                            family: 'Poppins, sans-serif'
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            };

            switch(chartType) {
                case 'pie':
                    // Generate a distinct color for each label to avoid repeats
                    const allLabels = chartData.labels || [];
                    const allValues = chartData.mastery_percentages || [];
                    const dynamicColors = allLabels.map((_, i) => `hsl(${(i * 47) % 360} 70% 55%)`);

                    // If there are no non-zero values, draw a single full gray slice
                    const hasAnyValue = allValues.some(v => Number(v) > 0);
                    if (!hasAnyValue) {
                        return {
                            ...baseConfig,
                            type: 'pie',
                            data: {
                                labels: ['No Data'],
                                datasets: [{
                                    data: [1],
                                    _allZero: true,
                                    backgroundColor: ['#9ca3af'],
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                ...baseConfig.options,
                                layout: { padding: 10 },
                                interaction: { mode: 'nearest', intersect: true },
                                plugins: {
                                    ...baseConfig.plugins,
                                    legend: { display: false },
                                    tooltip: {
                                        ...baseConfig.plugins.tooltip,
                                        callbacks: {
                                            label: function() { return 'No Data'; }
                                        }
                                    }
                                }
                            }
                        };
                    }

                    // Build filtered arrays for slices with value > 0
                    const indexMap = [];
                    const filteredLabels = [];
                    const filteredValues = [];
                    const filteredColors = [];
                    for (let i = 0; i < allLabels.length; i++) {
                        const v = Number(allValues[i]) || 0;
                        if (v > 0) {
                            indexMap.push(i);
                            filteredLabels.push(allLabels[i]);
                            filteredValues.push(v);
                            filteredColors.push(dynamicColors[i]);
                        }
                    }

                    return {
                        ...baseConfig,
                        type: 'pie',
                        data: {
                            labels: filteredLabels,
                            datasets: [{
                                data: filteredValues,
                                // Keep references to the original arrays for tooltips/legend
                                _rawLabels: allLabels,
                                _rawData: allValues,
                                _indexMap: indexMap, // maps filtered index -> raw index
                                backgroundColor: filteredColors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            ...baseConfig.options,
                            layout: {
                                padding: 10
                            },
                            // Override interaction for pie charts to ensure correct slice is targeted
                            interaction: {
                                mode: 'nearest',
                                intersect: true
                            },
                            plugins: {
                                ...baseConfig.plugins,
                                legend: { display: false }, // use external legend for pie
                                tooltip: {
                                    ...baseConfig.plugins.tooltip,
                                    callbacks: {
                                        label: function(context) {
                                            // Map filtered index back to raw index for accurate value
                                            const ds = context.chart.data.datasets[context.datasetIndex];
                                            if (ds && ds._allZero) { return 'No Data'; }
                                            const rawIndex = (ds && ds._indexMap) ? ds._indexMap[context.dataIndex] : context.dataIndex;
                                            const rawVal = (ds && ds._rawData) ? ds._rawData[rawIndex] : context.raw;
                                            const rawLabel = (ds && ds._rawLabels) ? ds._rawLabels[rawIndex] : context.label;
                                            return `${rawLabel}: ${formatPercent(rawVal)} mastery`;
                                        }
                                    }
                                }
                            }
                        },

                    };

                case 'bar':
                    return {
                        ...baseConfig,
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Question Count',
                                    data: chartData.question_counts,
                                    backgroundColor: 'rgba(79, 70, 229, 0.7)',
                                    borderColor: '#4f46e5',
                                    borderWidth: 1,
                                    order: 2
                                },
                                {
                                    label: 'Mastery Percentage',
                                    data: chartData.mastery_percentages,
                                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                                    borderColor: '#10b981',
                                    borderWidth: 1,
                                    type: 'line',
                                    order: 1
                                }
                            ]
                        },
                        options: {
                            ...baseConfig.options,
                            scales: {
                                y: {
                                    type: 'linear',
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Questions',
                                        font: {
                                            family: 'Poppins, sans-serif'
                                        }
                                    },
                                    beginAtZero: true
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Mastery (%)',
                                        font: {
                                            family: 'Poppins, sans-serif'
                                        }
                                    },
                                    min: 0,
                                    max: 100,
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            }
                        },

                    };

                default: // line chart
                    return {
                        ...baseConfig,
                        type: 'line',
                        data: {
                            labels: chartData.labels,
                            datasets: [
                                {
                                    label: 'Question Count',
                                    data: chartData.question_counts,
                                    borderColor: '#4f46e5',
                                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                                    tension: 0.3,
                                    yAxisID: 'y'
                                },
                                {
                                    label: 'Mastery Percentage',
                                    data: chartData.mastery_percentages,
                                    borderColor: '#10b981',
                                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                    tension: 0.3,
                                    yAxisID: 'y1',
                                    pointBackgroundColor: '#10b981',
                                    pointRadius: 5,
                                    pointHoverRadius: 7
                                }
                            ]
                        },
                        options: {
                            ...baseConfig.options,
                            scales: {
                                y: {
                                    type: 'linear',
                                    display: true,
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Questions',
                                        font: {
                                            family: 'Poppins, sans-serif'
                                        }
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    display: true,
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Mastery (%)',
                                        font: {
                                            family: 'Poppins, sans-serif'
                                        }
                                    },
                                    min: 0,
                                    max: 100,
                                    grid: {
                                        drawOnChartArea: false
                                    },
                                    ticks: {
                                        callback: function(value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            }
                        },

                    };
            }
        }

        // Enhanced filter handling with debounce
        let filterTimeout;
        function handleFilterChange() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(() => {
                updateLOVisualization();
            }, 300);
        }

        // Chart variables
        let loTrendChart = null;
        const chartCtx = document.getElementById('loTrendChart').getContext('2d');
        let currentChartType = 'line';
        const defaultChartData = <?= json_encode($loChartData) ?>;

        // Initialize the chart
        function initializeChart() {
            applyChartContainerHeight(currentChartType);
            loTrendChart = new Chart(chartCtx, getChartConfig(defaultChartData, currentChartType));
            updateMasteryLabels(defaultChartData);
            updateExternalLegend();

            // Check localStorage for mastery percentages visibility preference
            const masteryPercentagesVisible = localStorage.getItem('masteryPercentagesVisible');
            if (masteryPercentagesVisible === 'false') {
                // Hide the mastery percentages
                const masteryLabelsList = document.getElementById('masteryLabelsList');
                masteryLabelsList.style.maxHeight = '0';
                masteryLabelsList.style.opacity = '0';
                masteryLabelsList.classList.add('mastery-hidden');

                // Update toggle button appearance
                const toggleMasteryBtn = document.getElementById('toggleMasteryBtn');
                const toggleText = toggleMasteryBtn.querySelector('.toggle-text');
                const toggleIcon = toggleMasteryBtn.querySelector('i');

                toggleText.textContent = 'Show';
                toggleIcon.className = 'fas fa-eye me-1';
                toggleMasteryBtn.classList.remove('active');
                toggleMasteryBtn.classList.add('inactive');
            }
        }

        // Function to update mastery percentage labels
        function updateMasteryLabels(chartData) {
            const masteryLabelsContainer = document.getElementById('masteryLabelsList');
            masteryLabelsContainer.innerHTML = '';

            // Create mastery percentage labels
            if (chartData && chartData.labels && chartData.mastery_percentages) {
                const labels = chartData.labels;
                const percentages = chartData.mastery_percentages;

                // Get learning outcomes data from PHP
                const learningOutcomes = <?= json_encode($learningOutcomes) ?>;

                // Create a card for each learning outcome
                for (let i = 0; i < labels.length; i++) {
                    const label = labels[i];
                    const percentage = percentages[i];

                    // Find the corresponding learning outcome data
                    const loData = learningOutcomes.find(lo => lo.lo_symbol === label) || {};
                    const topicName = loData.topic_name || 'Unknown Topic';
                    const loDescription = loData.lo_description || '';
                    const courseName = loData.course_name || 'Unknown Course';

                    // Determine classification based on ABET criteria
                    let classification = '';
                    if (loDescription) {
                        if (loDescription.includes('math') || loDescription.includes('science') || loDescription.includes('engineering principles')) {
                            classification = 'a';
                        } else if (loDescription.includes('experiment') || loDescription.includes('data')) {
                            classification = 'b';
                        } else if (loDescription.includes('design') || loDescription.includes('constraints')) {
                            classification = 'c';
                        } else if (loDescription.includes('team')) {
                            classification = 'd';
                        } else if (loDescription.includes('problem')) {
                            classification = 'e';
                        } else if (loDescription.includes('ethical')) {
                            classification = 'f';
                        } else if (loDescription.includes('communicate')) {
                            classification = 'g';
                        } else if (loDescription.includes('global') || loDescription.includes('societal')) {
                            classification = 'h';
                        } else if (loDescription.includes('learning')) {
                            classification = 'i';
                        } else if (loDescription.includes('contemporary')) {
                            classification = 'j';
                        } else if (loDescription.includes('tools')) {
                            classification = 'k';
                        }
                    }

                    // If classification is not determined from description, try to extract from lo_symbol
                    if (!classification && label && label.length > 0) {
                        const firstChar = label.charAt(0).toLowerCase();
                        if ('abcdefghijk'.includes(firstChar)) {
                            classification = firstChar;
                        }
                    }

                    // Determine color based on mastery level
                    let color = '#4f46e5'; // Default blue
                    if (percentage >= 80) color = '#10b981'; // Green for high mastery
                    else if (percentage < 50) color = '#ef4444'; // Red for low mastery
                    else if (percentage < 70) color = '#f59e0b'; // Orange for medium mastery

                    // Create card element
                    const cardElement = document.createElement('div');
                    cardElement.className = 'col-md-4 col-sm-6 col-12';
                    cardElement.innerHTML = `
                        <div class="card mastery-card h-100">
                            <div class="card-header d-flex justify-content-between align-items-center" style="border-left: 4px solid ${color}; padding: 10px 15px;">
                                <div>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-bold me-2">${label}</span>
                                        ${classification ? `<span class="classification-badge">ABET ${classification.toUpperCase()}</span>` : ''}
                                    </div>
                                    <div class="topic-name">${topicName}</div>
                                </div>
                                <div class="mastery-percentage" style="color: ${color};">${percentage}%</div>
                            </div>
                            <div class="card-body p-3">
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar" role="progressbar" style="width: ${percentage}%; background-color: ${color};"
                                         aria-valuenow="${percentage}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="small text-muted">${courseName}</div>
                            </div>
                        </div>
                    `;

                    masteryLabelsContainer.appendChild(cardElement);
                }
            }
        }

        initializeChart();

        // Chart type toggle buttons
        const legendElRef = document.getElementById('loExternalLegend');
        document.querySelectorAll('.btn-group button').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                currentChartType = this.dataset.type;
                applyChartContainerHeight(currentChartType);
                // Toggle legend visibility state immediately
                if (legendElRef) {
                    if (currentChartType === 'pie') legendElRef.classList.remove('d-none');
                    else legendElRef.classList.add('d-none');
                }
                updateExternalLegend();

                // Update active state
                document.querySelectorAll('.btn-group button').forEach(btn => {
                    btn.classList.remove('active');
                });
                this.classList.add('active');

                updateLOVisualization();
            });

            // Accessibility enhancements
            button.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    button.click();
                }
            });
        });

        // Initialize event listeners with debounce
        document.getElementById('loCourseFilter').addEventListener('change', handleFilterChange);
        document.getElementById('loTypeFilter').addEventListener('change', handleFilterChange);

        // Toggle Mastery Percentages visibility
        const toggleMasteryBtn = document.getElementById('toggleMasteryBtn');
        const masteryLabelsList = document.getElementById('masteryLabelsList');
        const toggleText = toggleMasteryBtn.querySelector('.toggle-text');
        const toggleIcon = toggleMasteryBtn.querySelector('i');

        // Function to update toggle button appearance
        function updateToggleButton(isVisible) {
            if (isVisible) {
                toggleText.textContent = 'Hide';
                toggleIcon.className = 'fas fa-eye-slash me-1';
                toggleMasteryBtn.classList.add('active');
                toggleMasteryBtn.classList.remove('inactive');
            } else {
                toggleText.textContent = 'Show';
                toggleIcon.className = 'fas fa-eye me-1';
                toggleMasteryBtn.classList.remove('active');
                toggleMasteryBtn.classList.add('inactive');
            }
        }

        toggleMasteryBtn.addEventListener('click', function() {
            // Toggle visibility with animation
            if (masteryLabelsList.classList.contains('mastery-hidden')) {
                // Show the mastery percentages
                masteryLabelsList.style.maxHeight = '2000px'; // Large enough to show all content
                masteryLabelsList.style.opacity = '1';
                masteryLabelsList.classList.remove('mastery-hidden');

                // Update button appearance
                updateToggleButton(true);

                // Store preference in localStorage
                localStorage.setItem('masteryPercentagesVisible', 'true');
            } else {
                // Hide the mastery percentages
                masteryLabelsList.style.maxHeight = '0';
                masteryLabelsList.style.opacity = '0';
                masteryLabelsList.classList.add('mastery-hidden');

                // Update button appearance
                updateToggleButton(false);

                // Store preference in localStorage
                localStorage.setItem('masteryPercentagesVisible', 'false');
            }

            // Add a subtle animation effect
            toggleMasteryBtn.classList.add('animate__animated', 'animate__pulse');
            setTimeout(() => {
                toggleMasteryBtn.classList.remove('animate__animated', 'animate__pulse');
            }, 500);
        });

        // Responsive adjustments
        function handleResize() {
            if (loTrendChart) {
                loTrendChart.resize();
            }
            // Keep legend sizing in sync on resize
            updateExternalLegend();
        }

        window.addEventListener('resize', handleResize);

        // External legend renderer for pie chart
        function updateExternalLegend() {
            const legendEl = document.getElementById('loExternalLegend');
            if (!legendEl) return;

            if (currentChartType !== 'pie') {
                legendEl.classList.add('d-none');
                legendEl.innerHTML = '';
                return;
            }

            try {
                const ds = loTrendChart?.data?.datasets?.[0];
                if (ds?._allZero) {
                    legendEl.innerHTML = '<ul><li><span style="background:#9ca3af"></span>No Data (0%)</li></ul>';
                    legendEl.classList.remove('d-none');
                    return;
                }
                const rawLabels = ds?._rawLabels || loTrendChart?.data?.labels || [];
                const rawValues = ds?._rawData || ds?.data || [];
                const filteredIndexMap = ds?._indexMap || [];
                const filteredColors = ds?.backgroundColor || [];

                // Color lookup: non-zero use filteredColors via indexMap, zeros use gray
                const colorByRawIndex = new Array(rawLabels.length).fill('#9ca3af');
                for (let i = 0; i < filteredIndexMap.length; i++) {
                    const rawIdx = filteredIndexMap[i];
                    colorByRawIndex[rawIdx] = filteredColors[i] || '#9ca3af';
                }

                // Build items including zeros; sort descending by value
                const items = rawLabels.map((label, i) => ({
                    label,
                    value: Number(rawValues[i]) || 0,
                    color: colorByRawIndex[i]
                })).sort((a, b) => b.value - a.value);

                let html = '<ul>';
                for (const it of items) {
                    html += `<li><span style="background:${it.color}"></span>${it.label} (${formatPercent(it.value)})</li>`;
                }
                html += '</ul>';

                legendEl.innerHTML = html;
                legendEl.classList.remove('d-none');
            } catch (e) {
                console.warn('Legend render failed:', e);
                legendEl.classList.add('d-none');
            }
        }

        // Main update function
        async function updateLOVisualization() {
            const resultsElement = document.getElementById('loResults');
            const chartLoading = document.getElementById('chartLoading');

            try {
                // Show loading states
                chartLoading.classList.remove('d-none');
                resultsElement.innerHTML = '<div class="col-12 text-center py-5"><i class="fas fa-spinner fa-spin fa-2x text-muted"></i></div>';

                // Get current filter values
                const courseId = document.getElementById('loCourseFilter').value || '';
                const loType = document.getElementById('loTypeFilter').value || 'all';

                // Make API request
                const response = await fetch(`lo_filter.php?course_id=${encodeURIComponent(courseId)}&lo_type=${encodeURIComponent(loType)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                // Check for HTTP errors
                if (!response.ok) {
                    console.error(`Server error: ${response.status}`);
                    // Try to get error details if available
                    const errorText = await response.text();
                    console.error('Error response:', errorText);

                    try {
                        // Try to parse error as JSON
                        const errorJson = JSON.parse(errorText);
                        throw new Error(errorJson.message || `Server error: ${response.status}`);
                    } catch (parseError) {
                        // If parsing fails, use generic error
                        throw new Error(`Server error: ${response.status}. Check server logs for details.`);
                    }
                }

                // Get response text first to check for any PHP errors
                const responseText = await response.text();

                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    console.error('Failed to parse JSON:', responseText);
                    throw new Error('Invalid JSON response from server');
                }

                // Check for application errors
                if (!data || data.success === false) {
                    throw new Error(data?.message || 'Failed to load data');
                }

                // Update UI - ensure we're only setting valid HTML
                if (data.html) {
                    resultsElement.innerHTML = data.html;
                } else {
                    resultsElement.innerHTML = '<div class="col-12"><div class="alert alert-info">No learning outcomes found for selected filters</div></div>';
                }

                // Update chart
                if (loTrendChart) {
                    loTrendChart.destroy();
                }

                let chartDataToUse;
                if (data.chart_data && data.chart_data.labels && data.chart_data.labels.length > 0) {
                    chartDataToUse = data.chart_data;
                } else {
                    // If no chart data, use empty chart data
                    chartDataToUse = { labels: ['No Data'], question_counts: [0], mastery_percentages: [0] };
                }

                // Update chart and mastery labels
                loTrendChart = new Chart(chartCtx, getChartConfig(chartDataToUse, currentChartType));
                updateMasteryLabels(chartDataToUse);
                updateExternalLegend();

                // Hide loading indicator
                chartLoading.classList.add('d-none');

                // Maintain visibility state after update
                const masteryPercentagesVisible = localStorage.getItem('masteryPercentagesVisible');
                if (masteryPercentagesVisible === 'false') {
                    const masteryLabelsList = document.getElementById('masteryLabelsList');
                    masteryLabelsList.style.maxHeight = '0';
                    masteryLabelsList.style.opacity = '0';
                    masteryLabelsList.classList.add('mastery-hidden');

                    // Update toggle button appearance
                    updateToggleButton(false);
                }

            } catch (error) {
                console.error('Error:', error);

                // Hide loading indicator
                chartLoading.classList.add('d-none');

                resultsElement.innerHTML = `
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div>
                                    <strong>Error:</strong> ${error.message || 'Failed to load data'}
                                </div>
                            </div>
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="updateLOVisualization()">
                                    <i class="fas fa-sync-alt me-1"></i> Try Again
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }
        }

        // Initial data load
        updateLOVisualization();
    });
    </script>
</body>
</html>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /Org-Accreditation-System/frontend/views/auth/login.php");
    exit();
}

$dashboard_items = [
    [
        'title' => 'Total Organizations',
        'value' => 25,
        'change' => '+5 from last year',
        'change_color' => 'text-green-500',
    ],
    [
        'title' => 'Pending Requirements',
        'value' => 10,
        'change' => '-2 from last month',
        'change_color' => 'text-orange-500',
    ],
    [
        'title' => 'Fully Accredited',
        'value' => 15,
        'change' => '44% completion rate',
        'change_color' => 'text-green-500',
    ],
    [
        'title' => 'Needs Attention',
        'value' => 5,
        'change' => 'Revisions requested',
        'change_color' => 'text-red-500',
    ]
]
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="/Org-Accreditation-System/frontend/src/output.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&display=swap" rel="stylesheet">
</head>

<body class="bg-[#F1ECEC] h-screen">
    <?php include_once '../../components/header.php'; ?>
    <div id="main-content" class="p-10 pt-0 h-full flex gap-8">
        <?php include_once '../../components/admin-sidebar.php'; ?>
        <div class="flex flex-col w-full gap-5">
            <div class="flex flex-col gap-2">
                <p class="manrope-bold text-4xl">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
                <p class="text-md">Track all school organizations accreditation progress for S.Y. 2025-2026</p> <!--Academic Year should be fetched from db-->
            </div>
            <div class="flex gap-5">
                <?php foreach ($dashboard_items as $item): ?>
                    <div class="flex-1 w-full h-40 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                        <div class="px-7 w-full h-full flex flex-col justify-center gap-3">
                            <p class="dm-sans-semibold text-xl"><?php echo $item['title']; ?></p>
                            <div>
                                <p class="dm-sans-bold text-4xl <?php echo $item['change_color']; ?>"><?php echo $item['value']; ?></p>
                                <p class="<?php echo $item['change_color']; ?>"><?php echo $item['change']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex-1 w-full h-50 bg-white rounded-xl border-[0.1px] border-black shadow-xl/20">
                <div class="p-7 w-full h-full flex flex-col">
                    <div class="mb-5">
                        <p class="dm-sans-bold text-2xl">Recent Submissions</p>
                        <p class="">Latest document submissions requiring review</p>
                    </div>
                    <div>
                        <div class="border border-gray-400 w-full h-30 rounded-2xl">
                            <div class="p-5 px-8 flex justify-between">
                                <div>
                                    <p class="dm-sans-bold text-xl">Google Developer Groups on Campus Crimsons</p>
                                    <p class="text-md">Financial Report</p>
                                    <p class="text-sm">2 hours ago</p>
                                </div>
                                <div class="flex justify-center items-center">
                                    <button class="bg-[#940505] text-white hover:text-[#940505] px-8 py-2 rounded-lg hover:bg-white border hover:border-black ease-in-out duration-300">Review</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
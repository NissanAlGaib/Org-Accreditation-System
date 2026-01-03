<?php
$currentPage = basename($_SERVER['PHP_SELF']);
$base_path = '/Org-Accreditation-System/frontend/src/imgs/sidebar/';
$menu_items = [
    [
        'label' => 'Dashboard',
        'link'  => 'dashboard.php',
        'icon'   => $base_path . 'dashboard.png',
        'icon-active' => $base_path . 'dashboard-active.png'
    ],
    [
        'label' => 'Organizations',
        'link'  => 'organization.php',
        'icon'   => $base_path . 'my-org.png',
        'icon-active' => $base_path . 'my-org-active.png'
    ],
    [
        'label' => 'Documents',
        'link'  => 'documents.php',
        'icon'   => $base_path . 'requirements.png',
        'icon-active' => $base_path . 'requirements-active.png'
    ],
    [
        'label' => 'Create Accounts',
        'link'  => 'create-accounts.php',
        'icon'   => $base_path . 'create-account.png',
        'icon-active' => $base_path . 'create-account-active.png'
    ],
    [
        'label' => 'Requirements',
        'link'  => 'requirements.php',
        'icon'   => $base_path . 'requirements.png',
        'icon-active' => $base_path . 'requirements-active.png'
    ],
    [
        'label' => 'History',
        'link'  => 'archive.php',
        'icon'   => $base_path . 'archive.png',
        'icon-active' => $base_path . 'archive-active.png'
    ],
    [
        'label' => 'Settings',
        'link'  => 'settings.php',
        'icon'   => $base_path . 'settings.png',
        'icon-active' => $base_path . 'settings-active.png'
    ],
];
?>
<div id="sidebar" class="w-1/4 h-full bg-white rounded-xl p-10 px-8 dm-sans-semibold text-lg text-[#980000] flex flex-col justify-between border-[0.1px] border-black shadow-xl/20">
    <div class="flex flex-col gap-2">
        <?php foreach ($menu_items as $item): ?>
            <?php
            $isActive = ($currentPage == $item['link']);
            $activeClasses = $isActive ? 'bg-[#940505] text-white' : 'hover:bg-[#940505] hover:text-white';
            $currentIcon = $isActive ? $item['icon-active'] : $item['icon'];
            ?>
            <a href="<?php echo $item['link']; ?>" class="flex items-center gap-3 <?php echo $activeClasses; ?> p-3 pl-5 rounded-lg ease-in-out duration-300">
                <img id="sidebar-icon" class="size-5" src="<?php echo $currentIcon; ?>" alt="">
                <p><?php echo $item['label']; ?></p>
            </a>
        <?php endforeach; ?>
    </div>

    <a href="../auth/logout.php">
        <div class="p-3 pl-5 rounded-lg bg-[#940505] text-white hover:bg-white hover:text-[#980000] border hover:border-black ease-in-out duration-300">Logout</div>
    </a>
</div>
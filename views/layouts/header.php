<?php
// views/layouts/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SsyyCoffee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        coffee: {
                            50: '#FDFBF7',
                            100: '#F4EFE6',
                            200: '#E6DCC8',
                            600: '#8C6239',
                            800: '#5C3A21',
                            950: '#2B1704',
                        }
                    },
                    boxShadow: {
                        'soft': '0 4px 20px -2px rgba(43, 23, 4, 0.03), 0 2px 8px -1px rgba(43, 23, 4, 0.01)',
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #E6DCC8; border-radius: 10px; }
    </style>
</head>
<body class="bg-coffee-50 text-coffee-950 font-sans min-h-screen flex flex-col md:flex-row">

    <!-- TOPBAR KHUSUS MOBILE (Muncul hanya di layar < md) -->
    <div class="md:hidden w-full bg-white p-4 flex items-center justify-between shadow-sm border-b border-coffee-100 z-50 sticky top-0">
        <div class="flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6 text-coffee-950">
                <path d="M4 19V6.2C4 5.09543 4.89543 4 6 4H15C16.1046 4 17 4.89543 17 6.2V19H4ZM6 2H15C17.2091 2 19 3.79086 19 6.2V15C20.6569 15 22 13.6569 22 12C22 10.3431 20.6569 9 19 9V7C21.7614 7 24 9.23858 24 12C24 14.7614 21.7614 17 19 17H17V19C17 20.6569 15.6569 22 14 22H7C5.34315 22 4 20.6569 4 19V21H2V19H0V17H2V6.2C2 3.79086 3.79086 2 6 2Z"/>
            </svg>
            <span class="font-bold text-lg text-coffee-950">SsyyCoffee</span>
        </div>
        
        <!-- Toggle Button Pakai Checkbox (No JS) -->
        <label for="menu-toggle" class="cursor-pointer p-2 rounded-xl hover:bg-coffee-50">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </label>
    </div>

    <!-- Hidden Checkbox untuk Pemicu Menu Mobile -->
    <input type="checkbox" id="menu-toggle" class="peer hidden" />

    <!-- SIDEBAR NAVIGATION (Responsive & Adaptif) -->
    <aside class="fixed inset-y-0 left-0 transform -translate-x-full peer-checked:translate-x-0 md:relative md:translate-x-0 w-64 bg-white min-h-screen flex flex-col justify-between p-6 shadow-soft border-r border-coffee-100 shrink-0 transition-transform duration-300 ease-in-out z-40 md:z-auto">
        <div>
            <!-- Header Logo (Hidden di mobile karena sudah ada di topbar) -->
            <div class="hidden md:flex items-center gap-3 mb-10 px-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-8 h-8 text-coffee-950">
                    <path d="M4 19V6.2C4 5.09543 4.89543 4 6 4H15C16.1046 4 17 4.89543 17 6.2V19H4ZM6 2H15C17.2091 2 19 3.79086 19 6.2V15C20.6569 15 22 13.6569 22 12C22 10.3431 20.6569 9 19 9V7C21.7614 7 24 9.23858 24 12C24 14.7614 21.7614 17 19 17H17V19C17 20.6569 15.6569 22 14 22H7C5.34315 22 4 20.6569 4 19V21H2V19H0V17H2V6.2C2 3.79086 3.79086 2 6 2Z"/>
                </svg>
                <div>
                    <h1 class="text-xl font-bold text-coffee-950 leading-none">SsyyCoffee</h1>
                    <p class="text-[10px] text-coffee-600 font-medium uppercase mt-1">Workspace</p>
                </div>
            </div>

            <!-- Navigasi Utama -->
            <nav class="space-y-2 mt-16 md:mt-0">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 text-sm rounded-xl transition-colors <?= $current_page == 'dashboard.php' ? 'bg-coffee-100 text-coffee-950 font-bold' : 'font-medium hover:bg-coffee-50 text-coffee-800' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" /></svg>
                    <span>Dashboard</span>
                </a>
                
                <a href="menu.php" class="flex items-center gap-3 px-4 py-3 text-sm rounded-xl transition-colors <?= $current_page == 'menu.php' ? 'bg-coffee-100 text-coffee-950 font-bold' : 'font-medium hover:bg-coffee-50 text-coffee-800' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0ZM3.75 12h.007v.008H3.75V12Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm-.375 5.25h.007v.008H3.75v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" /></svg>
                    <span>Manajemen Menu</span>
                </a>
                
                <a href="employees.php" class="flex items-center gap-3 px-4 py-3 text-sm rounded-xl transition-colors <?= $current_page == 'employees.php' ? 'bg-coffee-100 text-coffee-950 font-bold' : 'font-medium hover:bg-coffee-50 text-coffee-800' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    <span>Manajemen Karyawan</span>
                </a>
                
                <a href="reports.php" class="flex items-center gap-3 px-4 py-3 text-sm rounded-xl transition-colors <?= $current_page == 'reports.php' ? 'bg-coffee-100 text-coffee-950 font-bold' : 'font-medium hover:bg-coffee-50 text-coffee-800' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Zm3.75 11.625a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    <span>Laporan Penjualan</span>
                </a>
            </nav>
        </div>

        <!-- Profil & Logout -->
        <div class="border-t border-coffee-100 pt-4 flex items-center justify-between">
            <div class="truncate mr-2 flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-coffee-200 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 text-coffee-800"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </div>
                <div class="truncate">
                    <p class="text-xs font-bold text-coffee-950 truncate"><?= htmlspecialchars($_SESSION['name'] ?? 'Admin'); ?></p>
                    <p class="text-[10px] text-coffee-600 capitalize"><?= htmlspecialchars($_SESSION['role'] ?? 'Staff'); ?></p>
                </div>
            </div>
            <a href="../../logout.php" class="p-2.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-xl transition-colors shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" /></svg>
            </a>
        </div>
    </aside>

    <!-- Overlay untuk menutup menu mobile saat di klik di luar area -->
    <label for="menu-toggle" class="fixed inset-0 bg-stone-900/20 z-30 hidden peer-checked:block md:hidden"></label>

    <!-- MAIN CONTENT WRAPPER -->
    <main class="flex-1 p-4 md:p-10 overflow-y-auto max-h-[calc(100vh-68px)] md:max-h-screen">
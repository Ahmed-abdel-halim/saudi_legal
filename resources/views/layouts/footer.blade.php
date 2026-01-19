<?php
// layout/footer.php - Professional Footer with Standard Fonts

// 1. تحديد اللغة الحالية (افتراضياً العربية)
$current_lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : 'ar';

// 2. مصفوفة الترجمة الداخلية (Fallback Translations)
$footer_translations = [
    'ar' => [
        'PLATFORM_NAME' => 'رديف',
        'HERO_SUBTITLE' => 'منصة تجمع بين أصحاب المشاريع والخبراء المستقلين في بيئة عمل مرنة وآمنة.',
        'NAV_ABOUT' => 'عن رديف',
        'NAV_HOW_IT_WORKS' => 'كيف نعمل',
        'NAV_LEGAL_TITLE' => 'قانوني',
        'NAV_MENU_ABOUT' => 'من نحن',
        'NAV_MENU_CONTACT' => 'اتصل بنا',
        'NAV_MENU_CAREERS' => 'الوظائف',
        'NAV_MENU_BLOG' => 'المدونة',
        'NAV_MENU_HOW_IT_WORKS' => 'كيف نعمل',
        'NAV_MENU_BENEFITS' => 'الفوائد',
        'NAV_MENU_PRICING' => 'الأسعار',
        'NAV_MENU_FAQ' => 'الأسئلة الشائعة',
        'NAV_MENU_TERMS' => 'شروط الخدمة',
        'NAV_MENU_PRIVACY' => 'سياسة الخصوصية',
        'NAV_MENU_MSA' => 'اتفاقية مستوى الخدمة',
        'FOOTER_RIGHTS' => 'جميع الحقوق محفوظة.',
        'FOOTER_SYSTEM_STATUS' => 'جميع الأنظمة تعمل بكفاءة'
    ],
    'en' => [
        'PLATFORM_NAME' => 'Radiif',
        'HERO_SUBTITLE' => 'A platform connecting project owners with independent experts in a flexible and secure environment.',
        'NAV_ABOUT' => 'About Radiif',
        'NAV_HOW_IT_WORKS' => 'How it Works',
        'NAV_LEGAL_TITLE' => 'Legal',
        'NAV_MENU_ABOUT' => 'About Us',
        'NAV_MENU_CONTACT' => 'Contact Us',
        'NAV_MENU_CAREERS' => 'Careers',
        'NAV_MENU_BLOG' => 'Blog',
        'NAV_MENU_HOW_IT_WORKS' => 'How it Works',
        'NAV_MENU_BENEFITS' => 'Benefits',
        'NAV_MENU_PRICING' => 'Pricing',
        'NAV_MENU_FAQ' => 'FAQ',
        'NAV_MENU_TERMS' => 'Terms of Service',
        'NAV_MENU_PRIVACY' => 'Privacy Policy',
        'NAV_MENU_MSA' => 'Service Level Agreement',
        'FOOTER_RIGHTS' => 'All Rights Reserved.',
        'FOOTER_SYSTEM_STATUS' => 'All Systems Operational'
    ]
];

// 3. دمج الترجمات
if (!isset($L)) {
    $L = [];
}
$lang_data = $footer_translations[$current_lang] ?? $footer_translations['ar'];
foreach ($lang_data as $key => $value) {
    if (!isset($L[$key])) {
        $L[$key] = $value;
    }
}
?>

</main>

<!-- تضمين سكريبت Tailwind وإعداداته -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    'brand-primary': '#8e44ad',
                    'brand-secondary': '#2980b9',
                    'brand-accent': '#f39c12',
                    'dark-navy': '#0f172a',
                },
                fontFamily: {
                    'cairo': ['Cairo', 'sans-serif'],
                }
            }
        }
    }
</script>

<!-- الفوتر مع تعديل أحجام الخطوط لتكون طبيعية -->
<footer class="bg-dark-navy text-white pt-12 pb-6 border-t border-white/5 relative overflow-hidden font-cairo" style="margin-top: auto; font-family: 'Cairo', sans-serif;">

    <!-- خلفية جمالية خفيفة -->
    <div class="absolute inset-0 pointer-events-none opacity-5"
        style="background-image: url('https://www.transparenttextures.com/patterns/cubes.png'); background-size: auto;">
    </div>

    <div class="container mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-8 border-b border-white/10 pb-8 mb-8">

            <!-- العمود الأول: الشعار والوصف -->
            <div class="col-span-1 md:col-span-2 lg:col-span-2">
                <a href="/index.php" class="flex items-center gap-3 mb-4 group">
                    <div class="relative">
                        <div class="absolute -inset-1 bg-gradient-to-r from-brand-primary to-brand-secondary rounded-lg blur opacity-30 group-hover:opacity-60 transition duration-500"></div>
                        <img src="/images/icon.png" onerror="this.src='https://placehold.co/40x40/8e44ad/FFFFFF?text=R'" alt="Logo" class="relative h-10 w-auto rounded-lg shadow-lg">
                    </div>
                    <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-300">
                        <?php echo $L['PLATFORM_NAME']; ?>
                    </span>
                </a>
                <!-- استخدام text-sm (حوالي 14px) للوصف ليكون ناعماً وغير مزعج -->
                <p class="text-gray-400 text-sm leading-relaxed max-w-sm mb-6 font-normal">
                    <?php echo $L['HERO_SUBTITLE']; ?>
                </p>

                <div class="flex gap-3">
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 hover:bg-brand-primary flex items-center justify-center transition-all duration-300 text-gray-400 hover:text-white">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                        </svg>
                    </a>
                    <a href="#" class="w-9 h-9 rounded-full bg-white/5 hover:bg-brand-secondary flex items-center justify-center transition-all duration-300 text-gray-400 hover:text-white">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
                        </svg>
                    </a>
                </div>
            </div>

            <!-- الأعمدة: استخدام text-sm (14px) للروابط -->
            <div>
                <h4 class="text-base font-bold mb-4 text-white relative inline-block pb-2">
                    <?php echo $L['NAV_ABOUT']; ?>
                    <span class="absolute bottom-0 right-0 w-1/2 h-0.5 bg-brand-primary rounded-full"></span>
                </h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/about/index.php" class="text-gray-400 hover:text-brand-primary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_ABOUT']; ?></a></li>
                    <li><a href="/about/contact.php" class="text-gray-400 hover:text-brand-primary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_CONTACT']; ?></a></li>
                    <li><a href="/about/careers.php" class="text-gray-400 hover:text-brand-primary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_CAREERS']; ?></a></li>
                    <li><a href="/blog/index.php" class="text-gray-400 hover:text-brand-primary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_BLOG']; ?></a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-base font-bold mb-4 text-white relative inline-block pb-2">
                    <?php echo $L['NAV_HOW_IT_WORKS']; ?>
                    <span class="absolute bottom-0 right-0 w-1/2 h-0.5 bg-brand-secondary rounded-full"></span>
                </h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/how-it-works/index.php" class="text-gray-400 hover:text-brand-secondary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_HOW_IT_WORKS']; ?></a></li>
                    <li><a href="/how-it-works/benefits.php" class="text-gray-400 hover:text-brand-secondary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_BENEFITS']; ?></a></li>
                    <li><a href="/how-it-works/pricing.php" class="text-gray-400 hover:text-brand-secondary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_PRICING']; ?></a></li>
                    <li><a href="/how-it-works/faq.php" class="text-gray-400 hover:text-brand-secondary hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_FAQ']; ?></a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-base font-bold mb-4 text-white relative inline-block pb-2">
                    <?php echo $L['NAV_LEGAL_TITLE']; ?>
                    <span class="absolute bottom-0 right-0 w-1/2 h-0.5 bg-brand-accent rounded-full"></span>
                </h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="/legal/terms.php" class="text-gray-400 hover:text-brand-accent hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_TERMS']; ?></a></li>
                    <li><a href="/legal/privacy.php" class="text-gray-400 hover:text-brand-accent hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_PRIVACY']; ?></a></li>
                    <li><a href="/legal/msa.php" class="text-gray-400 hover:text-brand-accent hover:translate-x-1 transition-all duration-300 block py-1"><?php echo $L['NAV_MENU_MSA']; ?></a></li>
                </ul>
            </div>
        </div>

        <!-- الحقوق -->
        <div class="flex flex-col md:flex-row justify-between items-center pt-4 text-xs text-gray-500 border-t border-white/5">
            <div class="mb-2 md:mb-0 text-center md:text-right">
                &copy; <?php echo date("Y"); ?> <span class="text-white font-semibold"><?php echo $L['PLATFORM_NAME']; ?></span>. <?php echo $L['FOOTER_RIGHTS']; ?>
            </div>
            <div class="flex items-center gap-2 opacity-70 hover:opacity-100 transition">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                <span><?php echo $L['FOOTER_SYSTEM_STATUS']; ?></span>
            </div>
        </div>
    </div>
</footer>

</body>

</html>
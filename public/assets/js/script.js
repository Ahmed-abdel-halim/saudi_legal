let body = document.body;

let profile = document.querySelector('.header .flex .profile');

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   searchForm.classList.remove('active');
}

let searchForm = document.querySelector('.header .flex .search-form');

document.querySelector('#search-btn').onclick = () =>{
   searchForm.classList.toggle('active');
   profile.classList.remove('active');
}

let sideBar = document.querySelector('.side-bar');

document.querySelector('#menu-btn').onclick = () =>{
   sideBar.classList.toggle('active');
   body.classList.toggle('active');
}

document.querySelector('.side-bar .close-side-bar').onclick = () =>{
   sideBar.classList.remove('active');
   body.classList.remove('active');
}

window.onscroll = () =>{
   profile.classList.remove('active');
   searchForm.classList.remove('active');

   if(window.innerWidth < 1200){
      sideBar.classList.remove('active');
      body.classList.remove('active');
   }
}

// --- ULTIMATE SCROLL FIX (الحل النهائي الجذري) ---
// تم تصميم هذا الكود ليكون "عدوانياً" جداً ضد أي كود يمنع التمرير
// حيث يقوم بإعادة فرض التمرير كل 100 جزء من الثانية لمدة قصيرة للتغلب على أي منافسة

(function() {
    function forceEnableScroll() {
        // 1. إجبار تفعيل التمرير العمودي على مستوى الـ CSS
        document.documentElement.style.setProperty('overflow-y', 'auto', 'important');
        document.body.style.setProperty('overflow-y', 'auto', 'important');
        
        // 2. إجبار الطول التلقائي للسماح بالتمدد
        document.documentElement.style.setProperty('height', 'auto', 'important');
        document.body.style.setProperty('height', 'auto', 'important');

        // 3. إزالة أي كلاسات "active" قد تكون عالقة في الـ body
        // (غالباً ما تكون هي السبب لأنها تضيف overflow: hidden)
        if (document.body.classList.contains('active')) {
            document.body.classList.remove('active');
        }
    }

    // التنفيذ الفوري عند تحميل السكربت
    forceEnableScroll();

    // التنفيذ عند جاهزية الـ DOM
    document.addEventListener('DOMContentLoaded', forceEnableScroll);

    // التنفيذ عند اكتمال تحميل الصفحة بالكامل (بما في ذلك الصور)
    window.addEventListener('load', function() {
        forceEnableScroll();
        
        // **الضربة القاضية:** تكرار المحاولة لمدة 3 ثوانٍ
        // هذا يضمن أننا سنلغي تأثير أي كود آخر يعمل بعد التحميل (مثل اللودر)
        var checkCount = 0;
        var scrollInterval = setInterval(function() {
            forceEnableScroll();
            checkCount++;
            if (checkCount > 30) clearInterval(scrollInterval); // التوقف بعد 3 ثوانٍ (30 * 100ms)
        }, 100);
    });
})();
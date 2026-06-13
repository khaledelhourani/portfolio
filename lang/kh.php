<?php

/**
 * Client-side i18n dictionary for LIVE (no-reload) language switching.
 * Injected into the page as window.kh_i18n and read by Alpine's $store.app.t().
 * Kept separate from Laravel's server-side lang/ar.json (used by __()).
 */
return [
    'ar' => [
        'nav.home' => 'الرئيسية',
        'nav.projects' => 'المشاريع',
        'nav.skills' => 'المهارات',
        'nav.services' => 'خدماتي',
        'nav.certificates' => 'شهاداتي',
        'nav.blog' => 'المدونة',
        'nav.ai' => 'مساعد AI',
        'nav.contact' => 'تواصل معي',
        'nav.hire' => 'وظّفني',
        'nav.cms' => 'لوحة التحكم',

        'hero.greeting' => 'أنا خالد الحوراني',
        'hero.available' => 'متاح للعمل الحر',
        'hero.busy' => 'مشغول حالياً',
        'hero.cta_projects' => 'شاهد مشاريعي',
        'hero.cta_ai' => 'اسأل مساعد AI',
        'hero.cta_cv' => 'تحميل السيرة الذاتية',

        'stats.projects' => 'مشروع منجز',
        'stats.clients' => 'عميل راضٍ',
        'stats.hours' => 'ساعة برمجة',
        'stats.countries' => 'دول عملت معها',

        'sections.about' => 'من أنا',
        'sections.projects' => 'المشاريع',
        'sections.skills' => 'مهاراتي التقنية',
        'sections.services' => 'خدماتي',
        'sections.certificates' => 'شهاداتي',
        'sections.blog' => 'المدونة',
        'sections.testimonials' => 'آراء العملاء',
        'sections.contact' => 'تواصل معي',

        'contact.name' => 'الاسم الكريم',
        'contact.email' => 'البريد الإلكتروني',
        'contact.subject' => 'موضوع الرسالة',
        'contact.message' => 'تفاصيل الرسالة',
        'contact.submit' => 'إرسل رسالتك',
        'contact.success' => 'تم إرسال رسالتك بنجاح!',
        'contact.sending' => 'جارٍ الإرسال...',

        'ai.title' => 'بوابة الاستفسار الذاتي — خالد الحوراني AI',
        'ai.subtitle' => 'اسألني عن مهاراتي، مشاريعي، خدماتي',
        'ai.placeholder' => 'أرسل رسالة أو استفسار...',
        'ai.connected' => 'متصل',

        'cms.title' => 'بوابة لوحة القيادة المؤمنة',
        'cms.passcode' => 'رمز المرور الاستراتيجي',

        'common.all_rights' => 'جميع الحقوق محفوظة.',
        'common.view_all' => 'عرض الكل',
        'common.phone' => 'الهاتف',
        'common.location' => 'الموقع',
        'common.all' => 'الكل',

        'cv.work_experience' => 'الخبرات العملية',
        'cv.education' => 'التعليم',
        'cv.certificates' => 'الشهادات',
        'cv.export' => 'تصدير / طباعة السيرة',
        'cv.download' => 'تنزيل (PDF)',
        'cv.print' => 'طباعة الصفحة',
        'cv.current' => 'حالي',
        'cv.empty' => 'لا توجد خبرات مُضافة بعد.',

        'contact.send_another' => 'إرسال رسالة أخرى',
        'projects.empty' => 'لا توجد مشاريع في هذا التصنيف بعد.',

        'services.title' => 'الخدمات',
        'services.subtitle' => 'حلول برمجية متكاملة من التصميم حتى الإطلاق',
        'skills.title' => 'المهارات التقنية',
        'skills.subtitle' => 'الأدوات والتقنيات التي أعمل بها',
        'testimonials.title' => 'آراء العملاء',
        'testimonials.subtitle' => 'ماذا قال من تعاملوا معي',

        'github.title' => 'نشاط GitHub',
        'github.subtitle' => 'آخر ما عملت عليه على GitHub',
        'github.repos' => 'مستودع',
        'github.followers' => 'متابع',
        'github.commits' => 'دفعة',
        'github.view' => 'عرض الملف',
        'online.now' => 'متصل الآن',
    ],

    'en' => [
        'nav.home' => 'Home',
        'nav.projects' => 'Projects',
        'nav.skills' => 'Skills',
        'nav.services' => 'Services',
        'nav.certificates' => 'Certificates',
        'nav.blog' => 'Blog',
        'nav.ai' => 'AI Assistant',
        'nav.contact' => 'Contact',
        'nav.hire' => 'Hire Me',
        'nav.cms' => 'Control Panel',

        'hero.greeting' => "I'm Khaled Al-Hourani",
        'hero.available' => 'Available for Freelance',
        'hero.busy' => 'Currently Busy',
        'hero.cta_projects' => 'View My Projects',
        'hero.cta_ai' => 'Ask AI Assistant',
        'hero.cta_cv' => 'Download CV',

        'stats.projects' => 'Projects Done',
        'stats.clients' => 'Happy Clients',
        'stats.hours' => 'Coding Hours',
        'stats.countries' => 'Countries Worked With',

        'sections.about' => 'About Me',
        'sections.projects' => 'Projects',
        'sections.skills' => 'Technical Skills',
        'sections.services' => 'My Services',
        'sections.certificates' => 'Certificates',
        'sections.blog' => 'Blog',
        'sections.testimonials' => 'Testimonials',
        'sections.contact' => 'Contact Me',

        'contact.name' => 'Full Name',
        'contact.email' => 'Email Address',
        'contact.subject' => 'Subject',
        'contact.message' => 'Message Details',
        'contact.submit' => 'Send Message',
        'contact.success' => 'Message sent successfully!',
        'contact.sending' => 'Sending...',

        'ai.title' => 'Self-Inquiry Portal — Khaled Al-Hourani AI',
        'ai.subtitle' => 'Ask me about my skills, projects, and services',
        'ai.placeholder' => 'Send a message or inquiry...',
        'ai.connected' => 'Connected',

        'cms.title' => 'Secured Control Panel Gateway',
        'cms.passcode' => 'Strategic Passcode',

        'common.all_rights' => 'All rights reserved.',
        'common.view_all' => 'View all',
        'common.phone' => 'Phone',
        'common.location' => 'Location',
        'common.all' => 'All',

        'cv.work_experience' => 'Work Experience',
        'cv.education' => 'Education',
        'cv.certificates' => 'Certificates',
        'cv.export' => 'Export / Print CV',
        'cv.download' => 'Download (PDF)',
        'cv.print' => 'Print page',
        'cv.current' => 'Current',
        'cv.empty' => 'No experience added yet.',

        'contact.send_another' => 'Send another message',
        'projects.empty' => 'No projects in this category yet.',

        'services.title' => 'Services',
        'services.subtitle' => 'End-to-end software solutions, from design to launch',
        'skills.title' => 'Technical Skills',
        'skills.subtitle' => 'The tools and technologies I work with',
        'testimonials.title' => 'Testimonials',
        'testimonials.subtitle' => 'What people I worked with had to say',

        'github.title' => 'GitHub Activity',
        'github.subtitle' => 'What I have been building lately on GitHub',
        'github.repos' => 'repos',
        'github.followers' => 'followers',
        'github.commits' => 'commits',
        'github.view' => 'View profile',
        'online.now' => 'online now',
    ],
];

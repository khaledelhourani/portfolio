<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Education;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Setting;
use App\Models\WorkExperience;
use Illuminate\Database\Seeder;

/**
 * Loads Khalid's REAL profile/bio, the AI assistant instructions, and his
 * actual projects — replacing the Phase 1 placeholder data. Work experience,
 * education and certificates are cleared (no real data provided yet) so the
 * assistant never fabricates credentials.
 */
class OwnerDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Profile — keep brand name + contact, refresh bio & role.
        $profile = Profile::current();
        $profile->update([
            'role_ar' => 'مطور ويب متكامل',
            'role_en' => 'Full-Stack Web Developer',
            'bio_ar' => 'مطوّر ويب مهتم بالتقنيات الحديثة والذكاء الاصطناعي. أطوّر مواقع الويب والمتاجر الإلكترونية ولوحات التحكم باستخدام PHP وLaravel، مع تركيز على التصميم الحديث وتجربة المستخدم والأداء والحلول العملية القابلة للتوسّع.',
            'bio_en' => 'A web developer passionate about modern technologies and AI. I build websites, e-commerce stores and admin dashboards with PHP and Laravel, focusing on clean modern design, user experience, performance and scalable, practical solutions.',
            'credential_badge_ar' => 'متاح لمشاريع جديدة',
            'credential_badge_en' => 'Available for new projects',
        ]);

        // 2) AI assistant system prompt (behavioural + identity instructions).
        Setting::set('ai_system_prompt', $this->systemPrompt(), 'text', 'ai');

        // 3) Real projects — replace placeholders.
        Project::query()->delete();

        $projects = [
            [
                'title_ar' => 'نظام إدارة المنتجات',
                'title_en' => 'Product Management System',
                'type' => 'Web App',
                'duration' => 'مشروع',
                'description_ar' => 'نظام متكامل لإدارة المنتجات مبني بـ PHP وMySQL: إضافة وتعديل وحذف وعرض المنتجات، مع نظام لإدارة ألوان المنتجات وصورها.',
                'description_en' => 'A complete product management system built with PHP and MySQL: full product CRUD plus a module to manage product colors and images.',
                'tech_stack' => ['PHP', 'MySQL', 'HTML', 'CSS', 'JavaScript'],
                'core_focus' => 'عمليات CRUD كاملة للمنتجات مع إدارة الألوان والصور.',
                'architecture' => 'طبقة بيانات MySQL مع واجهات إدارة ديناميكية.',
                'mitigation' => 'التحقق من المدخلات ومعالجة رفع الصور بأمان.',
                'featured' => true,
                'status' => 'published',
                'sort_order' => 1,
            ],
            [
                'title_ar' => 'متجر إلكتروني للملابس',
                'title_en' => 'Clothing E-commerce Store',
                'type' => 'E-commerce',
                'duration' => 'مشروع',
                'description_ar' => 'متجر إلكتروني لبيع الملابس مع عرض المنتجات بألوان ومقاسات، وإدارة كاملة للمنتجات والطلبات من لوحة تحكم.',
                'description_en' => 'An online clothing store with products displayed by colors and sizes, and full product/order management from an admin dashboard.',
                'tech_stack' => ['Laravel', 'PHP', 'MySQL', 'Tailwind CSS', 'JavaScript'],
                'core_focus' => 'تجربة تسوّق سلسة مع إدارة المنتجات بالألوان والمقاسات.',
                'architecture' => 'Laravel MVC مع قاعدة بيانات MySQL ولوحة تحكم للإدارة.',
                'mitigation' => 'حماية النماذج، التحقق من المخزون، وتنظيم الطلبات.',
                'featured' => true,
                'status' => 'published',
                'sort_order' => 2,
            ],
            [
                'title_ar' => 'لوحات تحكم مخصّصة',
                'title_en' => 'Custom Admin Dashboards',
                'type' => 'Full-Stack',
                'duration' => 'مشاريع متعددة',
                'description_ar' => 'تطوير لوحات تحكم حديثة وسهلة الاستخدام لإدارة المحتوى والمنتجات، مع تركيز على الأداء وقابلية التوسّع.',
                'description_en' => 'Building modern, easy-to-use admin dashboards for managing content and products, focused on performance and scalability.',
                'tech_stack' => ['Laravel', 'PHP', 'MySQL', 'Alpine.js', 'Tailwind CSS'],
                'core_focus' => 'إدارة محتوى ومنتجات بواجهات حديثة وسريعة.',
                'architecture' => 'Laravel مع مكوّنات Blade وAlpine.js وتصميم متجاوب.',
                'mitigation' => 'صلاحيات وصول واضحة وتحقق من البيانات.',
                'featured' => false,
                'status' => 'published',
                'sort_order' => 3,
            ],
        ];

        foreach ($projects as $data) {
            $data['slug'] = \Illuminate\Support\Str::slug($data['title_en']);
            Project::create($data);
        }

        // 4) Clear placeholder CV data (no real data supplied yet).
        WorkExperience::query()->delete();
        Education::query()->delete();
        Certificate::query()->delete();
    }

    private function systemPrompt(): string
    {
        return <<<'PROMPT'
        أنت "مساعد خالد" الشخصي على الموقع الرسمي لخالد الحوراني — مطوّر ويب متكامل.

        # دورك
        - تجيب على أسئلة الزوّار والعملاء حول خدمات خالد التقنية.
        - تشرح الخدمات وتقترح حلولاً مناسبة للمشاريع.
        - تجمع متطلبات العميل أولاً قبل إعطاء أي تقدير للسعر.
        - أسلوبك احترافي وودود ومباشر، تركّز على النتائج والتفاصيل التقنية.
        - أجب دائماً بنفس لغة العميل (عربي أو إنجليزي)، وباختصار ووضوح.

        # عن خالد
        مطوّر ويب متكامل مهتم بالتقنيات الحديثة والذكاء الاصطناعي. يطوّر مواقع الويب والمتاجر الإلكترونية ولوحات التحكم باستخدام PHP وLaravel، ويهتم بالتصميم الحديث وتجربة المستخدم والأداء وقابلية التوسّع والأتمتة.
        التخصصات: تطوير مواقع الويب، المتاجر الإلكترونية، لوحات التحكم، PHP، Laravel، إدارة قواعد بيانات MySQL، دمج أدوات الذكاء الاصطناعي، تحسين تجربة المستخدم.
        المهارات التقنية: PHP, Laravel, MySQL, HTML, CSS, JavaScript, REST APIs, Git, Responsive Design, Dashboard Development, E-commerce Development.

        # عند الاستفسار عن مشروع، اسأل عن:
        نوع المشروع، الهدف منه، عدد الصفحات، وجود لوحة تحكم، نظام مستخدمين، دفع إلكتروني، موعد التسليم، والميزانية التقريبية.

        # عند الاستفسار عن متجر إلكتروني، اسأل عن:
        عدد المنتجات، طرق الدفع، طرق الشحن، وجود ألوان ومقاسات، الحاجة للوحة تحكم، الحاجة لتطبيق جوال مستقبلاً.

        # عند الاستفسار عن موقع شركة، اسأل عن:
        مجال الشركة، عدد الصفحات، اللغات المطلوبة، نموذج التواصل، نظام الحجز أو الطلبات إن وجد.

        # عند اقتراح الحلول
        - ركّز على Laravel وPHP.
        - اقترح MySQL للمشاريع المناسبة.
        - اقترح لوحات تحكم حديثة وسهلة الاستخدام.
        - راعِ الأداء وتجربة المستخدم وقابلية التوسّع مستقبلاً.

        # ممنوع تماماً
        - لا تخترع معلومات غير موجودة.
        - لا تَعِد العميل بميزات أو أسعار غير مؤكدة.
        - لا تدّعِ وجود شهادات أو خبرات غير مذكورة في البيانات.
        - لا تكشف أي معلومات خاصة أو حساسة.
        PROMPT;
    }
}

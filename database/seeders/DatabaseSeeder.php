<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\Certificate;
use App\Models\Education;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Setting;
use App\Models\Tag;
use App\Models\User;
use App\Models\WorkExperience;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedSuperAdmin();
        $this->seedProfile();
        $this->seedProjects();
        $this->seedBlog();
        $this->seedCv();
        $this->seedSettings();
    }

    private function seedSuperAdmin(): void
    {
        User::updateOrCreate(
            ['email' => 'khaledsameer2662@gmail.com'],
            [
                'name' => 'خالد الحوراني',
                'password' => Hash::make('password'), // change after first login
                'role' => 'super_admin',
                'is_super_admin' => true,
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
    }

    private function seedProfile(): void
    {
        Profile::updateOrCreate(['id' => 1], [
            'name_ar' => 'خالد الحوراني',
            'name_en' => 'Khaled Al-Hourani',
            'role_ar' => 'مطور ويب متكامل',
            'role_en' => 'Full-Stack Web Developer',
            'bio_ar' => 'مطور ويب متكامل متخصص في بناء تطبيقات Laravel و PHP الحديثة، مع شغف بالواجهات النظيفة والأنظمة القابلة للتوسّع. أحوّل الأفكار المعقّدة إلى منتجات رقمية أنيقة وآمنة.',
            'bio_en' => 'Full-stack web developer specialised in modern Laravel & PHP applications, with a passion for clean interfaces and scalable systems.',
            'credential_badge_ar' => 'متاح لمشاريع جديدة',
            'credential_badge_en' => 'Available for new projects',
            'city' => 'عمّان، الأردن',
            'email' => 'khaledsameer2662@gmail.com',
            'phone' => '+962 7XX XXX XXX',
            'social_links' => [
                'github' => 'https://github.com/',
                'instagram' => 'https://instagram.com/khaled._.sameer',
                'facebook' => 'https://facebook.com/',
                'linkedin' => 'https://linkedin.com/',
            ],
        ]);
    }

    private function seedProjects(): void
    {
        $categories = [
            ['name_ar' => 'تطبيقات ويب', 'name_en' => 'Web Apps', 'slug' => 'web-apps', 'sort_order' => 1],
            ['name_ar' => 'واجهات برمجية', 'name_en' => 'APIs', 'slug' => 'apis', 'sort_order' => 2],
            ['name_ar' => 'أنظمة إدارة', 'name_en' => 'Dashboards', 'slug' => 'dashboards', 'sort_order' => 3],
        ];

        foreach ($categories as $cat) {
            ProjectCategory::updateOrCreate(['slug' => $cat['slug']], $cat);
        }

        $webApps = ProjectCategory::where('slug', 'web-apps')->first();
        $dashboards = ProjectCategory::where('slug', 'dashboards')->first();

        $projects = [
            [
                'project_category_id' => $dashboards->id,
                'title_ar' => 'لوحة تحكم البورتفوليو',
                'title_en' => 'Portfolio Control Panel',
                'type' => 'Full-Stack',
                'duration' => '6 أسابيع',
                'description_ar' => 'منصة بورتفوليو شخصية مع لوحة تحكم ذاتية الاستضافة، تتبّع زوّار لحظي، ومساعد ذكاء اصطناعي.',
                'description_en' => 'Personal portfolio with a self-hosted control panel, real-time visitor tracking, and an AI assistant.',
                'tech_stack' => ['Laravel 11', 'Alpine.js', 'Tailwind CSS', 'MySQL', 'Reverb'],
                'github_url' => 'https://github.com/',
                'core_focus' => 'تجربة مستخدم مستوحاة من Claude.ai مع دعم كامل للعربية RTL.',
                'architecture' => 'معمارية MVC نظيفة مع طبقة خدمات، بث لحظي عبر WebSockets، وتخزين مشفّر للإعدادات الحسّاسة.',
                'mitigation' => 'تحديد معدّل الطلبات، حماية CSRF، وفحص الملفات المرفوعة قبل المعالجة.',
                'featured' => true,
                'status' => 'published',
                'sort_order' => 1,
            ],
            [
                'project_category_id' => $webApps->id,
                'title_ar' => 'متجر إلكتروني متكامل',
                'title_en' => 'E-Commerce Platform',
                'type' => 'Web App',
                'duration' => '3 أشهر',
                'description_ar' => 'متجر إلكتروني كامل مع سلة شراء، بوابات دفع، ولوحة إدارة للمنتجات والطلبات.',
                'description_en' => 'Full e-commerce store with cart, payment gateways, and an admin panel.',
                'tech_stack' => ['Laravel', 'Vue.js', 'Stripe', 'Redis'],
                'github_url' => 'https://github.com/',
                'core_focus' => 'تدفّق شراء سلس وأداء عالٍ تحت الضغط.',
                'architecture' => 'فصل الواجهة عن الخلفية عبر API، مع طوابير لمعالجة الطلبات.',
                'mitigation' => 'التحقّق من المخزون بشكل ذرّي ومنع الطلبات المكرّرة.',
                'featured' => true,
                'status' => 'published',
                'sort_order' => 2,
            ],
            [
                'project_category_id' => $webApps->id,
                'title_ar' => 'نظام حجز المواعيد',
                'title_en' => 'Appointment Booking System',
                'type' => 'SaaS',
                'duration' => 'شهرين',
                'description_ar' => 'نظام حجز مواعيد متعدّد المستأجرين مع تقويم وإشعارات بريدية.',
                'description_en' => 'Multi-tenant appointment booking with calendar and email notifications.',
                'tech_stack' => ['Laravel', 'Livewire', 'Tailwind CSS'],
                'github_url' => 'https://github.com/',
                'core_focus' => 'إدارة المواعيد بدون تعارض زمني.',
                'architecture' => 'عزل بيانات كل مستأجر مع جدولة مهام دورية.',
                'mitigation' => 'أقفال متفائلة لمنع الحجز المزدوج.',
                'featured' => false,
                'status' => 'published',
                'sort_order' => 3,
            ],
        ];

        foreach ($projects as $p) {
            $p['slug'] = Str::slug($p['title_en']);
            Project::updateOrCreate(['slug' => $p['slug']], $p);
        }
    }

    private function seedBlog(): void
    {
        $category = BlogCategory::updateOrCreate(
            ['slug' => 'laravel'],
            ['name' => 'Laravel'],
        );

        $tagNames = ['Laravel', 'PHP', 'Tailwind', 'الذكاء الاصطناعي'];
        $tags = collect($tagNames)->map(fn ($name) => Tag::updateOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name],
        ));

        $admin = User::where('is_super_admin', true)->first();

        $posts = [
            [
                'title' => 'كيف بنيت لوحة تحكم لحظية باستخدام Laravel Reverb',
                'excerpt' => 'رحلة بناء نظام إشعارات لحظي للزوّار باستخدام WebSockets المستضافة ذاتياً.',
                'content' => "# مقدمة\n\nفي هذه التدوينة أشارك كيف استخدمت **Laravel Reverb** لبناء نظام تتبّع زوّار لحظي بالكامل.\n\n## لماذا Reverb؟\n\n- مجاني ومستضاف ذاتياً\n- تكامل سلس مع Laravel Echo\n- أداء ممتاز\n\nالنتيجة كانت لوحة تحكم تعرض كل زائر فور دخوله الموقع.",
                'status' => 'published',
                'published_at' => Carbon::now()->subDays(3),
            ],
            [
                'title' => 'دعم RTL الكامل في تطبيقات Tailwind CSS',
                'excerpt' => 'أفضل الممارسات لبناء واجهات عربية ثنائية الاتجاه بدون ألم.',
                'content' => "# دعم RTL في Tailwind\n\nالدعم الكامل للعربية يتطلّب أكثر من مجرد `dir=\"rtl\"`.\n\n## النقاط الأساسية\n\n1. استخدم الخصائص المنطقية (logical properties)\n2. اعتمد على متغيّرات `ms-*` و `me-*`\n3. اختبر على كل نقاط الكسر",
                'status' => 'published',
                'published_at' => Carbon::now()->subDay(),
            ],
        ];

        foreach ($posts as $p) {
            $p['slug'] = Str::slug($p['title']);
            $p['blog_category_id'] = $category->id;
            $p['user_id'] = $admin?->id;
            $p['reading_time'] = max(1, (int) ceil(mb_strlen(strip_tags($p['content'])) / 600));

            $post = BlogPost::updateOrCreate(['slug' => $p['slug']], $p);
            $post->tags()->sync($tags->random(2)->pluck('id')->all());
        }
    }

    private function seedCv(): void
    {
        $work = [
            [
                'role' => 'مطور ويب متكامل',
                'company' => 'العمل الحر',
                'location' => 'عن بُعد',
                'start_date' => '2022-01-01',
                'end_date' => null,
                'is_current' => true,
                'bullets' => [
                    'تطوير وصيانة تطبيقات Laravel لعملاء متعدّدين',
                    'بناء واجهات حديثة باستخدام Tailwind و Alpine.js',
                    'تصميم قواعد بيانات وتحسين الاستعلامات',
                ],
                'badge' => 'حالي',
                'sort_order' => 1,
            ],
            [
                'role' => 'مطور خلفية (Backend)',
                'company' => 'شركة تقنية',
                'location' => 'عمّان',
                'start_date' => '2020-06-01',
                'end_date' => '2021-12-31',
                'is_current' => false,
                'bullets' => [
                    'تطوير واجهات برمجية RESTful',
                    'التكامل مع بوابات الدفع وخدمات الطرف الثالث',
                ],
                'badge' => null,
                'sort_order' => 2,
            ],
        ];

        foreach ($work as $w) {
            WorkExperience::updateOrCreate(
                ['role' => $w['role'], 'company' => $w['company']],
                $w,
            );
        }

        Education::updateOrCreate(
            ['degree' => 'بكالوريوس علوم الحاسوب', 'institution' => 'الجامعة الأردنية'],
            ['start_year' => '2016', 'end_year' => '2020', 'description' => 'تخصّص هندسة برمجيات.', 'sort_order' => 1],
        );

        $certs = [
            ['title' => 'Laravel Certified Developer', 'issuer' => 'Laravel', 'issue_date' => '2023-05-01', 'sort_order' => 1],
            ['title' => 'Meta Front-End Developer', 'issuer' => 'Coursera', 'issue_date' => '2022-09-01', 'sort_order' => 2],
        ];

        foreach ($certs as $c) {
            Certificate::updateOrCreate(['title' => $c['title']], $c);
        }
    }

    private function seedSettings(): void
    {
        Setting::set('site_title', 'خالد الحوراني — مطور ويب', 'string', 'general');
        Setting::set('site_description', 'بورتفوليو شخصي ولوحة تحكم ذاتية الاستضافة.', 'text', 'seo');
        Setting::set('contact_email', 'khaledsameer2662@gmail.com', 'string', 'general');
        Setting::set('maintenance_mode', false, 'bool', 'general');
        Setting::set('ai_assistant_enabled', true, 'bool', 'ai');
        Setting::set('ai_system_prompt', 'أنت مساعد خالد الحوراني الذكي. أجب باختصار وبنفس لغة السؤال (عربي أو إنجليزي) معتمداً على بيانات السيرة والمشاريع.', 'text', 'ai');
    }
}

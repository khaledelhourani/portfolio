<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Seeds the Premium Chunk C content blocks (Services, Skills, Testimonials)
 * with Khaled Al-Hourani's real stack. Idempotent: clears each table first.
 */
class PremiumContentSeeder extends Seeder
{
    public function run(): void
    {
        Service::query()->delete();
        Skill::query()->delete();
        Testimonial::query()->delete();

        $services = [
            ['title_ar' => 'تطوير المواقع والأنظمة', 'title_en' => 'Web & Systems Development', 'description_ar' => 'بناء تطبيقات ويب متكاملة بـ Laravel وقواعد بيانات MySQL، من لوحات التحكم إلى الأنظمة الإدارية.', 'description_en' => 'Full-stack web apps with Laravel and MySQL — from dashboards to complete admin systems.', 'icon' => 'code', 'featured' => true, 'sort_order' => 1],
            ['title_ar' => 'متاجر إلكترونية', 'title_en' => 'E-commerce Stores', 'description_ar' => 'متاجر إلكترونية كاملة مع إدارة المنتجات والطلبات وبوابات الدفع.', 'description_en' => 'Complete online stores with product/order management and payment gateways.', 'icon' => 'web', 'featured' => false, 'sort_order' => 2],
            ['title_ar' => 'لوحات تحكم مخصّصة', 'title_en' => 'Custom Admin Dashboards', 'description_ar' => 'لوحات تحكم مصمّمة حسب احتياج عملك مع تقارير وتحليلات حيّة.', 'description_en' => 'Tailored admin dashboards with live reports and analytics.', 'icon' => 'server', 'featured' => false, 'sort_order' => 3],
            ['title_ar' => 'واجهات متجاوبة', 'title_en' => 'Responsive Interfaces', 'description_ar' => 'واجهات أنيقة وسريعة تعمل على جميع الأجهزة بدعم كامل للغة العربية.', 'description_en' => 'Elegant, fast interfaces that work on every device with full RTL support.', 'icon' => 'design', 'featured' => false, 'sort_order' => 4],
        ];
        foreach ($services as $s) {
            Service::create($s);
        }

        $skills = [
            ['name' => 'HTML', 'category' => 'frontend', 'level' => 95, 'sort_order' => 1],
            ['name' => 'CSS / Tailwind', 'category' => 'frontend', 'level' => 90, 'sort_order' => 2],
            ['name' => 'JavaScript', 'category' => 'frontend', 'level' => 85, 'sort_order' => 3],
            ['name' => 'Responsive Design', 'category' => 'frontend', 'level' => 90, 'sort_order' => 4],
            ['name' => 'PHP', 'category' => 'backend', 'level' => 92, 'sort_order' => 1],
            ['name' => 'Laravel', 'category' => 'backend', 'level' => 90, 'sort_order' => 2],
            ['name' => 'REST APIs', 'category' => 'backend', 'level' => 85, 'sort_order' => 3],
            ['name' => 'MySQL', 'category' => 'database', 'level' => 88, 'sort_order' => 1],
            ['name' => 'Git', 'category' => 'tools', 'level' => 85, 'sort_order' => 1],
        ];
        foreach ($skills as $s) {
            Skill::create($s);
        }

        // No real client testimonials provided yet — left empty so nothing
        // is fabricated. Khaled can add them via the admin manager.
    }
}

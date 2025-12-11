<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create site_settings table for frontend configuration
        Schema::create('site_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            // Info Sekolah
            $table->string('nama_sekolah')->default('MAN 1 Kota Semarang');
            $table->string('slogan')->nullable();
            $table->text('alamat')->nullable();
            $table->string('telepon', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            
            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('facebook_page_id')->nullable();
            $table->string('facebook_access_token')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('tiktok_url')->nullable();
            $table->string('whatsapp_number')->nullable();
            
            // Landing Page Content
            $table->string('hero_title')->nullable();
            $table->text('hero_subtitle')->nullable();
            $table->string('hero_image')->nullable();
            $table->text('about_content')->nullable();
            $table->string('about_image')->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Colors & Theme
            $table->string('primary_color', 10)->default('#007bff');
            $table->string('secondary_color', 10)->default('#6c757d');
            
            // Footer
            $table->text('footer_text')->nullable();
            $table->string('copyright_text')->nullable();
            
            // Google Maps
            $table->text('google_maps_embed')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            
            $table->timestamps();
        });
        
        // Create jadwal_ppdb table
        Schema::create('jadwal_ppdb', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_kegiatan');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();
            $table->text('keterangan')->nullable();
            $table->string('warna', 10)->default('#007bff');
            $table->integer('urutan')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        // Add more fields to beritas table
        if (Schema::hasTable('beritas')) {
            Schema::table('beritas', function (Blueprint $table) {
                if (!Schema::hasColumn('beritas', 'kategori')) {
                    $table->string('kategori', 50)->default('umum')->after('slug');
                }
                if (!Schema::hasColumn('beritas', 'penulis')) {
                    $table->string('penulis')->nullable()->after('konten');
                }
                if (!Schema::hasColumn('beritas', 'views')) {
                    $table->integer('views')->default(0)->after('gambar');
                }
                if (!Schema::hasColumn('beritas', 'is_featured')) {
                    $table->boolean('is_featured')->default(false)->after('views');
                }
                if (!Schema::hasColumn('beritas', 'shared_to_facebook')) {
                    $table->boolean('shared_to_facebook')->default(false)->after('is_featured');
                }
                if (!Schema::hasColumn('beritas', 'facebook_post_id')) {
                    $table->string('facebook_post_id')->nullable()->after('shared_to_facebook');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_ppdb');
        Schema::dropIfExists('site_settings');
        
        if (Schema::hasTable('beritas')) {
            Schema::table('beritas', function (Blueprint $table) {
                $columns = ['kategori', 'penulis', 'views', 'is_featured', 'shared_to_facebook', 'facebook_post_id'];
                foreach ($columns as $col) {
                    if (Schema::hasColumn('beritas', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

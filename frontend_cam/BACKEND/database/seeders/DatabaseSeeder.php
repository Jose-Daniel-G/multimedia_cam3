<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        // Storage::deleteDirectory('posts');
        // Storage::makeDirectory('posts');
        $this->call([RoleSeeder::class, organismoseeder::class,
                     UserSeeder::class, TipoPlantillaSeeder::class, TipoImpuestoSeeder::class,                    
                     TipoActoTramiteSeeder::class,TipoCausaDevolucionSeeder::class]);
                    //  TagSeeder::class,
                    
        // Category::factory(4)->create();
        // Tag::factory(8)->create();
    }
}

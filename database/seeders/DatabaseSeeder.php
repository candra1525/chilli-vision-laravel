<?php

namespace Database\Seeders;

use App\Models\Subscriptions;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'id' => Str::uuid(),
            'fullname' => 'Candra',
            'no_handphone' => '081234567890',
            'password' => bcrypt('root'),
            'role' => 'admin',
        ]);

        $packages = [
            [
                'title' => 'Paket Reguler',
                'price' => 500000,
                'image' => 'subscriptions_1745744090.png',
                'description' => '1.	Perangkat lunak dapat mendeteksi jenis penyakit pada tanaman cabai dengan maksimal request 100 kali per hari.
2.	Pengguna dapat berinteraksi dengan Chat AI untuk melakukan tanya jawab seputar penyakit tanaman cabai dengan maksimal penggunaan 10 kali perhari.
3.	Pengguna dapat menyimpan riwayat deteksi dengan maksimal  50 riwayat.
4.	Berlaku dalam rentang waktu 6 bulan sejak pembelian paket.',
                'period' => 6,
            ],
            [
                'title' => 'Paket Premium',
                'price' => 1000000,
                'image' => 'subscriptions_1745744129.png',
                'description' => '1.	Perangkat lunak dapat mendeteksi jenis penyakit pada tanaman cabai dengan maksimal request 500 kali per hari.
2.	Pengguna dapat berinteraksi dengan Chat AI untuk melakukan tanya jawab seputar penyakit tanaman cabai dengan maksimal penggunaan 100 kali per hari.
3.	Pengguna dapat menyimpan riwayat deteksi dengan maksimal 200 riwayat.
4.	Berlaku dalam rentang waktu 12 bulan sejak pembelian paket.',
                'period' => 12,
            ]
        ];

        foreach ($packages as $package) {
            Subscriptions::create([
                'id' => Str::uuid(),
                'title' => $package['title'],
                'price' => $package['price'],
                'image_subscriptions' => $package['image'],
                'description' => $package['description'],
                'period' => $package['period'],
            ]);
        }
    }
}

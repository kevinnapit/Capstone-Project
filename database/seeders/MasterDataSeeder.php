<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        ProductCategory::query()->upsert([
            ['name' => 'Alat Tulis Kantor', 'description' => 'Barang ATK yang dijual kepada pelanggan', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Bahan Fotokopi', 'description' => 'Bahan habis pakai untuk layanan fotokopi dan cetak', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ], ['name'], ['description', 'updated_at']);

        Unit::query()->upsert([
            ['name' => 'Buah', 'symbol' => 'pcs', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Rim', 'symbol' => 'rim', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Lembar', 'symbol' => 'lbr', 'created_at' => $timestamp, 'updated_at' => $timestamp],
            ['name' => 'Kotak', 'symbol' => 'box', 'created_at' => $timestamp, 'updated_at' => $timestamp],
        ], ['symbol'], ['name', 'updated_at']);
    }
}

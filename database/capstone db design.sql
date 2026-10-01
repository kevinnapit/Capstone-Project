CREATE TABLE `roles` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(50) UNIQUE NOT NULL,
  `description` varchar(255),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `users` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` ENUM ('active', 'inactive') NOT NULL DEFAULT 'active',
  `email_verified_at` timestamp,
  `remember_token` varchar(100),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `role_user` (
  `user_id` bigint NOT NULL,
  `role_id` bigint NOT NULL,
  `created_at` timestamp,
  PRIMARY KEY (`user_id`, `role_id

CREATE TABLE `password_reset_tokens` (
  `email` varchar(150) PRIMARY KEY,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp
);

CREATE TABLE `product_categories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(100) UNIQUE NOT NULL,
  `description` varchar(255),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `units` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `symbol` varchar(20) UNIQUE NOT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `products` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `category_id` bigint NOT NULL,
  `unit_id` bigint NOT NULL,
  `sku` varchar(50) UNIQUE NOT NULL,
  `name` varchar(150) NOT NULL,
  `purchase_price` decimal(15,2) NOT NULL DEFAULT 0,
  `selling_price` decimal(15,2) NOT NULL DEFAULT 0,
  `current_stock` decimal(15,2) NOT NULL DEFAULT 0,
  `minimum_stock` decimal(15,2) NOT NULL DEFAULT 0,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `paper_types` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `inventory_product_id` bigint UNIQUE NOT NULL,
  `code` varchar(20) UNIQUE NOT NULL,
  `name` varchar(50) NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `service_types` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(30) UNIQUE NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `print_modes` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(30) UNIQUE NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `service_prices` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `service_type_id` bigint NOT NULL,
  `paper_type_id` bigint NOT NULL,
  `print_mode_id` bigint,
  `side_mode` ENUM ('none', 'single_sided', 'duplex') NOT NULL DEFAULT 'none',
  `price` decimal(15,2) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_until` date,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `customers` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `phone` varchar(30),
  `address` text,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `order_channels` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(30) UNIQUE NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `payment_methods` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `code` varchar(30) UNIQUE NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` boolean NOT NULL DEFAULT true,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `orders` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_number` varchar(50) UNIQUE NOT NULL,
  `customer_id` bigint,
  `channel_id` bigint NOT NULL,
  `created_by` bigint NOT NULL,
  `status` ENUM ('draft', 'confirmed', 'processing', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
  `ordered_at` datetime NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0,
  `discount_amount` decimal(15,2) NOT NULL DEFAULT 0,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0,
  `paid_amount` decimal(15,2) NOT NULL DEFAULT 0,
  `notes` text,
  `completed_at` datetime,
  `cancelled_at` datetime,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `order_items` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `type` ENUM ('product', 'service') NOT NULL,
  `product_id` bigint,
  `service_price_id` bigint,
  `item_name_snapshot` varchar(150) NOT NULL,
  `quantity_billed` decimal(15,2) NOT NULL,
  `unit_price_snapshot` decimal(15,2) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `notes` varchar(500),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `service_item_details` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_item_id` bigint UNIQUE NOT NULL,
  `paper_type_id` bigint NOT NULL,
  `pages` int NOT NULL DEFAULT 1,
  `copies` int NOT NULL DEFAULT 1,
  `sheets_billed` int NOT NULL DEFAULT 0,
  `sheets_consumed` int NOT NULL DEFAULT 0,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `order_status_histories` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `from_status` ENUM ('draft', 'confirmed', 'processing', 'completed', 'cancelled'),
  `to_status` ENUM ('draft', 'confirmed', 'processing', 'completed', 'cancelled') NOT NULL,
  `changed_by` bigint NOT NULL,
  `reason` varchar(500),
  `created_at` timestamp NOT NULL
);

CREATE TABLE `order_adjustments` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `type` ENUM ('revision', 'cancellation') NOT NULL,
  `reason` text NOT NULL,
  `charge_amount` decimal(15,2) NOT NULL DEFAULT 0,
  `created_by` bigint NOT NULL,
  `created_at` timestamp NOT NULL
);

CREATE TABLE `payments` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `order_id` bigint NOT NULL,
  `payment_method_id` bigint NOT NULL,
  `received_by` bigint NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` ENUM ('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'paid',
  `reference_number` varchar(100),
  `paid_at` datetime NOT NULL,
  `notes` varchar(500),
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE TABLE `stock_movements` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `product_id` bigint NOT NULL,
  `movement_type` ENUM ('purchase', 'sale', 'service_usage', 'rework', 'waste', 'adjustment', 'opname') NOT NULL,
  `direction` ENUM ('in', 'out') NOT NULL,
  `quantity` decimal(15,2) NOT NULL,
  `unit_cost` decimal(15,2),
  `stock_before` decimal(15,2) NOT NULL,
  `stock_after` decimal(15,2) NOT NULL,
  `reference_type` varchar(50),
  `reference_id` bigint,
  `notes` varchar(500),
  `created_by` bigint NOT NULL,
  `occurred_at` datetime NOT NULL,
  `created_at` timestamp
);

CREATE TABLE `audit_logs` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `user_id` bigint,
  `action` varchar(100) NOT NULL,
  `auditable_type` varchar(100) NOT NULL,
  `auditable_id` bigint,
  `old_values` json,
  `new_values` json,
  `ip_address` varchar(45),
  `user_agent` text,
  `description` varchar(500),
  `created_at` timestamp NOT NULL
);

CREATE TABLE `application_settings` (
  `id` bigint PRIMARY KEY AUTO_INCREMENT,
  `setting_key` varchar(100) UNIQUE NOT NULL,
  `setting_value` text,
  `data_type` varchar(30) NOT NULL DEFAULT 'string',
  `updated_by` bigint,
  `created_at` timestamp,
  `updated_at` timestamp
);

CREATE UNIQUE INDEX `service_prices_index_0` ON `service_prices` (`service_type_id`, `paper_type_id`, `print_mode_id`, `side_mode`, `effective_from`);

CREATE INDEX `stock_movements_index_1` ON `stock_movements` (`product_id`, `occurred_at`);

CREATE INDEX `stock_movements_index_2` ON `stock_movements` (`reference_type`, `reference_id`);

CREATE INDEX `audit_logs_index_3` ON `audit_logs` (`auditable_type`, `auditable_id`);

CREATE INDEX `audit_logs_index_4` ON `audit_logs` (`user_id`, `created_at`);

ALTER TABLE `products` COMMENT = 'Menyimpan ATK dan bahan habis pakai, termasuk stok kertas. current_stock adalah saldo cepat; riwayat sumber kebenaran ada di stock_movements.';

ALTER TABLE `paper_types` COMMENT = 'Menghubungkan ukuran kertas dengan produk stok, misalnya Kertas A4.';

ALTER TABLE `service_prices` COMMENT = 'Tarif tidak dihapus saat berubah. Tutup effective_until lalu buat baris tarif baru.';

ALTER TABLE `order_channels` COMMENT = 'Contoh: WALK_IN dan WHATSAPP.';

ALTER TABLE `order_items` COMMENT = 'Jika type=product maka product_id wajib. Jika type=service maka service_price_id wajib. Harga dan nama disalin agar transaksi lama tidak berubah saat master diperbarui.';

ALTER TABLE `service_item_details` COMMENT = 'sheets_billed untuk tagihan, sheets_consumed untuk pengurangan stok aktual.';

ALTER TABLE `order_adjustments` COMMENT = 'Mencatat revisi atau pembatalan tanpa menghapus riwayat pesanan. Pemakaian bahan terkait tetap masuk ke stock_movements.';

ALTER TABLE `stock_movements` COMMENT = 'reference_type/reference_id mengarah secara logis ke order, order_item, adjustment, atau sumber lain.';

ALTER TABLE `role_user` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `role_user` ADD FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

ALTER TABLE `products` ADD FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`);

ALTER TABLE `products` ADD FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`);

ALTER TABLE `paper_types` ADD FOREIGN KEY (`inventory_product_id`) REFERENCES `products` (`id`);

ALTER TABLE `service_prices` ADD FOREIGN KEY (`service_type_id`) REFERENCES `service_types` (`id`);

ALTER TABLE `service_prices` ADD FOREIGN KEY (`paper_type_id`) REFERENCES `paper_types` (`id`);

ALTER TABLE `service_prices` ADD FOREIGN KEY (`print_mode_id`) REFERENCES `print_modes` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`channel_id`) REFERENCES `order_channels` (`id`);

ALTER TABLE `orders` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

ALTER TABLE `order_items` ADD FOREIGN KEY (`service_price_id`) REFERENCES `service_prices` (`id`);

ALTER TABLE `service_item_details` ADD FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`);

ALTER TABLE `service_item_details` ADD FOREIGN KEY (`paper_type_id`) REFERENCES `paper_types` (`id`);

ALTER TABLE `order_status_histories` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `order_status_histories` ADD FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);

ALTER TABLE `order_adjustments` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `order_adjustments` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);

ALTER TABLE `payments` ADD FOREIGN KEY (`received_by`) REFERENCES `users` (`id`);

ALTER TABLE `stock_movements` ADD FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

ALTER TABLE `stock_movements` ADD FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

ALTER TABLE `audit_logs` ADD FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

ALTER TABLE `application_settings` ADD FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

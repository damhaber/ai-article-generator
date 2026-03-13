<?php
if (!defined('ABSPATH')) { exit; }

if (!interface_exists('AIG_Provider_Interface')) {
    interface AIG_Provider_Interface {
        public function get_id(): string;
        public function get_label(): string;
        public function is_available(array $config = []): bool;
        public function list_models(array $config = []): array;
        public function generate(array $payload, array $config = []): array;
        public function test(array $config = []): array;
    }
}

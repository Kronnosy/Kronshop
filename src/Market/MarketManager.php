<?php

namespace Market;

use pocketmine\player\Player;
use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\utils\Config;
use Kronnonmy\Kronnomy;
use cooldogepm\BedrockEconomy\api\BedrockEconomyAPI;

class MarketManager {
    
    private Main $plugin;
    /** @var array<string, bool> Transaction lock - aynı anda birden fazla işlem yapılmasını engeller */
    private array $transactionLocks = [];
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    /**
     * Transaction lock kontrolü - race condition'ları önler
     */
    private function isTransactionLocked(Player $player): bool {
        return isset($this->transactionLocks[$player->getName()]);
    }
    
    /**
     * Transaction lock'u aktif et
     */
    private function lockTransaction(Player $player): void {
        $this->transactionLocks[$player->getName()] = true;
    }
    
    /**
     * Transaction lock'u kaldır
     */
    private function unlockTransaction(Player $player): void {
        unset($this->transactionLocks[$player->getName()]);
    }
    
    /**
     * Player disconnect olduğunda transaction lock'u temizle
     */
    public function clearTransactionLock(string $playerName): void {
        unset($this->transactionLocks[$playerName]);
    }
    
    public function getPrice(string $itemName, string $type = "buy"): ?int {
        $config = $this->plugin->getMarketConfig();
        $prices = $config->get("prices", []);
        
        $itemName = strtolower($itemName);
        if (isset($prices[$itemName]) && isset($prices[$itemName][$type])) {
            return (int) $prices[$itemName][$type];
        }
        
        return null;
    }
    
    public function getItemDisplayName(string $itemName): ?string {
        $config = $this->plugin->getMarketConfig();
        $prices = $config->get("prices", []);
        
        $itemName = strtolower($itemName);
        if (isset($prices[$itemName]["name"])) {
            return $prices[$itemName]["name"];
        }
        
        return null;
    }
    
    public function getAllItems(): array {
        $config = $this->plugin->getMarketConfig();
        return $config->get("prices", []);
    }
    
    /**
     * Item'in kategorisini belirler
     */
    public function getItemCategory(string $itemId): string {
        $itemIdLower = strtolower($itemId);
        
        // Bloklar
        if (strpos($itemIdLower, "block") !== false || 
            strpos($itemIdLower, "ore") !== false ||
            strpos($itemIdLower, "stone") !== false ||
            strpos($itemIdLower, "dirt") !== false ||
            strpos($itemIdLower, "sand") !== false ||
            strpos($itemIdLower, "gravel") !== false ||
            strpos($itemIdLower, "log") !== false ||
            strpos($itemIdLower, "planks") !== false ||
            strpos($itemIdLower, "brick") !== false ||
            strpos($itemIdLower, "slab") !== false ||
            strpos($itemIdLower, "stairs") !== false ||
            strpos($itemIdLower, "fence") !== false ||
            strpos($itemIdLower, "wall") !== false ||
            strpos($itemIdLower, "glass") !== false ||
            strpos($itemIdLower, "wool") !== false ||
            strpos($itemIdLower, "concrete") !== false ||
            strpos($itemIdLower, "terracotta") !== false ||
            strpos($itemIdLower, "shulker_box") !== false ||
            in_array($itemIdLower, ["grass_block", "cobblestone", "bedrock", "sponge", "sandstone", "obsidian", "ice", "snow", "cactus", "clay", "netherrack", "soul_sand", "glowstone", "end_stone", "prismarine", "sea_lantern", "hay_block", "packed_ice", "magma", "nether_wart_block", "red_nether_brick", "bone_block"])) {
            return "blocks";
        }
        
        // Kaynaklar (Ores, Ingots, Gems)
        if (strpos($itemIdLower, "ingot") !== false ||
            strpos($itemIdLower, "nugget") !== false ||
            strpos($itemIdLower, "dust") !== false ||
            in_array($itemIdLower, ["diamond", "emerald", "coal", "redstone", "lapis_lazuli", "quartz", "nether_star", "blaze_rod", "ghast_tear", "ender_pearl", "shulker_shell"])) {
            return "resources";
        }
        
        // Araçlar (Tools)
        if (strpos($itemIdLower, "sword") !== false ||
            strpos($itemIdLower, "pickaxe") !== false ||
            strpos($itemIdLower, "axe") !== false ||
            strpos($itemIdLower, "shovel") !== false ||
            strpos($itemIdLower, "hoe") !== false ||
            strpos($itemIdLower, "bow") !== false ||
            strpos($itemIdLower, "fishing_rod") !== false ||
            in_array($itemIdLower, ["flint_and_steel", "shears", "shield", "elytra", "carrot_on_a_stick", "lead", "name_tag"])) {
            return "tools";
        }
        
        // Zırh (Armor)
        if (strpos($itemIdLower, "helmet") !== false ||
            strpos($itemIdLower, "chestplate") !== false ||
            strpos($itemIdLower, "leggings") !== false ||
            strpos($itemIdLower, "boots") !== false ||
            strpos($itemIdLower, "horse_armor") !== false) {
            return "armor";
        }
        
        // Yiyecek (Food)
        if (strpos($itemIdLower, "apple") !== false ||
            strpos($itemIdLower, "porkchop") !== false ||
            strpos($itemIdLower, "beef") !== false ||
            strpos($itemIdLower, "chicken") !== false ||
            strpos($itemIdLower, "fish") !== false ||
            strpos($itemIdLower, "rabbit") !== false ||
            strpos($itemIdLower, "mutton") !== false ||
            strpos($itemIdLower, "potato") !== false ||
            strpos($itemIdLower, "carrot") !== false ||
            strpos($itemIdLower, "bread") !== false ||
            strpos($itemIdLower, "cookie") !== false ||
            strpos($itemIdLower, "cake") !== false ||
            strpos($itemIdLower, "pie") !== false ||
            strpos($itemIdLower, "stew") !== false ||
            strpos($itemIdLower, "soup") !== false ||
            in_array($itemIdLower, ["wheat", "melon", "pumpkin", "beetroot", "chorus_fruit", "rotten_flesh", "spider_eye", "mushroom_stew", "beetroot_soup", "rabbit_stew"])) {
            return "food";
        }
        
        // Redstone
        if (strpos($itemIdLower, "redstone") !== false ||
            strpos($itemIdLower, "repeater") !== false ||
            strpos($itemIdLower, "comparator") !== false ||
            strpos($itemIdLower, "detector") !== false ||
            strpos($itemIdLower, "piston") !== false ||
            strpos($itemIdLower, "dispenser") !== false ||
            strpos($itemIdLower, "dropper") !== false ||
            strpos($itemIdLower, "hopper") !== false ||
            strpos($itemIdLower, "rail") !== false ||
            strpos($itemIdLower, "button") !== false ||
            strpos($itemIdLower, "pressure_plate") !== false ||
            strpos($itemIdLower, "lever") !== false ||
            strpos($itemIdLower, "tripwire") !== false ||
            in_array($itemIdLower, ["redstone_torch", "redstone_lamp", "redstone_block", "command_block", "repeating_command_block", "chain_command_block", "command_block_minecart"])) {
            return "redstone";
        }
        
        // Dekoratif
        if (strpos($itemIdLower, "banner") !== false ||
            strpos($itemIdLower, "carpet") !== false ||
            strpos($itemIdLower, "flower") !== false ||
            strpos($itemIdLower, "painting") !== false ||
            strpos($itemIdLower, "sign") !== false ||
            strpos($itemIdLower, "bed") !== false ||
            in_array($itemIdLower, ["torch", "lantern", "campfire", "flower_pot", "item_frame", "armor_stand", "jukebox", "note_block"])) {
            return "decorative";
        }
        
        // Diğer
        return "other";
    }
    
    /**
     * Kategoriye göre itemleri gruplar
     */
    public function getItemsByCategory(?string $category = null): array {
        $allItems = $this->getAllItems();
        
        // Eğer kategori "all" veya null ise, tüm itemleri döndür
        if ($category === null || $category === "all") {
            return $allItems;
        }
        
        // Belirli bir kategori için itemleri filtrele
        $filtered = [];
        foreach ($allItems as $itemId => $itemData) {
            $itemCategory = $this->getItemCategory($itemId);
            
            if ($itemCategory === $category) {
                $filtered[$itemId] = $itemData;
            }
        }
        
        return $filtered;
    }
    
    /**
     * Tüm kategorileri döndürür (dil sistemine entegre)
     */
    public function getCategories(?Player $player = null): array {
        $langManager = $this->plugin->getLanguageManager();
        
        return [
            "all" => $langManager->get("categories.all", $player),
            "blocks" => $langManager->get("categories.blocks", $player),
            "resources" => $langManager->get("categories.resources", $player),
            "tools" => $langManager->get("categories.tools", $player),
            "armor" => $langManager->get("categories.armor", $player),
            "food" => $langManager->get("categories.food", $player),
            "redstone" => $langManager->get("categories.redstone", $player),
            "decorative" => $langManager->get("categories.decorative", $player),
            "other" => $langManager->get("categories.other", $player)
        ];
    }
    
    public function buyItem(Player $player, string $itemName, int $amount): bool {
        // Player online kontrolü
        if (!$player->isOnline()) {
            return false;
        }
        
        // Transaction lock kontrolü - aynı anda birden fazla işlem yapılmasını engeller
        if ($this->isTransactionLocked($player)) {
            $langManager = $this->plugin->getLanguageManager();
            $player->sendMessage($langManager->get("buy.transaction_in_progress", $player));
            return false;
        }
        
        // Negatif ve sıfır değer kontrolleri
        if ($amount <= 0) {
            $langManager = $this->plugin->getLanguageManager();
            $player->sendMessage($langManager->get("buy.amount_too_low", $player));
            return false;
        }
        
        // Integer overflow kontrolü
        if ($amount > PHP_INT_MAX / 1000) {
            $langManager = $this->plugin->getLanguageManager();
            $player->sendMessage($langManager->get("buy.amount_too_high", $player));
            return false;
        }
        
        $itemName = strtolower($itemName);
        $price = $this->getPrice($itemName, "buy");
        $langManager = $this->plugin->getLanguageManager();
        
        if ($price === null) {
            $player->sendMessage($langManager->get("buy.item_not_found", $player));
            return false;
        }
        
        // Negatif fiyat kontrolü
        if ($price <= 0) {
            $this->plugin->getLogger()->warning("Invalid price for item {$itemName}: {$price}");
            $player->sendMessage($langManager->get("buy.invalid_price", $player));
            return false;
        }
        
        $config = $this->plugin->getMarketConfig();
        $settings = $config->get("settings", []);
        
        if (!($settings["enable_buy"] ?? true)) {
            $player->sendMessage($langManager->get("buy.buy_disabled", $player));
            return false;
        }
        
        $maxBuy = $settings["max_buy_per_transaction"] ?? 64;
        if ($amount > $maxBuy) {
            $player->sendMessage($langManager->get("buy.max_buy", $player, ["max" => $maxBuy]));
            return false;
        }
        
        // Integer overflow kontrolü - totalPrice
        if ($price > PHP_INT_MAX / $amount) {
            $player->sendMessage($langManager->get("buy.amount_too_high", $player));
            return false;
        }
        
        $totalPrice = $price * $amount;
        
        // Negatif totalPrice kontrolü
        if ($totalPrice <= 0) {
            $player->sendMessage($langManager->get("buy.invalid_price", $player));
            return false;
        }
        
        // Ekonomi sistemi kontrolü
        if (!$this->plugin->isEconomyEnabled()) {
            $player->sendMessage($langManager->get("buy.economy_disabled", $player));
            return false;
        }
        
        // Eşyayı oluştur
        $item = StringToItemParser::getInstance()->parse($itemName);
        if ($item === null) {
            $player->sendMessage($langManager->get("buy.invalid_item", $player));
            return false;
        }
        
        $item->setCount($amount);
        
        // Envanter kontrolü
        if (!$player->getInventory()->canAddItem($item)) {
            $player->sendMessage($langManager->get("buy.inventory_full", $player));
            return false;
        }
        
        // Transaction lock'u aktif et
        $this->lockTransaction($player);
        
        // Kronnomy kullanılıyorsa (öncelikli)
        if ($this->plugin->isKronnomyEnabled()) {
            $kronnomy = $this->plugin->getKronnomy();
            $economyManager = $kronnomy->getEconomyManager();
            
            // Player hala online mı kontrol et
            if (!$player->isOnline()) {
                $this->unlockTransaction($player);
                return false;
            }
            
            $balance = $economyManager->getBalance($player->getName());
            
            if ($balance < $totalPrice) {
                $this->unlockTransaction($player);
                $currency = $settings["currency"] ?? $economyManager->formatBalance(0);
                $formattedBalance = $economyManager->formatBalance($balance);
                $formattedPrice = $economyManager->formatBalance($totalPrice);
                $player->sendMessage($langManager->get("buy.insufficient_balance", $player));
                $player->sendMessage($langManager->get("buy.required", $player, ["amount" => $formattedPrice]));
                $player->sendMessage($langManager->get("buy.current", $player, ["amount" => $formattedBalance]));
                $player->sendMessage("§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                return false;
            }
            
            // Para düşür
            if (!$economyManager->removeBalance($player->getName(), $totalPrice)) {
                $this->unlockTransaction($player);
                $player->sendMessage($langManager->get("buy.withdraw_failed", $player));
                return false;
            }
            
            // Player hala online mı kontrol et
            if (!$player->isOnline()) {
                // Para geri ver (rollback)
                $economyManager->addBalance($player->getName(), $totalPrice);
                $this->unlockTransaction($player);
                return false;
            }
            
            // Envanter kontrolünü tekrar yap (para çekildikten sonra)
            if (!$player->getInventory()->canAddItem($item)) {
                // Para geri ver (rollback)
                $economyManager->addBalance($player->getName(), $totalPrice);
                $this->unlockTransaction($player);
                $player->sendMessage($langManager->get("buy.inventory_full", $player));
                return false;
            }
            
            // Eşyayı ver
            $player->getInventory()->addItem($item);
            
            // Transaction lock'u kaldır
            $this->unlockTransaction($player);
            
            $displayName = $this->getItemDisplayName($itemName) ?? $itemName;
            $formattedPrice = $economyManager->formatBalance($totalPrice);
            $player->sendMessage($langManager->get("buy.success", $player));
            $player->sendMessage($langManager->get("buy.item_label", $player, ["amount" => $amount, "name" => $displayName]));
            $player->sendMessage($langManager->get("buy.total_label", $player, ["amount" => $formattedPrice]));
            $player->sendMessage("§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            
            return true;
        }
        
        // BedrockEconomy kullanılıyorsa (yedek)
        if ($this->plugin->isBedrockEconomyEnabled()) {
            $manager = $this;
            BedrockEconomyAPI::getInstance()->getPlayerBalance($player->getName(), function (?int $balance) use ($player, $totalPrice, $item, $itemName, $amount, $settings, $manager, $langManager) {
                // Player online kontrolü
                if (!$player->isOnline()) {
                    $manager->unlockTransaction($player);
                    return;
                }
                
                if ($balance === null) {
                    $manager->unlockTransaction($player);
                    $player->sendMessage($langManager->get("buy.balance_check_failed", $player));
                    return;
                }
                
                if ($balance < $totalPrice) {
                    $manager->unlockTransaction($player);
                    $currency = $settings["currency"] ?? "Para";
                    $player->sendMessage($langManager->get("buy.insufficient_balance", $player));
                    $player->sendMessage($langManager->get("buy.required", $player, ["amount" => $totalPrice . " " . $currency]));
                    $player->sendMessage($langManager->get("buy.current", $player, ["amount" => $balance]));
                    $player->sendMessage("§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                    return;
                }
                
                // Para düşür
                BedrockEconomyAPI::getInstance()->subtractFromPlayerBalance($player->getName(), $totalPrice, function (bool $success) use ($player, $item, $itemName, $amount, $totalPrice, $settings, $manager, $langManager) {
                    // Player online kontrolü
                    if (!$player->isOnline()) {
                        // Para geri ver (rollback)
                        BedrockEconomyAPI::getInstance()->addToPlayerBalance($player->getName(), $totalPrice, function() use ($manager, $player) {
                            $manager->unlockTransaction($player);
                        });
                        return;
                    }
                    
                    if (!$success) {
                        $manager->unlockTransaction($player);
                        $player->sendMessage($langManager->get("buy.withdraw_failed", $player));
                        return;
                    }
                    
                    // Envanter kontrolünü tekrar yap (para çekildikten sonra)
                    if (!$player->getInventory()->canAddItem($item)) {
                        // Para geri ver (rollback)
                        BedrockEconomyAPI::getInstance()->addToPlayerBalance($player->getName(), $totalPrice, function() use ($manager, $player, $langManager) {
                            $manager->unlockTransaction($player);
                            if ($player->isOnline()) {
                                $player->sendMessage($langManager->get("buy.inventory_full", $player));
                            }
                        });
                        return;
                    }
                    
                    // Eşyayı ver
                    $player->getInventory()->addItem($item);
                    
                    // Transaction lock'u kaldır
                    $manager->unlockTransaction($player);
                    
                    $displayName = $manager->getItemDisplayName($itemName) ?? $itemName;
                    $currency = $settings["currency"] ?? "Para";
                    $player->sendMessage($langManager->get("buy.success", $player));
                    $player->sendMessage($langManager->get("buy.item_label", $player, ["amount" => $amount, "name" => $displayName]));
                    $player->sendMessage($langManager->get("buy.total_label", $player, ["amount" => $totalPrice . " " . $currency]));
                    $player->sendMessage("§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
                });
            });
            return true;
        }
        
        // Ekonomi sistemi yoksa
        $this->unlockTransaction($player);
        return false;
    }
    
    public function sellItem(Player $player, string $itemName, int $amount): bool {
        // Player online kontrolü
        if (!$player->isOnline()) {
            return false;
        }
        
        // Transaction lock kontrolü - aynı anda birden fazla işlem yapılmasını engeller
        if ($this->isTransactionLocked($player)) {
            $langManager = $this->plugin->getLanguageManager();
            $player->sendMessage($langManager->get("sell.transaction_in_progress", $player));
            return false;
        }
        
        // Negatif ve sıfır değer kontrolleri
        if ($amount <= 0) {
            $langManager = $this->plugin->getLanguageManager();
            $player->sendMessage($langManager->get("sell.amount_too_low", $player));
            return false;
        }
        
        // Integer overflow kontrolü
        if ($amount > PHP_INT_MAX / 1000) {
            $langManager = $this->plugin->getLanguageManager();
            $player->sendMessage($langManager->get("sell.amount_too_high", $player));
            return false;
        }
        
        $itemName = strtolower($itemName);
        $price = $this->getPrice($itemName, "sell");
        $langManager = $this->plugin->getLanguageManager();
        
        if ($price === null) {
            $player->sendMessage($langManager->get("sell.item_not_sellable", $player));
            return false;
        }
        
        // Negatif fiyat kontrolü
        if ($price <= 0) {
            $this->plugin->getLogger()->warning("Invalid sell price for item {$itemName}: {$price}");
            $player->sendMessage($langManager->get("sell.invalid_price", $player));
            return false;
        }
        
        $config = $this->plugin->getMarketConfig();
        $settings = $config->get("settings", []);
        
        if (!($settings["enable_sell"] ?? true)) {
            $player->sendMessage($langManager->get("sell.sell_disabled", $player));
            return false;
        }
        
        $maxSell = $settings["max_sell_per_transaction"] ?? 64;
        if ($amount > $maxSell) {
            $player->sendMessage($langManager->get("sell.max_sell", $player, ["max" => $maxSell]));
            return false;
        }
        
        // Eşyayı kontrol et
        $item = StringToItemParser::getInstance()->parse($itemName);
        if ($item === null) {
            $player->sendMessage($langManager->get("sell.invalid_item", $player));
            return false;
        }
        
        $item->setCount($amount);
        
        // Gerçek item miktarını kontrol et (contains yeterli değil)
        $inventory = $player->getInventory();
        $actualCount = 0;
        foreach ($inventory->getContents() as $invItem) {
            if ($invItem->equals($item, true, false)) { // Meta ve NBT'yi ignore et, sadece item type kontrolü
                $actualCount += $invItem->getCount();
            }
        }
        
        if ($actualCount < $amount) {
            $player->sendMessage($langManager->get("sell.insufficient_items", $player));
            return false;
        }
        
        // Ekonomi sistemi kontrolü
        if (!$this->plugin->isEconomyEnabled()) {
            $player->sendMessage($langManager->get("sell.economy_disabled", $player));
            return false;
        }
        
        // Integer overflow kontrolü - totalPrice
        if ($price > PHP_INT_MAX / $amount) {
            $player->sendMessage($langManager->get("sell.amount_too_high", $player));
            return false;
        }
        
        $totalPrice = $price * $amount;
        
        // Negatif totalPrice kontrolü
        if ($totalPrice <= 0) {
            $player->sendMessage($langManager->get("sell.invalid_price", $player));
            return false;
        }
        
        // Transaction lock'u aktif et
        $this->lockTransaction($player);
        
        // Item miktarını tekrar kontrol et (lock'tan önce değişmiş olabilir)
        $actualCount = 0;
        foreach ($player->getInventory()->getContents() as $invItem) {
            if ($invItem->equals($item, true, false)) {
                $actualCount += $invItem->getCount();
            }
        }
        
        if ($actualCount < $amount) {
            $this->unlockTransaction($player);
            $player->sendMessage($langManager->get("sell.insufficient_items", $player));
            return false;
        }
        
        // Kronnomy kullanılıyorsa (öncelikli)
        if ($this->plugin->isKronnomyEnabled()) {
            $kronnomy = $this->plugin->getKronnomy();
            $economyManager = $kronnomy->getEconomyManager();
            
            // Player hala online mı kontrol et
            if (!$player->isOnline()) {
                $this->unlockTransaction($player);
                return false;
            }
            
            // Para ekle
            if (!$economyManager->addBalance($player->getName(), $totalPrice)) {
                $this->unlockTransaction($player);
                $player->sendMessage($langManager->get("sell.add_failed", $player));
                return false;
            }
            
            // Player hala online mı kontrol et
            if (!$player->isOnline()) {
                // Para geri al (rollback)
                $economyManager->removeBalance($player->getName(), $totalPrice);
                $this->unlockTransaction($player);
                return false;
            }
            
            // Item miktarını son kez kontrol et
            $finalCount = 0;
            foreach ($player->getInventory()->getContents() as $invItem) {
                if ($invItem->equals($item, true, false)) {
                    $finalCount += $invItem->getCount();
                }
            }
            
            if ($finalCount < $amount) {
                // Para geri al (rollback)
                $economyManager->removeBalance($player->getName(), $totalPrice);
                $this->unlockTransaction($player);
                $player->sendMessage($langManager->get("sell.insufficient_items", $player));
                return false;
            }
            
            // Para başarıyla eklendi, şimdi eşyayı al
            $player->getInventory()->removeItem($item);
            
            // Transaction lock'u kaldır
            $this->unlockTransaction($player);
            
            $displayName = $this->getItemDisplayName($itemName) ?? $itemName;
            $formattedPrice = $economyManager->formatBalance($totalPrice);
            $player->sendMessage($langManager->get("sell.success", $player));
            $player->sendMessage($langManager->get("sell.item_label", $player, ["amount" => $amount, "name" => $displayName]));
            $player->sendMessage($langManager->get("sell.earnings_label", $player, ["amount" => $formattedPrice]));
            $player->sendMessage("§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            
            return true;
        }
        
        // BedrockEconomy kullanılıyorsa (yedek)
        if ($this->plugin->isBedrockEconomyEnabled()) {
            $manager = $this;
            BedrockEconomyAPI::getInstance()->addToPlayerBalance($player->getName(), $totalPrice, function (bool $success) use ($player, $item, $itemName, $amount, $totalPrice, $settings, $manager, $langManager) {
                // Player online kontrolü
                if (!$player->isOnline()) {
                    // Para geri al (rollback)
                    BedrockEconomyAPI::getInstance()->subtractFromPlayerBalance($player->getName(), $totalPrice, function() use ($manager, $player) {
                        $manager->unlockTransaction($player);
                    });
                    return;
                }
                
                if (!$success) {
                    $manager->unlockTransaction($player);
                    $player->sendMessage($langManager->get("sell.add_failed", $player));
                    return;
                }
                
                // Item miktarını son kez kontrol et
                $finalCount = 0;
                foreach ($player->getInventory()->getContents() as $invItem) {
                    if ($invItem->equals($item, true, false)) {
                        $finalCount += $invItem->getCount();
                    }
                }
                
                if ($finalCount < $amount) {
                    // Para geri al (rollback)
                    BedrockEconomyAPI::getInstance()->subtractFromPlayerBalance($player->getName(), $totalPrice, function() use ($manager, $player, $langManager) {
                        $manager->unlockTransaction($player);
                        if ($player->isOnline()) {
                            $player->sendMessage($langManager->get("sell.insufficient_items", $player));
                        }
                    });
                    return;
                }
                
                // Para başarıyla eklendi, şimdi eşyayı al
                $player->getInventory()->removeItem($item);
                
                // Transaction lock'u kaldır
                $manager->unlockTransaction($player);
                
                $displayName = $manager->getItemDisplayName($itemName) ?? $itemName;
                $currency = $settings["currency"] ?? "Para";
                $player->sendMessage($langManager->get("sell.success", $player));
                $player->sendMessage($langManager->get("sell.item_label", $player, ["amount" => $amount, "name" => $displayName]));
                $player->sendMessage($langManager->get("sell.earnings_label", $player, ["amount" => $totalPrice . " " . $currency]));
                $player->sendMessage("§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            });
            return true;
        }
        
        // Ekonomi sistemi yoksa
        $this->unlockTransaction($player);
        return false;
    }
    
    
    /**
     * Oyundaki tüm itemleri markete ekler
     */
    public function addAllItemsToMarket(): int {
        $config = $this->plugin->getMarketConfig();
        $prices = $config->get("prices", []);
        $added = 0;
        
        // PocketMine'deki tüm item ID'leri
        $allItemIds = $this->getAllItemIds();
        
        foreach ($allItemIds as $itemId) {
            $itemIdLower = strtolower($itemId);
            
            // Eğer item zaten eklenmemişse ekle
            if (!isset($prices[$itemIdLower])) {
                $item = StringToItemParser::getInstance()->parse($itemIdLower);
                
                if ($item !== null) {
                    // Varsayılan fiyatları hesapla (item'in nadirliğine göre)
                    $defaultBuyPrice = $this->calculateDefaultBuyPrice($itemIdLower);
                    $defaultSellPrice = (int)($defaultBuyPrice * 0.8); // Satış fiyatı alış fiyatının %80'i
                    
                    $displayName = $item->getName();
                    
                    $prices[$itemIdLower] = [
                        "buy" => $defaultBuyPrice,
                        "sell" => $defaultSellPrice,
                        "name" => $displayName
                    ];
                    
                    $added++;
                }
            }
        }
        
        $config->set("prices", $prices);
        $config->save();
        
        return $added;
    }
    
    /**
     * Tüm item ID'lerini döndürür
     */
    private function getAllItemIds(): array {
        // PocketMine 5.0'da bilinen tüm item ID'leri
        // Bu liste Minecraft'ın tüm itemlerini içerir
        return [
            // Bloklar
            "stone", "grass_block", "dirt", "cobblestone", "planks", "sapling", "bedrock",
            "sand", "gravel", "gold_ore", "iron_ore", "coal_ore", "log", "leaves",
            "sponge", "glass", "lapis_ore", "lapis_block", "dispenser", "sandstone",
            "noteblock", "bed", "golden_rail", "detector_rail", "sticky_piston", "web",
            "tallgrass", "deadbush", "piston", "pistonarmcollision", "wool", "yellow_flower",
            "red_flower", "brown_mushroom", "red_mushroom", "gold_block", "iron_block",
            "double_stone_slab", "stone_slab", "brick_block", "tnt", "bookshelf", "mossy_cobblestone",
            "obsidian", "torch", "fire", "mob_spawner", "oak_stairs", "chest", "redstone_wire",
            "diamond_ore", "diamond_block", "crafting_table", "wheat", "farmland", "furnace",
            "lit_furnace", "standing_sign", "wooden_door", "ladder", "rail", "stone_stairs",
            "wall_sign", "lever", "stone_pressure_plate", "iron_door", "wooden_pressure_plate",
            "redstone_ore", "lit_redstone_ore", "redstone_torch", "stone_button", "snow_layer",
            "ice", "snow", "cactus", "clay", "reeds", "fence", "pumpkin", "netherrack",
            "soul_sand", "glowstone", "pumpkin_stem", "melon_stem", "vine", "fence_gate",
            "brick_stairs", "stone_brick_stairs", "mycelium", "waterlily", "nether_brick",
            "nether_brick_fence", "nether_brick_stairs", "nether_wart", "enchanting_table",
            "brewing_stand", "cauldron", "end_portal", "end_portal_frame", "end_stone",
            "dragon_egg", "redstone_lamp", "lit_redstone_lamp", "double_wooden_slab",
            "wooden_slab", "cocoa", "sandstone_stairs", "emerald_ore", "ender_chest",
            "tripwire_hook", "tripwire", "emerald_block", "spruce_stairs", "birch_stairs",
            "jungle_stairs", "command_block", "beacon", "cobblestone_wall", "flower_pot",
            "carrots", "potatoes", "wooden_button", "skull", "anvil", "trapped_chest",
            "light_weighted_pressure_plate", "heavy_weighted_pressure_plate", "comparator",
            "daylight_detector", "redstone_block", "quartz_ore", "hopper", "quartz_block",
            "quartz_stairs", "activator_rail", "dropper", "stained_hardened_clay",
            "stained_glass_pane", "leaves2", "log2", "acacia_stairs", "dark_oak_stairs",
            "slime", "barrier", "iron_trapdoor", "prismarine", "sea_lantern", "hay_block",
            "carpet", "hardened_clay", "coal_block", "packed_ice", "double_plant", "standing_banner",
            "wall_banner", "daylight_detector_inverted", "red_sandstone", "red_sandstone_stairs",
            "double_stone_slab2", "stone_slab2", "spruce_fence_gate", "birch_fence_gate",
            "jungle_fence_gate", "dark_oak_fence_gate", "acacia_fence_gate", "spruce_fence",
            "birch_fence", "jungle_fence", "dark_oak_fence", "acacia_fence", "spruce_door",
            "birch_door", "jungle_door", "acacia_door", "dark_oak_door", "end_rod",
            "chorus_plant", "chorus_flower", "purpur_block", "purpur_pillar", "purpur_stairs",
            "purpur_double_slab", "purpur_slab", "end_bricks", "beetroots", "grass_path",
            "end_gateway", "repeating_command_block", "chain_command_block", "frosted_ice",
            "magma", "nether_wart_block", "red_nether_brick", "bone_block", "structure_void",
            "structure_block", "shulker_box", "white_shulker_box", "orange_shulker_box",
            "magenta_shulker_box", "light_blue_shulker_box", "yellow_shulker_box",
            "lime_shulker_box", "pink_shulker_box", "gray_shulker_box", "silver_shulker_box",
            "cyan_shulker_box", "purple_shulker_box", "blue_shulker_box", "brown_shulker_box",
            "green_shulker_box", "red_shulker_box", "black_shulker_box", "white_glazed_terracotta",
            "orange_glazed_terracotta", "magenta_glazed_terracotta", "light_blue_glazed_terracotta",
            "yellow_glazed_terracotta", "lime_glazed_terracotta", "pink_glazed_terracotta",
            "gray_glazed_terracotta", "silver_glazed_terracotta", "cyan_glazed_terracotta",
            "purple_glazed_terracotta", "blue_glazed_terracotta", "brown_glazed_terracotta",
            "green_glazed_terracotta", "red_glazed_terracotta", "black_glazed_terracotta",
            "concrete", "concrete_powder", "structure_block",
            
            // Eşyalar
            "iron_shovel", "iron_pickaxe", "iron_axe", "flint_and_steel", "apple", "bow",
            "arrow", "coal", "diamond", "iron_ingot", "gold_ingot", "iron_sword", "wooden_sword",
            "wooden_shovel", "wooden_pickaxe", "wooden_axe", "stone_sword", "stone_shovel",
            "stone_pickaxe", "stone_axe", "diamond_sword", "diamond_shovel", "diamond_pickaxe",
            "diamond_axe", "stick", "bowl", "mushroom_stew", "golden_sword", "golden_shovel",
            "golden_pickaxe", "golden_axe", "string", "feather", "gunpowder", "wooden_hoe",
            "stone_hoe", "iron_hoe", "diamond_hoe", "golden_hoe", "wheat_seeds", "wheat",
            "leather_helmet", "leather_chestplate", "leather_leggings", "leather_boots",
            "chainmail_helmet", "chainmail_chestplate", "chainmail_leggings", "chainmail_boots",
            "iron_helmet", "iron_chestplate", "iron_leggings", "iron_boots", "diamond_helmet",
            "diamond_chestplate", "diamond_leggings", "diamond_boots", "golden_helmet",
            "golden_chestplate", "golden_leggings", "golden_boots", "flint", "porkchop",
            "cooked_porkchop", "painting", "golden_apple", "sign", "wooden_door", "bucket",
            "water_bucket", "lava_bucket", "minecart", "saddle", "iron_door", "redstone",
            "snowball", "boat", "leather", "milk_bucket", "brick", "clay_ball", "reeds",
            "paper", "book", "slime_ball", "chest_minecart", "furnace_minecart", "egg",
            "compass", "fishing_rod", "clock", "glowstone_dust", "fish", "cooked_fish",
            "dye", "bone", "sugar", "cake", "bed", "repeater", "cookie", "filled_map",
            "shears", "melon", "pumpkin_seeds", "melon_seeds", "beef", "cooked_beef",
            "chicken", "cooked_chicken", "rotten_flesh", "ender_pearl", "blaze_rod",
            "ghast_tear", "gold_nugget", "nether_wart", "potion", "glass_bottle", "spider_eye",
            "fermented_spider_eye", "blaze_powder", "magma_cream", "brewing_stand",
            "cauldron", "ender_eye", "speckled_melon", "spawn_egg", "experience_bottle",
            "fire_charge", "writable_book", "written_book", "emerald", "item_frame",
            "flower_pot", "carrot", "potato", "baked_potato", "poisonous_potato", "empty_map",
            "golden_carrot", "skull", "carrot_on_a_stick", "nether_star", "pumpkin_pie",
            "fireworks", "firework_charge", "enchanted_book", "comparator", "netherbrick",
            "quartz", "tnt_minecart", "hopper_minecart", "prismarine_shard", "prismarine_crystals",
            "rabbit", "cooked_rabbit", "rabbit_stew", "rabbit_foot", "rabbit_hide",
            "armor_stand", "iron_horse_armor", "golden_horse_armor", "diamond_horse_armor",
            "lead", "name_tag", "command_block_minecart", "mutton", "cooked_mutton",
            "banner", "end_crystal", "spruce_door", "birch_door", "jungle_door", "acacia_door",
            "dark_oak_door", "chorus_fruit", "chorus_fruit_popped", "beetroot", "beetroot_seeds",
            "beetroot_soup", "dragon_breath", "splash_potion", "spectral_arrow", "tipped_arrow",
            "lingering_potion", "shield", "elytra", "spruce_boat", "birch_boat", "jungle_boat",
            "acacia_boat", "dark_oak_boat", "totem", "shulker_shell", "iron_nugget",
            "knowledge_book", "record_13", "record_cat", "record_blocks", "record_chirp",
            "record_far", "record_mall", "record_mellohi", "record_stal", "record_strad",
            "record_ward", "record_11", "record_wait"
        ];
    }
    
    /**
     * Item için varsayılan alış fiyatını hesaplar
     */
    private function calculateDefaultBuyPrice(string $itemId): int {
        $itemIdLower = strtolower($itemId);
        
        // Nadir itemler için yüksek fiyat
        $rareItems = [
            "diamond" => 1000,
            "emerald" => 800,
            "gold_ingot" => 500,
            "iron_ingot" => 200,
            "diamond_block" => 9000,
            "emerald_block" => 7200,
            "gold_block" => 4500,
            "iron_block" => 1800,
            "nether_star" => 5000,
            "beacon" => 10000,
            "elytra" => 15000,
            "dragon_egg" => 20000,
            "totem" => 8000
        ];
        
        if (isset($rareItems[$itemIdLower])) {
            return $rareItems[$itemIdLower];
        }
        
        // Bloklar için orta fiyat
        if (strpos($itemIdLower, "block") !== false || strpos($itemIdLower, "ore") !== false) {
            return 100;
        }
        
        // Eşyalar için düşük-orta fiyat
        if (strpos($itemIdLower, "sword") !== false || strpos($itemIdLower, "pickaxe") !== false ||
            strpos($itemIdLower, "axe") !== false || strpos($itemIdLower, "shovel") !== false ||
            strpos($itemIdLower, "hoe") !== false) {
            return 50;
        }
        
        // Zırh için orta fiyat
        if (strpos($itemIdLower, "helmet") !== false || strpos($itemIdLower, "chestplate") !== false ||
            strpos($itemIdLower, "leggings") !== false || strpos($itemIdLower, "boots") !== false) {
            return 75;
        }
        
        // Diğer itemler için varsayılan fiyat
        return 10;
    }
}


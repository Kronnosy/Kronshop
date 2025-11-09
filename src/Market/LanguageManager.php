<?php

namespace Market;

use pocketmine\player\Player;
use pocketmine\utils\Config;

class LanguageManager {
    
    private Main $plugin;
    private array $languages = [];
    private array $playerLanguages = [];
    private string $defaultLanguage = "en";
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
        $this->loadLanguages();
        $this->loadPlayerLanguages();
    }
    
    private function loadLanguages(): void {
        $languagesDir = $this->plugin->getDataFolder() . "languages/";
        
        // Eğer klasör yoksa oluştur
        if (!is_dir($languagesDir)) {
            @mkdir($languagesDir, 0777, true);
        }
        
        // Dil dosyalarını yükle
        $languageFiles = glob($languagesDir . "*.json");
        
        $this->languages = [];
        foreach ($languageFiles as $file) {
            $langCode = basename($file, ".json");
            
            // Boş dosya adlarını atla
            if (empty($langCode)) {
                continue;
            }
            
            try {
                $config = new Config($file, Config::JSON);
                $data = $config->getAll();
                
                // Temel yapı kontrolü - en azından bir anahtar olmalı ve array olmalı
                if (!empty($data) && is_array($data)) {
                    $this->languages[$langCode] = $data;
                } else {
                    $this->plugin->getLogger()->warning("Boş veya geçersiz dil dosyası atlandı: " . basename($file));
                }
            } catch (\Exception $e) {
                // Geçersiz JSON dosyasını atla
                $this->plugin->getLogger()->warning("Geçersiz JSON dosyası atlandı: " . basename($file) . " - " . $e->getMessage());
                continue;
            }
        }
        
        // Eğer hiç dil yüklenmediyse varsayılan dil dosyalarını kaydet
        if (empty($this->languages)) {
            $this->saveDefaultLanguages();
            $this->loadLanguages();
        }
    }
    
    /**
     * Dil dosyalarını yeniden yükle (yeni dil dosyaları eklendiğinde kullanılabilir)
     */
    public function reloadLanguages(): void {
        $this->loadLanguages();
    }
    
    private function saveDefaultLanguages(): void {
        $languagesDir = $this->plugin->getDataFolder() . "languages/";
        
        // İngilizce dil dosyası (varsayılan dil)
        $en = [
            "language_name" => "English",
            "menu" => [
                "title" => "§l§6║   §e§lMARKET MENU§r§6   ║\n",
                "content" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n§e§l» §r§fWelcome! Select one of the options below\n§f    to access the market system.\n\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n",
                "buy_button" => "§l§a║   §f§lBUY§r§a   ║\n§r§7Click to buy items",
                "sell_button" => "§l§c║    §f§lSELL§r§c    ║\n§r§7Click to sell items",
                "list_button" => "§l§e║    §f§lLIST§r§e    ║\n§r§7View all items",
                "price_button" => "§l§b║   §f§lPRICE§r§b   ║\n§r§7Query item prices",
                "settings_button" => "§l§d║   §f§lSETTINGS§r§d   ║\n§r§7Change settings"
            ],
            "buy" => [
                "title" => "§l§a║   §f§lBUY ITEMS§r§a   ║\n",
                "content" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n§e§l» §r§fSelect the item and amount you want to buy\n\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n",
                "item_dropdown" => "§a§l» §r§fSelect Item:",
                "amount_input" => "§a§l» §r§fAmount:",
                "amount_placeholder" => "Example: 5",
                "item_not_found" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cThis item is not available in the market!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "buy_disabled" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cBuying is currently disabled!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "max_buy" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cYou can buy a maximum of {max} items!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "economy_disabled" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cEconomy system is not active!\n§7Kronnomy or BedrockEconomy plugin is not installed.\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_item" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid item name!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "inventory_full" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cYou don't have enough space in your inventory!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "insufficient_balance" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cYou don't have enough money!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "required" => "§e§lRequired: §r§f{amount}",
                "current" => "§e§lCurrent: §r§f{amount}",
                "withdraw_failed" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cFailed to withdraw money!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "success" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§a§l» §r§aSuccessfully Purchased!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "item_label" => "§e§lItem: §r§f{amount}x {name}",
                "total_label" => "§a§lTotal: §r§f{amount}",
                "no_items" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cNo items available in the market!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_selection" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid selection!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "amount_too_low" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cAmount cannot be less than 1!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "balance_check_failed" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cFailed to check balance!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "transaction_in_progress" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cAnother transaction is in progress. Please wait...\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_price" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid price for this item!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "amount_too_high" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cAmount is too high!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_amount" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid amount! Please enter a number.\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
            ],
            "sell" => [
                "title" => "§l§c║     §f§lSELL§r§c     ║\n",
                "content" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n§e§l» §r§fSelect the item and amount you want to sell\n\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n",
                "item_dropdown" => "§c§l» §r§fSelect Item:",
                "amount_input" => "§c§l» §r§fAmount:",
                "amount_placeholder" => "Example: 5",
                "item_not_sellable" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cThis item cannot be sold in the market!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "sell_disabled" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cSelling is currently disabled!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "max_sell" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cYou can sell a maximum of {max} items!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_item" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid item name!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "insufficient_items" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cYou don't have enough items in your inventory!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "economy_disabled" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cEconomy system is not active!\n§7Kronnomy or BedrockEconomy plugin is not installed.\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "add_failed" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cFailed to add money!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "success" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§a§l» §r§aSuccessfully Sold!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "item_label" => "§e§lItem: §r§f{amount}x {name}",
                "earnings_label" => "§a§lEarnings: §r§f{amount}",
                "no_items" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cNo items available in the market!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_selection" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid selection!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "amount_too_low" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cAmount cannot be less than 1!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "transaction_in_progress" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cAnother transaction is in progress. Please wait...\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_price" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid price for this item!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "amount_too_high" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cAmount is too high!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_amount" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid amount! Please enter a number.\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
            ],
            "list" => [
                "title" => "§l§e║   §f§lMARKET LIST§r§e   ║\n",
                "no_items" => "§c§l» §r§fNo items available in the market!\n\n",
                "item_format" => "§e§l[§r§f{index}§e§l] §r§6{name}\n§7   ID: §f{item_id}\n§a   §l» §r§aBuy: §f{buy_price} {currency}\n§c   §l» §r§cSell: §f{sell_price} {currency}\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n",
                "back_button" => "§l§a║   §f§lGO BACK§r§a   ║\n§r§7Return to main menu",
                "console_title" => "§6========== §eMARKET LIST §6==========",
                "console_no_items" => "§cNo items available in the market!",
                "console_item_format" => "§e{name} §7({item_id})",
                "console_buy" => "  §aBuy: §f{buy_price} {currency}",
                "console_sell" => "  §cSell: §f{sell_price} {currency}"
            ],
            "price" => [
                "title" => "§l§b║   §f§lPRICE QUERY§r§b   ║\n",
                "content" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n§e§l» §r§fSelect the item you want to check the price of\n\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n",
                "item_dropdown" => "§b§l» §r§fSelect Item:",
                "no_items" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cNo items available in the market!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "invalid_selection" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n§c§l» §r§cInvalid selection!\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━",
                "not_found" => "§c[Market] This item is not available in the market!",
                "usage" => "§cUsage: /market price <item>",
                "example" => "§eExample: /market price diamond",
                "console_title" => "§6========== §e{name} PRICE §6==========",
                "console_buy" => "§aBuy: §f{buy_price} {currency}",
                "console_sell" => "§cSell: §f{sell_price} {currency}",
                "form_title" => "§e§l» §r§6{name} §7Price Information",
                "form_buy" => "§a§l» §r§aBuy: §f§l{buy_price} {currency}",
                "form_sell" => "§c§l» §r§cSell: §f§l{sell_price} {currency}"
            ],
            "settings" => [
                "title" => "§l§d║   §f§lSETTINGS§r§d   ║\n",
                "content" => "§r\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n§e§l» §r§fYou can change your settings\n\n§7━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n",
                "language_label" => "§d§l» §r§fSelect Language:",
                "language_changed" => "§a§l» §r§aLanguage changed successfully!",
                "back_button" => "§l§a║   §f§lGO BACK§r§a   ║\n§r§7Return to main menu"
            ],
            "commands" => [
                "player_only" => "§cThis command can only be used by players!",
                "no_permission" => "§c[Market] You don't have permission to use this command!",
                "invalid_subcommand" => "§cInvalid subcommand! Usage: /market [list|buy|sell|price]",
                "amount_too_low" => "§cAmount cannot be less than 1!",
                "error" => "§c[Market] An error occurred! Please check the console.",
                "addall_processing" => "§e[Market] Adding all items to market...",
                "addall_success" => "§a[Market] Successfully added {count} items to market!",
                "invalid_amount" => "§c[Market] Invalid amount! Please enter a number.",
                "amount_too_high" => "§c[Market] Amount is too high!"
            ],
            "main" => [
                "kronnomy_connected" => "Kronnomy integration successful!",
                "bedrockeconomy_connected" => "BedrockEconomy integration successful!",
                "no_economy" => "Kronnomy or BedrockEconomy plugin not found! Market plugin will work without economy system.",
                "no_economy_warning" => "Please install Kronnomy or BedrockEconomy plugin.",
                "plugin_enabled" => "Market plugin enabled!",
                "config_reloaded" => "Market config reloaded!"
            ],
            "categories" => [
                "all" => "All",
                "blocks" => "Blocks",
                "resources" => "Resources",
                "tools" => "Tools",
                "armor" => "Armor",
                "food" => "Food",
                "redstone" => "Redstone",
                "decorative" => "Decorative",
                "other" => "Other",
                "select_category" => "§7Please select a category:",
                "category_title" => " - Category",
                "items_count" => "({count} items)"
            ]
        ];
        
        // Sadece İngilizce dil dosyasını kaydet
        $enConfig = new Config($languagesDir . "en.json", Config::JSON);
        $enConfig->setAll($en);
        $enConfig->save();
    }
    
    private function loadPlayerLanguages(): void {
        $playerDataFile = $this->plugin->getDataFolder() . "player_languages.json";
        
        if (file_exists($playerDataFile)) {
            $data = json_decode(file_get_contents($playerDataFile), true);
            if (is_array($data)) {
                $this->playerLanguages = $data;
            }
        }
    }
    
    private function savePlayerLanguages(): void {
        $playerDataFile = $this->plugin->getDataFolder() . "player_languages.json";
        file_put_contents($playerDataFile, json_encode($this->playerLanguages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
    
    public function getLanguage(Player|string $player): string {
        if ($player instanceof Player) {
            $playerName = $player->getName();
        } else {
            $playerName = $player;
        }
        
        return $this->playerLanguages[$playerName] ?? $this->defaultLanguage;
    }
    
    public function setLanguage(Player|string $player, string $language): void {
        if ($player instanceof Player) {
            $playerName = $player->getName();
        } else {
            $playerName = $player;
        }
        
        if (isset($this->languages[$language])) {
            $this->playerLanguages[$playerName] = $language;
            $this->savePlayerLanguages();
        }
    }
    
    public function get(string $key, Player|string|null $player = null, array $replacements = []): string {
        $lang = $this->getLanguage($player ?? $this->defaultLanguage);
        
        if (!isset($this->languages[$lang])) {
            $lang = $this->defaultLanguage;
        }
        
        $keys = explode(".", $key);
        $value = $this->languages[$lang];
        
        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                // Eğer anahtar bulunamazsa, varsayılan dilde dene
                if ($lang !== $this->defaultLanguage) {
                    return $this->get($key, $this->defaultLanguage, $replacements);
                }
                return $key; // Son çare olarak anahtarı döndür
            }
            $value = $value[$k];
        }
        
        if (!is_string($value)) {
            return $key;
        }
        
        // Yer değiştirmeleri yap
        foreach ($replacements as $search => $replace) {
            $value = str_replace("{" . $search . "}", $replace, $value);
        }
        
        return $value;
    }
    
    public function getAvailableLanguages(): array {
        return array_keys($this->languages);
    }
    
    public function getLanguageName(string $code): string {
        // Önce dil dosyasında "language_name" anahtarı var mı kontrol et
        if (isset($this->languages[$code]) && isset($this->languages[$code]["language_name"])) {
            return $this->languages[$code]["language_name"];
        }
        
        // Eğer yoksa, dil kodunu formatla ve göster
        // Örnek: "ru" -> "Ru", "en_US" -> "En Us"
        $parts = explode("_", $code);
        $formatted = array_map(function($part) {
            return ucfirst(strtolower($part));
        }, $parts);
        
        return implode(" ", $formatted);
    }
}


<?php

namespace Market\Commands;

use Market\Main;
use Market\Forms\MarketMenuForm;
use Market\Forms\ListForm;
use Market\Forms\BuyForm;
use Market\Forms\SellForm;
use Market\Forms\PriceForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class MarketCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct(
            "market", 
            "Market menüsü", 
            "/market [list|buy|sell|price]",
            ["m", "pazar"]
        );
        $this->plugin = $plugin;
        $this->setPermission("market.use");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        try {
            if (!$this->testPermission($sender)) {
                return false;
            }
            
            $langManager = $this->plugin->getLanguageManager();
            
            // Eğer argüman yoksa ana menüyü aç
            if (count($args) === 0) {
                if (!$sender instanceof Player) {
                    $sender->sendMessage($langManager->get("commands.player_only"));
                    return false;
                }
                
                $menuForm = new MarketMenuForm($this->plugin);
                $menuForm->sendForm($sender);
                return true;
            }
            
            $subCommand = strtolower($args[0]);
            
            switch ($subCommand) {
            case "list":
                if (!$sender instanceof Player) {
                    // Konsol için liste göster
                    $items = $this->plugin->getMarketManager()->getAllItems();
                    $config = $this->plugin->getMarketConfig();
                    $settings = $config->get("settings", []);
                    $currency = $settings["currency"] ?? "Para";
                    
                    $sender->sendMessage($langManager->get("list.console_title"));
                    
                    if (empty($items)) {
                        $sender->sendMessage($langManager->get("list.console_no_items"));
                        return true;
                    }
                    
                    foreach ($items as $itemId => $itemData) {
                        $name = $itemData["name"] ?? $itemId;
                        $buyPrice = $itemData["buy"] ?? 0;
                        $sellPrice = $itemData["sell"] ?? 0;
                        
                        $sender->sendMessage($langManager->get("list.console_item_format", null, ["name" => $name, "item_id" => $itemId]));
                        $sender->sendMessage($langManager->get("list.console_buy", null, ["buy_price" => $buyPrice, "currency" => $currency]));
                        $sender->sendMessage($langManager->get("list.console_sell", null, ["sell_price" => $sellPrice, "currency" => $currency]));
                    }
                    
                    $sender->sendMessage("§6=====================================");
                    return true;
                }
                
                $listForm = new ListForm($this->plugin);
                $listForm->sendForm($sender);
                return true;
                
            case "buy":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($langManager->get("commands.player_only"));
                    return false;
                }
                
                // Eğer argüman yoksa form aç
                if (count($args) < 2) {
                    $buyForm = new BuyForm($this->plugin);
                    $buyForm->sendForm($sender);
                    return true;
                }
                
                // Komut satırından kullanım
                $itemName = $args[1];
                $amount = isset($args[2]) ? $args[2] : "1";
                
                // Amount validasyonu - sadece sayı kabul et
                if (!is_numeric($amount)) {
                    $sender->sendMessage($langManager->get("commands.invalid_amount", $sender));
                    return false;
                }
                
                $amount = (int) $amount;
                
                if ($amount <= 0) {
                    $sender->sendMessage($langManager->get("commands.amount_too_low", $sender));
                    return false;
                }
                
                // Integer overflow kontrolü
                if ($amount > PHP_INT_MAX / 1000) {
                    $sender->sendMessage($langManager->get("commands.amount_too_high", $sender));
                    return false;
                }
                
                $this->plugin->getMarketManager()->buyItem($sender, $itemName, $amount);
                return true;
                
            case "sell":
                if (!$sender instanceof Player) {
                    $sender->sendMessage($langManager->get("commands.player_only"));
                    return false;
                }
                
                // Eğer argüman yoksa form aç
                if (count($args) < 2) {
                    $sellForm = new SellForm($this->plugin);
                    $sellForm->sendForm($sender);
                    return true;
                }
                
                // Komut satırından kullanım
                $itemName = $args[1];
                $amount = isset($args[2]) ? $args[2] : "1";
                
                // Amount validasyonu - sadece sayı kabul et
                if (!is_numeric($amount)) {
                    $sender->sendMessage($langManager->get("commands.invalid_amount", $sender));
                    return false;
                }
                
                $amount = (int) $amount;
                
                if ($amount <= 0) {
                    $sender->sendMessage($langManager->get("commands.amount_too_low", $sender));
                    return false;
                }
                
                // Integer overflow kontrolü
                if ($amount > PHP_INT_MAX / 1000) {
                    $sender->sendMessage($langManager->get("commands.amount_too_high", $sender));
                    return false;
                }
                
                $this->plugin->getMarketManager()->sellItem($sender, $itemName, $amount);
                return true;
                
            case "price":
                // Eğer oyuncu ise ve argüman yoksa form aç
                if ($sender instanceof Player && count($args) < 2) {
                    $priceForm = new PriceForm($this->plugin);
                    $priceForm->sendForm($sender);
                    return true;
                }
                
                // Komut satırından kullanım
                if (count($args) < 2) {
                    $sender->sendMessage($langManager->get("price.usage"));
                    $sender->sendMessage($langManager->get("price.example"));
                    return false;
                }
                
                $itemName = strtolower($args[1]);
                $buyPrice = $this->plugin->getMarketManager()->getPrice($itemName, "buy");
                $sellPrice = $this->plugin->getMarketManager()->getPrice($itemName, "sell");
                $displayName = $this->plugin->getMarketManager()->getItemDisplayName($itemName);
                
                if ($buyPrice === null && $sellPrice === null) {
                    $sender->sendMessage($langManager->get("price.not_found"));
                    return false;
                }
                
                $config = $this->plugin->getMarketConfig();
                $settings = $config->get("settings", []);
                $currency = $settings["currency"] ?? "Para";
                
                $name = $displayName ?? $itemName;
                
                $sender->sendMessage($langManager->get("price.console_title", null, ["name" => $name]));
                
                if ($buyPrice !== null) {
                    $sender->sendMessage($langManager->get("price.console_buy", null, ["buy_price" => $buyPrice, "currency" => $currency]));
                }
                
                if ($sellPrice !== null) {
                    $sender->sendMessage($langManager->get("price.console_sell", null, ["sell_price" => $sellPrice, "currency" => $currency]));
                }
                
                $sender->sendMessage("§6=====================================");
                return true;
                
            case "addall":
                // Admin komutu - tüm itemleri ekle
                if (!$sender->hasPermission("market.admin")) {
                    $sender->sendMessage($langManager->get("commands.no_permission"));
                    return false;
                }
                
                $sender->sendMessage($langManager->get("commands.addall_processing"));
                $added = $this->plugin->getMarketManager()->addAllItemsToMarket();
                $sender->sendMessage($langManager->get("commands.addall_success", null, ["count" => $added]));
                $this->plugin->getLogger()->info("{$sender->getName()} tarafından {$added} item markete eklendi.");
                return true;
                
            default:
                if (!$sender instanceof Player) {
                    $sender->sendMessage($langManager->get("commands.invalid_subcommand"));
                    return false;
                }
                
                // Geçersiz alt komut için ana menüyü aç
                $menuForm = new MarketMenuForm($this->plugin);
                $menuForm->sendForm($sender);
                return true;
            }
        } catch (\Throwable $e) {
            $langManager = $this->plugin->getLanguageManager();
            $this->plugin->getLogger()->error("Market komutu çalıştırılırken hata oluştu: " . $e->getMessage());
            $sender->sendMessage($langManager->get("commands.error"));
            return false;
        }
    }
}


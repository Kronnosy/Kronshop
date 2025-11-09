<?php

namespace Market\Commands;

use Market\Main;
use Market\Forms\PriceForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class PriceCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("price", "Eşya fiyatı", "/market price [eşya]");
        $this->plugin = $plugin;
        $this->setPermission("market.use");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }
        
        // Eğer oyuncu ise ve argüman yoksa form aç
        if ($sender instanceof Player && count($args) < 1) {
            $priceForm = new PriceForm($this->plugin);
            $priceForm->sendForm($sender);
            return true;
        }
        
        $langManager = $this->plugin->getLanguageManager();
        
        // Komut satırından kullanım
        if (count($args) < 1) {
            $sender->sendMessage($langManager->get("price.usage"));
            $sender->sendMessage($langManager->get("price.example"));
            return false;
        }
        
        $itemName = strtolower($args[0]);
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
    }
}


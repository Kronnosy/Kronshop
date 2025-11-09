<?php

namespace Market\Commands;

use Market\Main;
use Market\Forms\ListForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class ListCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("list", "Market listesi", "/market list");
        $this->plugin = $plugin;
        $this->setPermission("market.use");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if (!$this->testPermission($sender)) {
            return false;
        }
        
        // Eğer oyuncu ise form aç
        if ($sender instanceof Player) {
            $listForm = new ListForm($this->plugin);
            $listForm->sendForm($sender);
            return true;
        }
        
        $langManager = $this->plugin->getLanguageManager();
        
        // Konsol için mesaj göster
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
}


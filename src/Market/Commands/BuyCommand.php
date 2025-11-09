<?php

namespace Market\Commands;

use Market\Main;
use Market\Forms\BuyForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class BuyCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("buy", "Eşya satın al", "/market buy [eşya] [miktar]");
        $this->plugin = $plugin;
        $this->setPermission("market.use");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $langManager = $this->plugin->getLanguageManager();
        
        if (!$sender instanceof Player) {
            $sender->sendMessage($langManager->get("commands.player_only"));
            return false;
        }
        
        if (!$this->testPermission($sender)) {
            return false;
        }
        
        // Eğer argüman yoksa form aç
        if (count($args) < 1) {
            $buyForm = new BuyForm($this->plugin);
            $buyForm->sendForm($sender);
            return true;
        }
        
        // Komut satırından kullanım
        $itemName = $args[0];
        $amount = isset($args[1]) ? (int) $args[1] : 1;
        
        if ($amount < 1) {
            $sender->sendMessage($langManager->get("commands.amount_too_low", $sender));
            return false;
        }
        
        $this->plugin->getMarketManager()->buyItem($sender, $itemName, $amount);
        
        return true;
    }
}


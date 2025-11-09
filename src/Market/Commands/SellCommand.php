<?php

namespace Market\Commands;

use Market\Main;
use Market\Forms\SellForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class SellCommand extends Command {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        parent::__construct("sell", "Eşya sat", "/market sell [eşya] [miktar]");
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
            $sellForm = new SellForm($this->plugin);
            $sellForm->sendForm($sender);
            return true;
        }
        
        // Komut satırından kullanım
        $itemName = $args[0];
        $amount = isset($args[1]) ? (int) $args[1] : 1;
        
        if ($amount < 1) {
            $sender->sendMessage($langManager->get("commands.amount_too_low", $sender));
            return false;
        }
        
        $this->plugin->getMarketManager()->sellItem($sender, $itemName, $amount);
        
        return true;
    }
}

